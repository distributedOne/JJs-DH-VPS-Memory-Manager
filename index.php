<?php
/**
 * JJ's VPS Memory Manager
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

if(!$memory_manager->is_logged_in() && $system_action != 'install') {
	$system_action = 'login';
}

if($memory_manager->is_daemon_running() == false) {
  if($memory_manager->is_daemon_disabled() == false) {
    $memory_manager->start_daemon();
  }
}

switch($system_action) {

  case "commands":
    if($module_action) {
      $memory_manager->execute_menu_command($module_action);
    } else {
      $memory_manager->get_command_list();
    }
  break;

  case "install":
    $memory_manager->setup_install();
  break;

  case "index":
    //Just show the index template
  break;

  case "graph":
    $memory_manager->get_graph_data();
  break;
  
  case "config":
    //Show the config
  break;
  
  case "memory_log":
    $memory_manager->get_memory_log();
  break;
  
  case "login":
    if($module_action == 'login') {
  	  if($memory_manager->check_login($_POST['username'], $_POST['password'])) {
  	  	$memory_manager->set_logged_in();
		header("Location: $system_url");
  	  } else {
		setcookie('logged_in', '', time() - 360);
  	  }
    }
	//Show the login
  break;
  
  case "logout":
	setcookie('logged_in', '', time() - 360);
	header("Location: $system_url");
  break;
  
  default:
    //Show an error
    $system_action = "error";
    $template->assign('message','A valid action should be provided.');
  break;

}

$template->display($system_action . '.tpl');

?>