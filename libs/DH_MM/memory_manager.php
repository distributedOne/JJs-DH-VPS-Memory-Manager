<?php
/**
 * DreamHost VPS Memory Manager Class
 *
 * This file contains most of the control and 
 * functionality for the DreamHost VPS Memory Manager
 * 
 * @author JuanJose Galvez
 * @website http://www.gimmesoda.com/
 * @email jj@gimmesoda.com
 * @package JJ's VPS Memory Manager
 *  
 */

class MemoryManager {
  private $disable_file;
  private $stop_file;
  private $login_file;
  private $db_file;
  private $error_file;
  private $temp_file;
  private $process_file;
  private $memory_log;
  private $pid_file;
  private $config_file;
  private $config_sample_file;
  private $template;
  private $template_folder;
  private $module_action;
  private $start_daemon_command;
  private $cron_command;
  
  function __construct() {
    global $system_path, $template, $module_action;
    $this->disable_file = $system_path . '/var/run/DreamHost_VPS_Memory_Manager/disable';
    $this->stop_file = $system_path . '/var/run/DreamHost_VPS_Memory_Manager/stop';
    $this->login_file = $system_path . '/var/run/login.php';
    $this->db_file = $system_path . '/var/logs/flatfile';
    $this->temp_file = $system_path . '/var/logs/temp';
    $this->error_file = $system_path . '/var/logs/errors';
    $this->memory_log = $system_path . '/var/logs/memory';
    $this->process_file = $system_path . '/var/logs/processes';
    $this->pid_file = $system_path . '/var/run/DreamHost_VPS_Memory_Manager/DreamHost_VPS_Memory_Manager.pid';
    $this->config_file = $system_path . '/config.php';
    $this->config_sample_file = $system_path . '/config_sample.php';
    $this->template = $template;
    $this->template_folder = $system_path . "/templates/";
    $this->module_action = $module_action;
    $this->start_daemon_command = '/usr/local/php5/bin/php ' . $system_path . '/daemon.php >> ' . $this->error_file . ' 2>&1 &';
    $this->cron_command = '*/5 * * * * ' . $this->start_daemon_command;
  }
  
  /* Command List Related Functions */
  function get_command_list() {

    $daemon_disabled_status = $this->is_daemon_disabled();
    if($daemon_disabled_status == true) {
      $daemon_command = '<a href="#" onClick="enable_daemon();"><div id="ReEnable"><img src="templates/amazing/images/reEnable.png" alt="re-enable" /><span>Re-enable Daemon</span></div></a>';
    } else {
      $daemon_command = '<a href="#" onClick="disable_daemon();"><div id="Disable"><img src="templates/' . TEMPLATE . '/images/disable.png" alt="disable" /><span>Disable Daemon</span></div></a>';
    }
    $command_list = $daemon_command;

    $daemon_is_stopped = $this->is_daemon_running();
    if($daemon_is_stopped == false) {
      $stop_command = '<a href="#"><div id="Restart"><img src="templates/amazing/images/restart.png" alt="restart" /><span> Not Running</span></div></a>';
    } else {
      $stop_command = '<a href="#" onClick="reload_daemon();"><div id="Restart"><img src="templates/amazing/images/restart.png" alt="restart" /><span>Restart Daemon</span></div></a>';
    }
    $command_list  .= $stop_command;

    $clear_logs_command = '<a href="#" onClick="clear_logs();"><div id="Clear"><img src="templates/amazing/images/clear.png" alt="clear" /><span>Clear All</span></div></a>';
    $command_list .= $clear_logs_command;

    $crontab = new Gimme_Cron;
    $cron_installed = $crontab->find_line($this->cron_command);
    if($cron_installed == false) {
        $cron_command = '<a href="#" onClick="install_cron();"><div id="Cron"><img src="templates/amazing/images/cron.png" alt="cron" /><span>Install Cron</span></div></a>';
    } else {
        $cron_command = '<a href="#" onClick="remove_cron();"><div id="Cron"><img src="templates/amazing/images/cron.png" alt="cron" /><span>Remove Cron</span></div></a>';
    }
    $command_list .= $cron_command;
    
    $command_list .= '<a href="?action=logout"><div id="Logout"><img src="templates/amazing/images/logout.png" alt="logout" /><span>Logout</span></div></a>';
    
    $this->template->assign('command_list', $command_list);
  }
    
