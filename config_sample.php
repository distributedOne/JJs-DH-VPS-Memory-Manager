<?php

if(basename($_SERVER['SCRIPT_FILENAME']) == 'config.php') {
  die('You may not access the config file directly.');
}

# Required Settings
define('DH_API_KEY', '--DHAPIKEY--');
define('USER', '--USER--');
define('PASS', '--PASS--');
define('SALT', '--SALT--');
define('EMAIL', '--EMAIL--');

# Application Tuning
define('HOSTNAME', '--HOSTNAME--');
define('MIN_MEMORY', '--MIN_MEMORY--');
define('MAX_MEMORY', '--MAX_MEMORY--');
define('SAFETY_PERCENT', '--SAFETY_PERCENT--');
define('TEMPLATE', '--TEMPLATE--');
define('ALWAYS_USE_COMMITTED_AS', '--ALWAYS_USE_COMMITTED_AS--');
define('LOG_ALL', '--LOG_ALL--');
define('IGNORE_CACHE', '--IGNORE_CACHE--');

# Daemon Settings
define('DAEMON_USER', '--DAEMON_USER--');
define('DAEMON_GROUP', '--DAEMON_GROUP--');
define('CHANGE_MEMORY', '--CHANGE_MEMORY--');
define('SECONDS_BEFORE_DECREASE', 1800);
define('LOG_EVERY_X_MINUTES', 5);
define('MAX_CHECKS_BEFORE_GIVING_UP', 50);
define('SECONDS_TO_WAIT_BETWEEN_MEMORY_CHECKS', 5);
define('SECONDS_TO_WAIT_BETWEEN_RESIZE_STATUS_CHECKS', 5);
define('DAEMON_TIMEZONE', 'America/Los_Angeles');

# Contact Settings
define('EMAIL_ON_RESIZE', '--EMAIL_ON_RESIZE--');
define('TWEET_ON_RESIZE', '--TWEET_ON_RESIZE--');

# Tweet Settings
# Register the Memory Manager with Twitter
# https://dev.twitter.com/apps/new
define('TWEET_CONSUMER_KEY', '--TWEET_CONSUMER_KEY--');
define('TWEET_CONSUMER_SECRET', '--TWEET_CONSUMER_SECRET--');
define('TWEET_OAUTH_TOKEN', '--TWEET_OAUTH_TOKEN--');
define('TWEET_OAUTH_SECRET', '--TWEET_OAUTH_SECRET--');

?>