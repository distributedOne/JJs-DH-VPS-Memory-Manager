<!--Force IE6 into quirks mode with this comment tag-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <title>{$application_name}</title>
        <link type="text/css" rel=stylesheet href="templates/basic/css/style.css"/>
        <script type="text/javascript" src="libs/javascript/jquery/1.4.4/jquery.min.js"></script>
    </head>
    <body>
        <div id="maincontent">
            <div class="innertube" style="text-align:center;">
                {if $message} {$message} {else}
                <form action="index.php" method="POST">
                	<h1 id="apitext">Login Required</h1>
                    <label for="username"><span id="logintext">User</span></label>
                    <input type="text" name="username" id="username" value="{$username}" />
                    <label for="password"><span id="logintext">Pass</span></label>
                    <input type="password" name="password" id="password" value="" />
                    <br/>
                    <input type="hidden" name="action" id="action" value="login" />
                    <input type="hidden" name="do" id="do" value="login" />
                    <input type="submit" style="font-size:20px; font-family:Century Gothic,sans-serif; font-style:italic; background-color:#7b91a2; border-style:double;" name="submit" value="Login" />
                </form>
                {/if}
            </div>
        </div>
        {include file="top_frame.tpl"}
        <div id="bottomFrame">
            <div class="innertube">
                <div id="daemonLog" style="text-align:center;">
                    <h3>Need help? Contact <a href="mailto:jj@gimmesoda.com">JJ!</a></h3>
                </div>
            </div>
        </div>
    </body>
</html>