  function execute_menu_command($command) {
    switch($command) {
      case "clear_logs":
        $this->clear_logs();
      break;
      
      case "disable_daemon":
        $this->stop_daemon();
        $this->disable_daemon();
      break;
      
      case "reload_daemon":
        $this->stop_daemon();
        sleep(5);
        $this->start_daemon();
      break;
      
      case "enable_daemon";
        $this->enable_daemon();
      break;
      
      case "add_cron":
        $this->add_cron();
      break;

      case "remove_cron":
        $this->remove_cron();
      break;
      
      case "get_news":
        echo $this->get_news();
      break;
            
      default:
        //Do Nothing!
      break;
    }
  }
  
  /* End Command List Related Functions */

  /* Cron Related Functions */
  
  function add_cron() {
    $crontab = new Gimme_Cron;
    $crontab->add_line($this->cron_command);
  }

  function remove_cron() {
    $crontab = new Gimme_Cron;
    $crontab->remove_line($this->cron_command);
  }
  
  /* End Cron Related Functions */

  /* Graph Related Functions */  
  function get_graph_data() {
    $i = 1;
    $flat_file = file($this->db_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lines = count($flat_file);

    $am     = 'var am = [';
    $tm     = 'var tm = [';
    $load1  = 'var load1 = [';
    $load5  = 'var load5 = [';
    $load15 = 'var load15 = [';

    foreach($flat_file as $line) {
      $line_data = explode("|", $line);
      $time = ($line_data[0] / 1000) + (-28800);
      $tm     .="[" . trim($line_data[0]) . ", " . trim($line_data[1]) . "]";
      $am     .="[" . trim($line_data[0]) . ", " . trim($line_data[2]) . "]";
      $load1  .="[" . trim($line_data[0]) . ", " . trim($line_data[3]) . "]";
      $load5  .="[" . trim($line_data[0]) . ", " . trim($line_data[4]) . "]";
      $load15 .="[" . trim($line_data[0]) . ", " . trim($line_data[5]) . "]";

      if($i != $lines) {
        $tm     .=",";
        $am     .=",";
        $load1  .=",";
        $load5  .=",";
        $load15 .=",";
      }
      $i++;
    }
    
    $tm     .= '];';
    $am     .= '];';
    $load1  .= ']';
    $load5  .= ']';
    $load15 .= ']';

    $this->template->assign("tm", $tm);
    $this->template->assign("am", $am);
    $this->template->assign("load1", $load1);
    $this->template->assign("load5", $load5);
    $this->template->assign("load15", $load15);
  }
  /* End Graph Related Functions */
  
  /* Log Related Functions */
  
  function clear_logs() {
    exec("echo '' > " . $this->db_file);
    exec("echo '' > " . $this->temp_file);
    exec("echo '' > " . $this->memory_log);
    exec("echo '' > " . $this->error_file);
    exec("echo '' > " . $this->process_file);
  }
  
  function get_memory_log() {
    $this->trim_logs();
    exec('tail -n 17 ' . $this->memory_log, $log);
    $log = array_reverse($log);
    foreach($log as $line) {
      $final_log .= trim($line) . "<br />";
    }
    $this->template->assign("latest_log", $final_log);
  }
  
  function write_graph_log($time, $total_memory, $available_memory, $load_1, $load_2, $load_3) {

    $log_content = $time . "000|" . $total_memory . "|" . $available_memory . "|" . $load_1 . "|" . $load_2 . "|" . $load_3;
    if (!$fh = fopen($this->db_file, 'a')) {
      System_Daemon::emerg("Cannot open file: " . $this->db_file);
    }
  
    if (fwrite($fh, $log_content . "\n") === FALSE) {
      System_Daemon::emerg("Cannot write to file: " .  $this->db_file);
    } else {
      System_Daemon::info("Wrote to graph database: " . $log_content);
    }
    fclose($fh);
  }
  
  function write_process_log($suggestion, $used_memory, $cache_memory) {
  
    $datetime = date(DATE_RFC822);
    $log_content  = $datetime . "\n";
    $log_content .= "Used Memory: " . $used_memory . "\n";
    $log_content .= "Suggested Memory: " . $suggestion . "\n";
    $log_content .= "Cache Memory: " . $cache_memory . "\n";
    $log_content .= "========================\n";
    exec("ps -eF | grep -v CMD | sort -k6 -r", $ps);
    foreach($ps as $line) {
      $log_content .= trim($line) . "\n";
    }
    
    if (!$fh = fopen($this->process_file, 'a')) {
      System_Daemon::emerg("Cannot open file: " . $this->process_file);
    }
  
    if (fwrite($fh, $log_content . "\n") === FALSE) {
      System_Daemon::emerg("Cannot write to file: " . $this->process_file);
    } else {
      System_Daemon::info("Wrote to process log!");
    }
    fclose($fh);

  }

  function trim_logs() {
    $number_of_lines = exec("wc -l " . $this->db_file . " | awk {'print $1'}");
    if($number_of_lines >= "300") {
      $this->delete_lines($this->db_file, 12);
    }
    
    $number_of_lines = exec("wc -l " . $this->error_file . " | awk {'print $1'}");
    if($number_of_lines >= "1000") {
      $this->delete_lines($this->error_file, 200);
    }

    $number_of_lines = exec("wc -l " . $this->memory_log . " | awk {'print $1'}");
    if($number_of_lines >= "1000") {
      $this->delete_lines($this->memory_log, 200);
    }

    $number_of_lines = exec("wc -l " . $this->process_file . " | awk {'print $1'}");
    if($number_of_lines >= "1000") {
      $this->delete_lines($this->process_file, 200);
    }

  }

  function delete_lines($file, $number_of_lines) {
    exec("sed '1," . $number_of_lines . "d' " . $file . " > " . $this->temp_file);
    exec("rm " . $file);
    exec("mv " . $this->temp_file . " " . $file);
  }
  /* End Log Related Functions */
  
  /* Daemon Related Functions */
    function stop_daemon() {
    if (!$handle = fopen($this->stop_file, 'a')) {
      System_Daemon::info("Could not write stop file.");
      $success = true;
    } else {
      System_Daemon::info("The daemon will be stopped.");      
      $success = false;
    }
    fclose($handle);
    return $success;
  }

  function enable_daemon() {
    @unlink($this->disable_file);
    @unlink($this->stop_file);
    $this->start_daemon();
  }

  function is_daemon_stopped() {
    return (file_exists($this->stop_file)) ? true : false;
  }
  
  function disable_daemon() {
    if (!$handle = fopen($this->disable_file, 'a')) {
      System_Daemon::info("Could not write disablement file.");
      $success = true;
    } else {
      System_Daemon::info("Automatic start of the daemon has been disabled.");      
      $success = false;
    }
    fclose($handle);
    return $success;
  }
  
  function is_daemon_disabled() {
    return (file_exists($this->disable_file)) ? true : false;
  }
  
  function is_daemon_running() {
    if(file_exists($this->pid_file)) {
      $pid = file_get_contents($this->pid_file);
      //when executing a command like below through php the proc count is always +1
      $is_running = shell_exec('ps aux | grep ' . $pid . ' | grep daemon.php | wc -l');
      if($is_running > 1) { return true; } else { return false; }
    } else {
      return false;
    }
  }
  
  function start_daemon() {
    exec($this->start_daemon_command);
  }

  function check_for_stop_file() {
  	global $options;
  	if(file_exists($options['appStopFile'])) {
  		$stopFileFound = true;
  		unlink($options['appStopFile']);
	} else {
		$stopFileFound = false;
	}
	if ($stopFileFound) {
      System_Daemon::info('Stop file has been found, this is the final run.');
      System_Daemon::stop();
    }
  }

  /* End Daemon Related Functions */
  
  /* Load Related Functions */
  function get_load_average() {
    $avg_info = exec("awk -F\  {'print $1 \"|\" $2 \"|\" $3'} /proc/loadavg");
    return explode("|", $avg_info);
  }
  /* End Load Related Functions */
  
  /* Memory Related Functions */
  function is_change_needed() {
    $suggestion = $this->suggest_memory();
    $current_memory = $this->get_total_memory();

    if($current_memory > $suggestion) {
      $percent_difference = 100 * (($current_memory- $suggestion) / ($current_memory));
    } else {
      $percent_difference = 100 * (($suggestion - $current_memory) / ($suggestion));
    }

    if(($percent_difference >= (SAFETY_PERCENT / 2)) && ($current_memory != $suggestion)) {
      return true;
    } else {
      return false;
    }
  }
  
  function suggest_memory() {
    //For servers using lots of cache (default)
    if(IGNORE_CACHE == false) {
      $used_memory = $this->get_used_memory();
      $suggest = round($used_memory + ($used_memory * (SAFETY_PERCENT / 100)));

      $cached_memory = $this->get_cached_memory();
      $cached_suggest = round($cached_memory + ($cached_memory * (SAFETY_PERCENT / 100)));
    } else {
      $used_memory = $this->get_used_memory() - $this->get_cached_memory();
      $suggest = round($used_memory + ($used_memory * (SAFETY_PERCENT / 100)));
    }
    
    //Go for the higher memory requirement in this case
    if($cached_suggest > $suggest) {
      $suggest = $cached_suggest;
    }
    
    /**
     * 
     * Begin Committed_AS Code
     * Would be fantastic if not for the underflow bug
     * https://patchwork.kernel.org/patch/20336/
     * Leaving code in place in case it ever gets patched
     * 
     */
    
    $committed_AS = $this->get_committed_as();

    if($committed_AS < $suggest) {
      $suggest = $committed_AS;
    }

    if(defined('ALWAYS_USE_COMMITTED_AS')) {
      if(ALWAYS_USE_COMMITTED_AS == true) {
        $suggest = $committed_AS;
      }
    }
    
    /* End Committed_AS Code */
    
    if(defined('MAX_MEMORY')) {
      if($suggest > MAX_MEMORY) {
        $suggest = MAX_MEMORY;
      }
    }
    
    if(defined('MIN_MEMORY')) {
      if($suggest < MIN_MEMORY) {
        $suggest = MIN_MEMORY;
      }
    }
    
    return $suggest;
  }
  
  function get_committed_as() {
    $committed_AS = exec("grep Committed_AS /proc/meminfo | awk {'print $2'};");
    $committed_AS = round(($committed_AS / 1024), 0);
    return $committed_AS;
  }
  
  function get_cached_memory() {
    $cached_memory = exec("grep '^Cached:' /proc/meminfo | awk {'print $2'};");
    $cached_memory = round(($cached_memory / 1024), 0);
    return $cached_memory;
  }
  
  function get_used_memory() {
    $used_memory = round(($this->get_total_memory() - $this->get_free_memory()));
    return $used_memory;
  }
  
  function get_total_memory() {
    $total_memory = exec("grep MemTotal /proc/meminfo | awk {'print $2'};")  / 1024;
    return $total_memory;
  }
  
  function get_free_memory() {
    $free_memory = exec("grep MemFree /proc/meminfo | awk {'print $2'};")  / 1024;
    return $free_memory;
  }
  
  function get_daily_average() {
    $api_vps = new DreamHost_VPS_Commands(DH_API_KEY, HOSTNAME);
    if($api_vps->list_usage(HOSTNAME)) {
      $results = DreamHost_API::get_final_results();
    }
    foreach($results as $array) {
      if(stripos($array['stamp'], "00:00:00")) {
        $memory['memory'] += $array['memory_mb'];
        $total_records++;
      }
    }
    $average_memory = round($memory['memory'] / $total_records);
    return $average_memory;
  }
  /* Memory Related Functions */
  
  /* Install Related Functions */
  function setup_install() {
  	
	if(file_exists($this->config_file)) {
		$this->template->assign("message", 'Delete config.php if you want to re-install.');
	} else {
	    switch ($this->module_action) {
	      case "write":
	        $apiKey = $_POST['dh_api_key'];
	        $user = $_POST['username'];
	        $pass = $_POST['password1'];
	        $verify_pass = $_POST['password2'];
	        $salt = uniqid(mt_rand(), true); //Make it longer!
	        $email = $_POST['email'];
	        if($this->check_install_variables($apiKey, $user, $pass, $verify_pass, $email)) {
		        if($this->test_api($apiKey)) {
		          $this->write_config($apiKey, $user, $pass, $salt, $email);
		        }
	        }
	      break;
	
	      default:
	        $this->template->assign("apikey", "");
	        $this->template->assign("themes", $this->get_templates());
	      break;
	    }
	}
	
  }

  function check_install_variables($key, $user, $pass, $verify_pass, $email) {
  	preg_match('/^[A-Z0-9]+$/', $key, $matches);
	$key = $matches[0];
	if(trim($key) == '') {
		$this->template->assign("message", 'Your API key is invalid!');
		return false;
	}

  	preg_match('/^[a-zA-Z0-9._-]+$/', $user, $matches);
	$user = $matches[0];
	if(trim($user) == '') {
		$this->template->assign("message", 'Your username is invalid! Do not user special characters.');
		return false;
	}

	if($pass != $verify_pass) {
		$this->template->assign("message", 'Your passwords do not match!');
		return false;
	}
	
	if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$this->template->assign("message", 'Your email address is invalid!');
		return false;		
	}

	return true;

  }
  
