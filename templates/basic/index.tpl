<!--Force IE6 into quirks mode with this comment tag-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <title>{$application_name}</title>

        <link type="text/css" rel=stylesheet href="templates/basic/css/style.css"/>
        <script type="text/javascript" src="libs/javascript/jquery/1.4.4/jquery.min.js"></script>
        <script type="text/javascript" src="libs/javascript/flot/jquery.flot.js"></script>
        <script type="text/javascript" src="libs/javascript/flot/jquery.flot.selection.js"></script>
        <!--[if IE]><script language="javascript" type="text/javascript" src="/templates/wheretohost/js/excanvas.pack.js"></script><![endif]-->

        <script type="text/javascript">
            {literal}
            function load_daemon_log() {
              $('#daemonLog').load('index.php?action=memory_log');
            }
            
            function load_graph_data() {
              $('#graphData').load('index.php?action=graph');
            }
            
            function get_command_list() {
              $('#commandlist').load('index.php?action=commands');
            }
            
            function disable_daemon() {
              $.get('index.php?action=commands&do=disable_daemon');
              $('#commandlist').html('This command takes awhile, please wait...');
              $timeoutCommand = window.setTimeout(get_command_list, 10000);
            }

            function enable_daemon() {
              $.get('index.php?action=commands&do=enable_daemon');
              $('#commandlist').html('This command takes awhile, please wait...');
              $timeoutCommand = window.setTimeout(get_command_list, 7000);
              load_graph_data();
            }
            
            function clear_logs() {
              $.get('index.php?action=commands&do=clear_logs');
              $('#commandlist').html('This command is quick, determining command list...');
              load_graph_data();
              $timeoutCommand = window.setTimeout(get_command_list, 3000);
            }

            function install_cron() {
              $.get('index.php?action=commands&do=add_cron');
              $('#commandlist').html('This command is quick, determining command list...');
              $timeoutCommand = window.setTimeout(get_command_list, 3000);
            }

            function remove_cron() {
              $.get('index.php?action=commands&do=remove_cron');
              $('#commandlist').html('This command is quick, determining command list...');
              $timeoutCommand = window.setTimeout(get_command_list, 3000);
            }
            
            function reload_daemon() {
              $.get('index.php?action=commands&do=reload_daemon');
              $('#commandlist').html('This command takes awhile, please wait...');
              load_graph_data();
              $timeoutCommand = window.setTimeout(get_command_list, 12000);
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
            });
            {/literal}
        </script>
    </head>
    <body>
        <div id="maincontent">
            <div class="innertube">
              {if $index == 'true'}
              <div id="commandlist">Determining Command List</div>
              {/if}
              <div id="graphData" style="text-align:center;" align="center">
                {include file="graph.tpl"}
              </div>
            </div>
        </div>
        {include file="top_frame.tpl"}
        {include file="bottom_frame.tpl"}
    </body>
</html>