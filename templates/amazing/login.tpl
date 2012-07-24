<!DOCTYPE html>
<html>
<head>
  <title>{$application_name}</title>
  <link type="text/css" rel=stylesheet href="templates/amazing/css/style.css"/>
  <link href='http://fonts.googleapis.com/css?family=PT+Sans:400,700' rel='stylesheet' type='text/css'>
</head>
<body>
  <div id="wrapper">
    <div id="logo">
    </div>
    <div id="graphIcon">
    </div>
    <div id="login">
      <div id="titleBar">
        <h1>Login{if $message}: {$message} {/if}</h1>
      </div>
      <form action="index.php" method="POST">
        <div id="input">
          Username:<input type="text" name="username" id="username" />
          Password:<input type="password" name="password" id="password" />
        </div>
        <div id="loginBar">
          Need help? <a href="mailto:jj@gimmesoda.com">Contact JJ.</a>
          <button type="submit" name="submit" value="Login"><span>Login</span></button>
        </div>
        <input type="hidden" name="action" id="action" value="login" />
        <input type="hidden" name="do" id="do" value="login" />
      </form>
    </div>
  </div>
</body>