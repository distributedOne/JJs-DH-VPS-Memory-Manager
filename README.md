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

Credits and Thanks
-----------------

Many thanks to the various people who have contributed their time and efforts in getting this application tested, released, and themed. In no particular order:

DreamHost: For giving me such an amazing career that I want to work on things like this on my free time.

GY'ers: An extended family, thanks for putting up with all the rants and ramblings about VPS services and everything else!

Tyler Brekke: Theme development/implementation, and an awesome co-worker.

Kan Adachi: Thanks for putting your amazing skills to work and making this such a polished looking product.

Samuel Corral: Testing and encouragement from my earliest versions, another amazing co-worker.

Sarah Galvez: Even after all these years, you still put up (and love me) while so much of my free time is spent on development. <3