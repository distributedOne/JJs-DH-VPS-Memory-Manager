<?php

if(basename($_SERVER['SCRIPT_FILENAME']) == 'config.php') {
  die('You may not access the config file directly.');
}

define('DH_API_KEY', '--DHAPIKEY--');
define('USER', '--USER--');
define('PASS', '--PASS--');
define('SALT', '--SALT--');
define('EMAIL', '--EMAIL--');
define('TEMPLATE', '--TEMPLATE--');
define('HOSTNAME', '--HOSTNAME--');
define('MIN_MEMORY', '--MIN_MEMORY--');
define('MAX_MEMORY', '--MAX_MEMORY--');
define('SAFETY_PERCENT', '--SAFETY_PERCENT--');
define('DAEMON_USER', '--DAEMON_USER--');
define('DAEMON_GROUP', '--DAEMON_GROUP--');
define('ALWAYS_USE_COMMITTED_AS', '--ALWAYS_USE_COMMITTED_AS--');
define('LOG_ALL', '--LOG_ALL--');

?>