  function get_templates() {
    $folderHandle = opendir($this->template_folder);
    while($theme = readdir($folderHandle)) {
      if($theme != ".." && $theme != ".") {
        if($this->template_folder . $theme) {
          $themes[] = $theme;
        }
      }
    }
    return $themes;
  }
  
  function test_api($api_key) {
    $serverName = exec('hostname');
    $API_Meta = new DreamHost_API_Meta_Commands($api_key, $serverName);
    $found_set_size = false;
    $found_progess = false;
    if($API_Meta->list_accessible_cmds()) {
      $cmds = $API_Meta->get_final_results();
      foreach($cmds as $commands) {
        foreach($commands as $k=>$cmd) {
          if($cmd == "dreamhost_ps-set_size") {
            $found_set_size = true;
          } elseif($cmd == "services-progress") {
            $found_progress = true;
          }
        }
      }
      if($found_set_size == true && $found_progress == true) {
        return true;
      } else {
        $this->template->assign("message", 'Your API key could not be verified!<br /> ' .
        '<a href="javascript:history.go(-1)">Double check</a> the key you entered and ' .
        'make sure it has access to dreamhost_ps-set_size and services-progress.');
        return false;
      }
    } else {
      $this->template->assign("message", 'Your API key could not be verified! Could not run list_accessible_cmds!<br />');
      return false;
    }
  }
  
