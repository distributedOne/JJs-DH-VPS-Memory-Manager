## 1.2.0 (2013-03-15)
* refactor: major code cleanup of the daemon.php, new MemoryManagerDaemon class
* bugfix: do not attempt resize below current actual memory usage
* bugfix: we should no longer resize down until at least 30 minutes has gone by from last resize 
* bugfix: cronjobs added properly for those who never entered a cronjob before (adds MAILTO="" line)
* feature: new configurable for seconds before decrease, use SECONDS_BEFORE_DECREASE in config.php defaults to 1800
* feature: new configurable for minutes between log entries used in graphing data, use LOG_EVERY_X_MINUTES in the config.php defaults to 5
* feature: new configurable for seconds between memory checks, use SECONDS_TO_WAIT_BETWEEN_MEMORY_CHECKS in the config.php defaults to 5
* feature: new configurable for number of checks on API progress before giving up, use MAX_CHECKS_BEFORE_GIVING_UP in the config.php defaults to 50
* feature: new configurable for seconds between API progress checks, use SECONDS_TO_WAIT_BETWEEN_RESIZE_STATUS_CHECKS in the config.php defaults to 5
* feature: new configurable for the daemon's timezone, use DAEMON_TIMEZONE in the config.php defaults to 'America/Los_Angeles'. Check your server's /usr/share/zoneinfo for supported timezones.
* extra: added a .gitignore for cache, run files, logs, and config.php

## 1.1.3 (2012-07-24)
* bugfix: uid/gid was being set incorrectly in some rare cases
* extra: formatting fixes

## 1.1.2 (2012-04-27)
* extra: removed work around for the now valid api.dreamhost.com cert
* extra: readme updated to explain SAFETY_PERCENTAGE

## 1.1.1 (2012-04-14)
* bugfix: daemon now cleans log
* bugfix: properly loading excanvas for IE
* extra: consolidated jquery/flot files
* extra: updated flot library	
* workaround: ignoring expired api.dreamhost.com cert

## 1.1.0 (2011-12-09)
* feature: process snapshot when change is requested
* feature: tweet on resize (uses oauth: see readme)
* feature: email on resize
* bugfix: cached memory was being reported in kb not mb
* bugfix: crontab needs a newline to properly RELOAD
* bugfix: prevent large error log when using cron
* bugfix: was looping through missing array when login.php does not exist
* extra: Added CHANGELOG

## 1.0.0 (2011-12-01)

* First stable release