JJ's DH VPS Memory Manager
=========================

**IMPORTANT NOTE: While I work for DreamHost this application is NOT supported by DreamHost and any questions should be coming to me NOT DH Support!**

This application was built to be a VPS Memory Manager focused on uptime rather then cost savings. You can see one of my initial post on the subject here: http://www.gimmesoda.com/thoughts-on-vps-memory-management/

Installation
-----------

1. Grab the files using git or download them from github: https://github.com/jgalvez/JJs-DH-VPS-Memory-Manager
2. Upload the files.
3. Create an API key for use with the memory manager from here: https://panel.dreamhost.com/index.cgi?tree=home.api&

The key must have access to the following two functions:

dreamhost_ps-set_size
services-progress

4. Visit the URL you uploaded the memory manager, the install script will start.
5. Fill out the form that comes up, save the settings.

Configuration
------------

Once installed you should fine tune the application configuration. Let's look at the important values in the config.php file:

define('MIN_MEMORY', '300');
define('MAX_MEMORY', '4000');

MIN_MEMORY should be the minimum memory your VPS uses. 300 is default only because that is the lowest amount of memory you are allowed to use, change this to something more realistic.

MAX_MEMORY should be a maximum amount of memory you are willing to pay for, remember that the memory manager will only resize to this level if the memory is actually needed. You are not likely to be at this level of memory usage for long periods of time, if you are go buy a dedicated instead.

Restart the Daemon
-----------------

Anytime you make a change to the configuration file you should restart the daemon (using the restart link within the application).

That's it! Feel free to contact me at jj@gimmesoda.com with any questions.