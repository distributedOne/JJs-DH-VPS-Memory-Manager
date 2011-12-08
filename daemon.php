<?php
/**
 * JJ's VPS Memory Manager Daemon
 *
 * This application monitors and manages memory for a DreamHost Web VPS
 * It runs as a daemon and writes out the log for the web interface graphs
 * 
 * @author JuanJose Galvez
 * @website http://www.gimmesoda.com/
 * @email jj@gimmesoda.com
 * @package JJ's VPS Memory Manager
 * 
 */

require("setup.php");

$memory_manager = new MemoryManager;
$vps_commands = new DreamHost_VPS_Commands(DH_API_KEY, HOSTNAME);
$service_commands = new DreamHost_Service_Control_Commands(DH_API_KEY, HOSTNAME);

// Check to see if the memory manager should even run
if($memory_manager->is_daemon_disabled())
    die('Memory manager is disabled!' . "\n");

if($memory_manager->is_daemon_running())
    die();

date_default_timezone_set("America/Los_Angeles"); //Use DreamHost TZ!
 
$run_mode = array(
    'no-daemon' => false,
    'help' => false,
);
 
// Scan command line attributes for allowed arguments
foreach ($argv as $k=>$arg) {
    if (substr($arg, 0, 2) == '--' && isset($run_mode[substr($arg, 2)])) {
        $run_mode[substr($arg, 2)] = true;
    }
}
 
// Help mode. Shows allowed argumentents and quit directly
if ($run_mode['help'] == true) {
    echo 'Usage: '.$argv[0].' [runmode]' . "\n";
    echo 'Available runmodes:' . "\n";
    foreach ($run_mode as $runmode=>$val) {
        echo ' --'.$runmode . "\n";
    }
    die();
}
 
// This program can also be run in the foreground with runmode --no-daemon
if (!$run_mode['no-daemon']) {
    System_Daemon::start();
}
 
$cnt = 1;
$last_resize = 0;
$stopFileFound = false;
$tomorrow  = mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"));

while (!System_Daemon::isDying() && !$stopFileFound) {
  
  $time = time();

  $suggestion = $memory_manager->suggest_memory();
  $totalMemory = $memory_manager->get_total_memory();
  $usedMemory = $memory_manager->get_used_memory();
  $cacheMemory = $memory_manager->get_cached_memory();
  $load_averages = $memory_manager->get_load_average();
  $availableMemory = $totalMemory - $usedMemory;
  $type = ($suggestion > $totalMemory) ? 'increase' : 'decrease';

  /* Write to the graph logs! */

  //Things to do on first run
  if($cnt == 1) {
      System_Daemon::info('Daemon Started. Current Memory: %s Used: %s Suggestion: %s', $totalMemory, $usedMemory, $suggestion);
      $memory_manager->write_graph_log($time, $totalMemory, $availableMemory, $load_averages[0], $load_averages[1], $load_averages[2]);
      $next_time = $time + (60 * 5);
  }
  
  if($time >= $next_time) {
    $memory_manager->write_graph_log($time, $totalMemory, $availableMemory, $load_averages[0], $load_averages[1], $load_averages[2]);
    $next_time = $time + (60 * 5);
  }

  if($long_sleep == false) {

    if($memory_manager->is_change_needed()) {
	  	
      if(CHANGE_MEMORY == true) {

  		  //Request is a decrease and is at least 30 mins apart from the last resize, or suggestion is greater then totalMemory
  	    if((($time > ($last_resize + (60 * 30))) && $type = 'decrease') || ($suggestion > $totalMemory)) { 
  	
    		  System_Daemon::info('Change is requested. Current Memory: %s Used: %s Suggestion: %s', $totalMemory, $usedMemory, $suggestion);
              $memory_manager->write_process_log($suggestion, $usedMemory, $cacheMemory);
  		  
    		  if($vps_commands->set_size(HOSTNAME, $suggestion)) {
  	
                $checks = 0;
    		    $status = false;
    		  	
    		    $results = $vps_commands->get_final_results();
    		    $resize_token = $results['token'];
  	
    		    while($status != 'success' && $status != 'failure') {
    	
    		      $checks++;
  			  
    		      if($service_commands->progress($resize_token)) {
    		        $service_results = $service_commands->get_final_results();
    		        $status = $service_results['status'];
    		        System_Daemon::info('Current status of resize request: %s', $status);
    		        $memory_manager->check_for_stop_file();
    		        sleep(5);
    		      }
  			  
    		      if($checks >= 50 && $status != 'success') {
    		        System_Daemon::info('API taking too long!');
    		        $status = 'failure';
    		      }
  	
    		    }
			
    		    if($status == 'success') {
              $last_resize = time();
    			    $last_type = ($suggestion > $totalMemory) ? 'increase' : 'decrease';
    		    }
			
    		  } else {
		  	
    		  	$results = $vps_commands->get_final_results();
			
      			if($results == 'exceeded_30_resizes_today') {

    		      System_Daemon::info('Max resizes have been hit, we are now in sleep mode!');
      			  mail(EMAIL, HOSTNAME . ": Reached Max Resizes", "
JJ's VPS Memory Manager has reached the total number of resizes that can be performed on " . HOSTNAME . " today.
This limitation is built into the DreamHost API and is not a limitation of JJ's VPS Memory manager.
You should monitor your memory usage and adjust accordingly from the DreamHost control panel.
You should also consider optimization of your VPS Memory Manager configuration.

Check out: http://www.gimmesoda.com/jjs-vps-memory-manager-faq/

Don't forget that VPS and website optimizations can also be quite helpful:

Check out: http://www.gimmesoda.com/jjs-vps-and-site-optimization-suggestions/

When tomorrow comes the memory manager will continue to perform adjustments as needed.
If you'd like to contact JJ with any bugs or questions just respond to this email!", 'Reply-To: memory_manager@gimmesoda.com');
      			  $long_sleep = true;

            } else {
              $error_array = $vps_commands->get_error_array();

              if($error_array['message']) {
    			      System_Daemon::info('CURL encountered an error. %s ', $error_array['message']);
              }

            } //End check for 30 resizes

          } //End set size

        } else { //Not allowed to modify memory

          if(LOG_ALL) {
      		  System_Daemon::info('Change not request per settings. Current Memory: %s Used: %s Suggestion: %s', $totalMemory, $usedMemory, $suggestion);
      		}

        }
      
      } //End memory change allowed check
	
    } else { //Change is NOT needed

      if(LOG_ALL){
	      System_Daemon::info('No change needed. Current Memory: %s Used: %s', $totalMemory, $usedMemory);
      }

    } 

  } else { //Currently in long sleep mode

  	if($time > $tomorrow) {
  	  $tomorrow  = mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"));
  	  $long_sleep = false;
      System_Daemon::info("It's a new dawn, it's a new day, and I'm feeling good!");
      System_Daemon::info('Resize limit should now be reset, we are now in active mode!');
    }
  	
  }

  $memory_manager->check_for_stop_file();

  System_Daemon::iterate(5);
  $cnt++;

}

System_Daemon::stop();

?>