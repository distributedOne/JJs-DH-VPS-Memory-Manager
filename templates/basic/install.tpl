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
                    <label for="dh_api_key"><h2 id="apitext">DreamHost API Key</h1></label>
                    	<p>Your key MUST have access to the following functions:<br />
                			&#42; <strong>dreamhost_ps-set_size</strong> &#42;<br />
                			&#42; <strong>services-progress</strong> &#42;
                    	</p>
                    <input type="text" name="dh_api_key" id="dh_api_key" value="{$apikey}" />
                    <label for="username"><h2 id="apitext">Create A User Login</h1></label>
                    	<p>This will be your login for the memory manager!</p>
                    <input type="text" name="username" id="username" value="{$username}" />
                    <label for="password1"><h2 id="apitext">Create A Password</h1></label>
                    <input type="password" name="password1" id="password1" value="{$password1}" />
                    <label for="password2"><h2 id="apitext">Repeat Password</h1></label>
                    <input type="password" name="password2" id="password2" value="" />
                    <label for="email"><h2 id="apitext">Email Address</h1></label>
                    <input type="text" name="email" id="email" value="" />
                    <br/>
                    <input type="hidden" name="action" id="action" value="install" />
                    <input type="hidden" name="do" id="do" value="write" />
                    <input type="submit" style="font-size:20px; font-family:Century Gothic,sans-serif; font-style:italic; background-color:#7b91a2; border-style:double;" name="submit" value="Config" />
                </form>
                {/if}
            </div>
        </div>
        {include file="top_frame.tpl"}
        <div id="bottomFrame">
            <div class="innertube">
                <div id="daemonLog" style="text-align:center;">
                    <h3>Need help installing? Contact <a href="mailto:jj@gimmesoda.com">JJ!</a></h3>
                </div>
            </div>
        </div>
    </body>
</html>