<!DOCTYPE html>
<html>
<head>
    <title>{$application_name}</title>
	<link href='http://fonts.googleapis.com/css?family=PT+Sans:400,700' rel='stylesheet' type='text/css'>
    <link type="text/css" rel=stylesheet href="templates/amazing/css/style.css"/>
    <script type="text/javascript" src="libs/javascript/flot/jquery.min.js"></script>
    <script type="text/javascript" src="libs/javascript/flot/jquery.flot.js"></script>
    <script type="text/javascript" src="libs/javascript/flot/jquery.flot.selection.js"></script>
    <!--[if lte IE 8]><script language="javascript" type="text/javascript" src="libs/javascript/flot/excanvas.min.js"></script><![endif]-->
    <script type="text/javascript">
        {literal}
        var toggle = "hide";
        var tm;
        var am;
        var load1;
        var load5;
        var load15;

        function load_daemon_log() {
          $('#daemonLog').load('index.php?action=memory_log');
        }
        
        function load_graph_data() {
          $('#GraphCanvas').load('index.php?action=graph');
        }
        
        function get_command_list() {
          $('#AppButtons').load('index.php?action=commands');
        }
        
        function disable_daemon() {
          $.get('index.php?action=commands&do=disable_daemon');
          $('#AppButtons').html('<h2 id="running"></h2>');
          $('#running').html('This command takes awhile, please wait...');
          $timeoutCommand = window.setTimeout(get_command_list, 10000);
        }

        function enable_daemon() {
          $.get('index.php?action=commands&do=enable_daemon');
          $('#AppButtons').html('<h2 id="running"></h2>');
          $('#running').html('This command takes awhile, please wait...');
          $timeoutCommand = window.setTimeout(get_command_list, 7000);
          load_graph_data();
        }
        
        function clear_logs() {
          $.get('index.php?action=commands&do=clear_logs');
          $('#AppButtons').html('<h2 id="running"></h2>');
          $('#running').html('This command is quick, determining command list...');
          load_graph_data();
          $timeoutCommand = window.setTimeout(get_command_list, 3000);
        }

        function install_cron() {
          $.get('index.php?action=commands&do=add_cron');
          $('#AppButtons').html('<h2 id="running"></h2>');
          $('#running').html('This command is quick, determining command list...');
          $timeoutCommand = window.setTimeout(get_command_list, 3000);
        }

        function remove_cron() {
          $.get('index.php?action=commands&do=remove_cron');
          $('#AppButtons').html('<h2 id="running"></h2>');
          $('#running').html('This command is quick, determining command list...');
          $timeoutCommand = window.setTimeout(get_command_list, 3000);
        }
        
        function reload_daemon() {
          $.get('index.php?action=commands&do=reload_daemon');
          $('#AppButtons').html('<h2 id="running"></h2>');
          $('#running').html('This command takes awhile, please wait...');
          load_graph_data();
          $timeoutCommand = window.setTimeout(get_command_list, 12000);
        }
        
        function get_news() {
          $('#news').load('index.php?action=commands&do=get_news');
        }
        
        function show_hide_logs() {
            if(toggle == "show") {
                $('#logbox').animate({"right": "+=605px"}, "slow");
                toggle = "hide";
            } else {
                $('#logbox').animate({"right": "-=605px"}, "slow");
                toggle = "show";
            }
        }

        $(document).ready( function() {
            $('hidden').hide();
            var refreshLog = setInterval( function() {
                load_daemon_log();
            }, 5000);

            var refreshGraph = setInterval( function() {
                load_graph_data();
            }, 300000);
            load_graph_data();
            get_command_list();
            get_news();
            show_hide_logs();
        });
        {/literal}
    </script>
</head>
<body>
	<div id="wrapperApp">
		<div id="logo">
		</div>
		<div id="App">
			<div id="AppStatus">
				<h2 id="news"></h2>
			</div>
			<div id="AppButtons"></div>
			<div id="Graph">
				<h1>Memory and Load Graph</h1>
				<div id="GraphContainer">
					<div id="GraphCanvas">
                        {include file="graph.tpl"}
					</div>
				</div>
				<div id="GraphCaption">
				In the graph above, click and drag your cursor to zoom in.
				</div>
			</div>
		</div>
	</div>
		<div id="logbox">
        	<div id="logIcon" onclick="javascript:show_hide_logs();"><img src="templates/amazing/images/view_log.png"><span id="viewLog">Review Logs</span></div>
        <p><div id="daemonLog">Loading Daemon Log....
        </div></p>
        <span id="logLink"><a href="var/logs/memory" target="_blank">Memory Manager Log</a> | <a href='var/logs/processes' target='_blank'>Process Log</a></span>
        </div>
</body>
</html>