  function write_config($api_key, $user, $pass, $salt, $email, $theme = "amazing", $hostname = "false", $min_memory = "300", $max_memory = "4000", $safety_percent = "20", $use_committed_as = "false", $log_all = "false", $change_memory = "true", $ignore_cache = "false", $email_on_resize = "false", $tweet_on_resize = "false", $tweet_consumer_key = "", $tweet_consumer_secret = "", $tweet_oauth_token = "", $tweet_oauth_secret = "") {
    
    if($hostname == "false") {
      $hostname = exec('hostname');
    }

    $user_info = explode("|", exec("whoami | awk {'print \"grep \" $1 \" /etc/passwd\"'} | sh | awk -F: {'print $3 \"|\" $4'}"));
    
    $sample_config = file_get_contents($this->config_sample_file);

    $new_config = str_replace("--DHAPIKEY--", $api_key, $sample_config); //Update sample first
    $new_config = str_replace("--USER--", $user, $new_config);
    $new_config = str_replace("--PASS--", md5($salt . $pass), $new_config);
    $new_config = str_replace("--SALT--", $salt, $new_config);
    $new_config = str_replace("--EMAIL--", $email, $new_config);
    $new_config = str_replace("--TEMPLATE--", $theme, $new_config);
    $new_config = str_replace("--HOSTNAME--", $hostname, $new_config);
    $new_config = str_replace("--MIN_MEMORY--", $min_memory, $new_config);
    $new_config = str_replace("--MAX_MEMORY--", $max_memory, $new_config);
    $new_config = str_replace("'--SAFETY_PERCENT--'", $safety_percent, $new_config);
    $new_config = str_replace("--DAEMON_USER--", $user_info[0], $new_config);
    $new_config = str_replace("--DAEMON_GROUP--", $user_info[1], $new_config);
    $new_config = str_replace("'--ALWAYS_USE_COMMITTED_AS--'", $use_committed_as, $new_config);
    $new_config = str_replace("'--LOG_ALL--'", $log_all, $new_config);
    $new_config = str_replace("'--CHANGE_MEMORY--'", $change_memory, $new_config);
    $new_config = str_replace("'--IGNORE_CACHE--'", $ignore_cache, $new_config);
    $new_config = str_replace("'--EMAIL_ON_RESIZE--'", $email_on_resize, $new_config);
    $new_config = str_replace("'--TWEET_ON_RESIZE--'", $tweet_on_resize, $new_config);
    $new_config = str_replace("--TWEET_CONSUMER_KEY--", $tweet_consumer_key, $new_config);
    $new_config = str_replace("--TWEET_CONSUMER_SECRET--", $tweet_consumer_secret, $new_config);
    $new_config = str_replace("--TWEET_OAUTH_TOKEN--", $tweet_oauth_token, $new_config);
    $new_config = str_replace("--TWEET_OAUTH_SECRET--", $tweet_oauth_secret, $new_config);

    $fh = fopen($this->config_file, 'w');
    if(fwrite($fh, $new_config) === FALSE) {
	  $this->template->assign("message", 'There was an error writing your config file. Check permissions.');
      return false;
    } else {
	  $this->template->assign("message", 'Success! Go to the <a href="index.php">login page</a>!');
      return true;
    }
  }
  /* End Install Related Functions */

