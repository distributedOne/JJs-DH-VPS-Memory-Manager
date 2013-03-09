<?php
/**
 * DreamHost VPS Memory Manager Daemon Class
 *
 * This file contains most of the functionality 
 * for the DreamHost VPS Memory Manager daemon
 * 
 * @author JuanJose Galvez
 * @website http://www.gimmesoda.com/
 * @email jj@gimmesoda.com
 * @package JJ's VPS Memory Manager
 *  
 */

class MemoryManagerDaemon {

  protected $memory_manager, $vps_commands, $service_commands;
  private $loop, $time, $next_time, $tomorrow, $long_sleep, $last_resize, $change_type;
  private $totalMemory, $usedMemory, $availableMemory, $suggestion, $load_averages, $cacheMemory;

  public function __construct() {
    $this->memory_manager = new MemoryManager;
    $this->vps_commands = new DreamHost_VPS_Commands(DH_API_KEY, HOSTNAME);
    $this->service_commands = new DreamHost_Service_Control_Commands(DH_API_KEY, HOSTNAME);
  }

  public function main() {

    $this->__setup_and_run();
    while (!System_Daemon::isDying() && !$stopFileFound) {
      $this->time = time();
      $this->suggestion = $this->memory_manager->suggest_memory();
      $this->totalMemory = $this->memory_manager->get_total_memory();
      $this->usedMemory = $this->memory_manager->get_used_memory();
      $this->cacheMemory = $this->memory_manager->get_cached_memory();
      $this->load_averages = $this->memory_manager->get_load_average();
      $this->availableMemory = $totalMemory - $usedMemory;
      $this->change_type = ($suggestion > $totalMemory) ? 'increase' : 'decrease';
      $this->__do_the_needful();
      $this->memory_manager->trim_logs();
      $this->memory_manager->check_for_stop_file();
      System_Daemon::iterate(5);
      $this->loop++;
    }
  }

  private function __setup_and_run()
  {
    global $argv;

    date_default_timezone_set("America/Los_Angeles"); //Use DreamHost TZ!

    $this->loop = 1;
    $this->stopFileFound = false;
    $this->tomorrow  = mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"));

    /* should the daemon run/is it already running? */
    if($this->memory_manager->is_daemon_disabled())
      die('Memory manager is disabled!' . "\n");

    if($this->memory_manager->is_daemon_running())
      die(); //die silently as to not fill logs when using the cronjob

    define('MAX_RESIZES_MSG', "
          JJ's VPS Memory Manager has reached the total number of resizes that can be performed on " . HOSTNAME . " today.
          This limitation is built into the DreamHost API and is not a limitation of JJ's VPS Memory manager.
          You should monitor your memory usage and adjust accordingly from the DreamHost control panel.
          You should also consider optimization of your VPS Memory Manager configuration.

          Check out: http://www.gimmesoda.com/jjs-vps-memory-manager-faq/

          Don't forget that VPS and website optimizations can also be quite helpful:

          Check out: http://www.gimmesoda.com/jjs-vps-and-site-optimization-suggestions/

          When tomorrow comes the memory manager will continue to perform adjustments as needed.
          If you'd like to contact JJ with any bugs or questions just respond to this email!"); //defining message here for now

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

  }

  private function __do_the_needful()
  {

    if($this->loop == 1) {
      System_Daemon::info('Daemon Started. Current Memory: %s Used: %s Suggestion: %s', $this->totalMemory, $this->usedMemory, $this->suggestion);
      $this->memory_manager->write_graph_log($this->time, $this->totalMemory, $this->availableMemory, $this->load_averages[0], $this->load_averages[1], $this->load_averages[2]);
      $this->next_time = $this->time + (60 * 5);
    }

    if($this->time >= $this->next_time) {
      $this->memory_manager->write_graph_log($this->time, $this->totalMemory, $this->availableMemory, $this->load_averages[0], $this->load_averages[1], $this->load_averages[2]);
    }

    $this->__check_resize();

  }

  private function __check_resize()
  {
    if($this->long_sleep == false)
    {
      if($this->memory_manager->is_change_needed())
      {
        if(CHANGE_MEMORY == true)
        {
          //Request is a decrease and is at least 30 mins apart from the last resize, or suggestion is greater then totalMemory
          if($this->change_type = 'decrease' && ($this->time > ($this->last_resize + 1800)) || ($this->suggestion > $this->totalMemory))
          {
            System_Daemon::info('Change is requested. Current Memory: %s Used: %s Suggestion: %s', $this->totalMemory, $this->usedMemory, $this->suggestion);
            $this->memory_manager->write_process_log($this->suggestion, $this->usedMemory, $this->cacheMemory);
            if($this->vps_commands->set_size(HOSTNAME, $this->suggestion))
            {
              $results = $this->vps_commands->get_final_results();
              $resize_token = $results['token'];
              if($this->service_commands->progress($resize_token))
                $this->__wait_on_resize($resize_token);
            }
          } else { //decrease before timelimit OR suggestion is less than current total memory
            if(LOG_ALL)
              System_Daemon::info('Change not requested per settings. Current Memory: %s Used: %s Suggestion: %s', $this->totalMemory, $this->usedMemory, $this->suggestion);
          }
        } //End memory change allowed check
      } else { //Change is NOT needed
        if(LOG_ALL)
          System_Daemon::info('No change needed. Current Memory: %s Used: %s', $this->totalMemory, $this->usedMemory);
      } 
    } else { //Currently in long sleep mode
      $this->__is_it_tomorrow();
    }
  }

  private function __is_it_tomorrow()
  {
    if($this->time > $this->tomorrow) {
      $this->tomorrow  = mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"));
      $this->long_sleep = false;
      System_Daemon::info("It's a new dawn, it's a new day, and I'm feeling good!");
      System_Daemon::info('Resize limit should now be reset, we are now in active mode!');
    }
  }

  private function __wait_on_resize($resize_token)
  {

    $status = null;
    $checks = 0;

    while($status != 'success' && $status != 'failure')
    {

      $checks++;
      $service_results = $this->service_commands->get_final_results();
      $status = $service_results['status'];
      System_Daemon::info('Current status of resize request: %s', $status);
      $this->memory_manager->check_for_stop_file();

      if($checks >= 50 && $status != 'success') {
        $this->memory_manager->info('API taking too long!');
        $status = 'failure';
      }

      if($status == 'success')
      {
        $this->last_resize = time();
        $last_type = ($this->suggestion > $this->totalMemory) ? 'increase' : 'decrease';
        $this->memory_manager->communicate($this->totalMemory, $this->suggestion);
      } else { //anything other than success
        $results = $this->vps_commands->get_final_results();
        if($results == 'exceeded_30_resizes_today')
        {
          System_Daemon::info('Max resizes have been hit, we are now in sleep mode!');
          mail(EMAIL, HOSTNAME . ": Reached Max Resizes", MAX_RESIZES_MSG, 'Reply-To: jj@gimmesoda.com');
          $this->long_sleep = true;
        } else {
          $error_array = $this->vps_commands->get_error_array();
          if($error_array['message'])
            System_Daemon::info('CURL encountered an error. %s ', $error_array['message']);
        }
      }

      System_Daemon::iterate(5); //wait 5 seconds before the next check

    }

  }             

}