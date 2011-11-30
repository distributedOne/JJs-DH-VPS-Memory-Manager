<!DOCTYPE html>
<html>
<head>
    <title>{$application_name} Install</title>
	<link href='http://fonts.googleapis.com/css?family=PT+Sans:400,700' rel='stylesheet' type='text/css'>
    <link type="text/css" rel='stylesheet' href="templates/amazing/css/style.css"/>
    <link type="text/css" rel='stylesheet' href="templates/amazing/css/install.css"/>
</head>
<body>
	<div id="wrapper">
		<div id="logo">
		</div>
		<div id="graphIcon">
		</div>
		<div id="install">
			<div id="titleBar">
				<h1>Installation{if $message}: {$message} {/if}</h1>
			</div>
			<form action="index.php" method="POST">
				<div id="installInput">
					<span>DreamHost API Key:</span><input type="text" id="dh_api_key" name="dh_api_key" value="{$apikey}"/>
					<span>Choose a Username:</span><input type="text" name="username" id="username" value="{$username}" />
					<span>Create Password:</span><input type="password" name="password1" id="password1" value="{$password1}" />
					<span>Repeat Password:</span><input type="password" name="password2" id="password2" value="" />
					<span>Email Address:</span><input type="text" name="email" id="email" value="" />
					<p>Need help? <a href="mailto:jj@gimmesoda.com">Contact JJ.</a></p>
                    <input type="hidden" name="action" id="action" value="install" />
                    <input type="hidden" name="do" id="do" value="write" />
					<button type="submit" name="configbutton" value="Config">Configure</button>
				</div>
			</form>
		</div>
	</div>
</body>
</html>