  /* Login Related Functions */
  
  function check_login($user, $pass) {
  	if($user != USER) {
	  $this->template->assign("message", 'Username not found!');
  	  return false;
  	}

    $pass = md5(SALT . $pass);
  	if($pass != PASS) {
	  $this->template->assign("message", 'Password incorrect!');
  	  return false;
  	}
	return true;
  }
  
  function set_logged_in() {
  	$key = uniqid(mt_rand(), true);
	setcookie('logged_in', $key);
	$this->set_login_file($key);
  }

  function set_login_file($key) {
  	global $system_url;
	$current_time = time();
	$expiration_time = $current_time + 86400;
  	if(file_exists($this->login_file)) {
		require($this->login_file);
    	foreach($login_array as $array) {
    		if($current_time < $array['timeout']){
    			$new_login_array .= 'array("key" => \'' . $array['key'] . '\', "timeout" => ' . $array['timeout'] . '),' . "\n";
    		}
    	}
	}
	$new_login_array .= 'array("key" => \'' . $key . '\', "timeout" => ' . $expiration_time . ')';
  	$file_contents = '<?php 
if(basename($_SERVER[\'SCRIPT_FILENAME\']) == \'login.php\') {
  die(\'You may not access this file directly.\'); 
} 
$login_array = array(
	' . $new_login_array . '
	);
?>';
    $fh = fopen($this->login_file, 'w');
	fwrite($fh, $file_contents);
	fclose($fh);
  }
  
  function is_logged_in() {

	$success = false;

  	if(file_exists($this->login_file)) {
		require($this->login_file);
		if(array_key_exists('logged_in', $_COOKIE)) {
			foreach($login_array as $current_array) {
				if($_COOKIE['logged_in'] == $current_array['key']) {
					$success = true;
				break;
				}
			}
		}
  	}

	return $success;
	
  }
  
  /* End Login Related Functions */
  
  /* News Related Functions */
  
  function get_news() {
    global $application_version;
    $curl_handle = curl_init();
    
    curl_setopt($curl_handle, CURLOPT_URL, "http://www.gimmesoda.com/mm_news.txt");
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl_handle, CURLOPT_HEADER, 0);
    
    $news_results = curl_exec($curl_handle);
    $request_info = curl_getinfo($curl_handle);

    //print_r($request_info);

    if($request_info['http_code'] != 200) {
       $news_results = "Unable to get latest news.";
    }
        
    curl_close($curl_handle);
    $curl_handle = null;

    return "Running Version: $application_version | " . $news_results;
  }
  
  /* End News Related Functions */
  
  /* Communication Functions */
  
  function communicate($old_memory, $new_memory) {
    global $system_path;
    $message = "The VPS " . HOSTNAME . " has been resized from " . $old_memory . "MB to " . $new_memory . "MB";
    if(EMAIL_ON_RESIZE == true) {
      mail(EMAIL, HOSTNAME . ": Server Resized to " . $new_memory . "MB", $message . "
If you'd like to contact JJ with any bugs or questions just respond to this email!", 'Reply-To: jj@gimmesoda.com');
    }

    if(TWEET_ON_RESIZE == true) {
      require($system_path . "/libs/Twitter_OAuth/twitteroauth.php");

      $tweet = new TwitterOAuth(TWEET_CONSUMER_KEY, TWEET_CONSUMER_SECRET, TWEET_OAUTH_TOKEN, TWEET_OAUTH_SECRET);
      $tweet->useragent = "JJ's VPS Memory Manager";

      $tweet->post('statuses/update', array('status' => $message));
    }

  }
  
  /* End Communication Functions */
  
}

?>