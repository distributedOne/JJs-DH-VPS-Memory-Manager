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
$daemon = new MemoryManagerDaemon;
$daemon->main();

?>