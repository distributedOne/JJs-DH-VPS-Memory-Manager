<?php

$application_name = "JJ's VPS Memory Manager";
$application_version = "v1.1.3";

if($_SERVER['SERVER_PORT'] == 443) { $protocol = "https"; } else { $protocol = "http"; }
$system_url = $protocol . "://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
if(!array_key_exists('action', $_REQUEST)) { $system_action = 'index'; } else { $system_action = $_REQUEST['action']; }
if(!array_key_exists('do', $_REQUEST)) { $module_action = false; } else { $module_action = $_REQUEST['do']; }

$system_path = dirname(__FILE__);

if($system_action != "install") {
  if(!file_exists($system_path . "/config.php")) {
    header("Location:" . $system_url . "?action=install"); exit(); //Goto Install + Exit
    exit();
  } else {
    require($system_path . "/config.php");
  }
} else { //Set the needed defines for install
  define('TEMPLATE', 'amazing'); //default template
}

require($system_path . "/libs/DH_MM/memory_manager.php");
require($system_path . "/libs/DH_API/dreamhost_api.php");
require($system_path . "/libs/Smarty/custom.php");
require($system_path . "/libs/Linux_Cron/cron.class.php");
require($system_path . "/libs/System_Daemon/System/Daemon.php");

$template = new Smarty_DH_VPS_MM;
$memory_manager = new MemoryManager;

$options = array(
  'appName' => 'DreamHost_VPS_Memory_Manager',
  'appDir' => dirname(__FILE__),
  'appDescription' => 'Monitors and manages memory for a DreamHost Web VPS.',
  'authorName' => 'JuanJose Galvez',
  'authorEmail' => 'jj@gimmesoda.com',
  'sysMaxExecutionTime' => '0',
  'sysMaxInputTime' => '0',
  'sysMemoryLimit' => '128M',
  'logLocation' => $system_path . "/var/logs/memory",
  'appPidLocation' => $system_path . "/var/run/DreamHost_VPS_Memory_Manager/DreamHost_VPS_Memory_Manager.pid",
  'appStopFile' => $system_path . "/var/run/DreamHost_VPS_Memory_Manager/stop",
  'usePEAR' => false,
  'appRunAsUID' => DAEMON_USER,
  'appRunAsGID' => DAEMON_GROUP,
  );

System_Daemon::setOptions($options);

$template->assign("application_name", $application_name);

if($system_action == 'index') {
  $template->assign("index", "true");
}

header('Cache-Control: no-cache');
header('Pragma: no-cache');

?>