<?php
require('local_config.php');
require('../../lib/classSherport.php');

$tstart = $zeit_start = microtime(true); // current time
$serverName = $_SERVER['SERVER_NAME'];
$serverPath = dirname($_SERVER['SCRIPT_NAME']);
$serverSecure = isset($_SERVER['HTTPS']);
session_name($GLOBALS['config']['website']['cookieName']);
session_set_cookie_params(0, $serverPath, $serverName, $serverSecure, true);
session_start();

// create the sherport-object
$sherport = new classSherport();
global $html, $javafile, $javascript;

// check if user pressed the logout-button
if (!empty($_POST['logout'])) {
	unset($_SESSION['loggedIn']);
}
// check if this pageview is a login attemp
// it is a login attemp if the "t"-get parameter and the stored login-token exists
if (!empty($_GET['t']) && isset($_SESSION['token'])) {
	// import the stored login-token.
	// We need to use the token from the last call to loginInit, because the qr-code is connected to it
	$sherport->importTokenArray($_SESSION['token']);
	// call loginGetData to see if we user has successfully logged in
	if ($userData = $sherport->loginGetData($_GET['t'])) {
		// store the transmitted userdata for later display
		$_SESSION['userData'] = $userData;
		// For every login a unique id for this user is transmitted.
		// You can re-identify a user at a later login attemp
		// this id is different for every website, so you can not track users over websites with it.
		if ($userData['id.anonym']) {
			// In a database based user management you can use this id to give the user access to his own data.
			// If a user with this id not allready exist, you can create a new user with the transmitted user data.
			$_SESSION['loggedIn'] = true;
			// We can delete the stored token
			unset($_SESSION['token']);
		}
	}
}
$title = 'Sherport Example 1 - Login';
if (empty($_SESSION['loggedIn'])) {
	// What to show if not logged in
	
	// initialize the sherport object for use as login
	$sherport->loginInit($GLOBALS['config']['sherport']['consumerId']);

	// loginInit created a token which needs to be accessed after a page reload
	// so we need to store it somewhere
	$_SESSION['token'] = $sherport->exportTokenArray();

	// Include the sherport-javascript in the page header
	$javafile = $sherport->getJsLibHtml();

	// We must configure the sherport-javascript
	// token: the token which was generated by loginInit
	// urlSuccess: the url to which our webpage will be redirected on successful login
	$javascript = 'var sherportConfig={token: "'.$sherport->getToken().'", urlSuccess: "'.($serverSecure? 'https://': 'http://').$serverName.$serverPath.'/index.php"};';

	// The page html when not logged in
	// the html for display of the sherport qr-code will be generated by the function loginGetSnippet
	$html = '<div class="c66l">
	<div class="subcl">
	<h2>Forum 1-2-3</h2>
<p>Melden Sie sich an, in dem Sie den untenstehenden QR-Code z. B. mit Ihrem Smartphone einscannen.</p>
<p>Bei Ihrem ersten Login werden Sie gefragt, welche Ihrer Daten Sie dieser Seite übermitteln möchten. Bei nachfolgenden Logins werden Sie nur noch nach den Daten gefragt, wenn diese Seite mehr Daten von Ihnen haben möchte oder wenn Sie Ihre Daten auf der Sherport-Seite geändert haben.</p>
'.$sherport->loginGetSnippet('index.php?t='.$sherport->getToken()).'
<p>Sollten Sie nicht automatisch eingeloggt werden, klicken Sie bitte auf den Sherport-Code.</p>
	</div>
</div>
<div class="c33r">
	<div class="subcr">
		<fieldset class="login-box">
			<legend>Login</legend>
			<form action="'.($serverSecure? 'https://': 'http://').$serverName.$serverPath.'/index.php" method="post">
				<label for="username">Benutzername:</label>
				<input type="text" class="login-input" name="username" id="username" maxlength="20" />
				<label for="password">Passwort:</label>
				<input type="password" class="login-input" name="password" id="password" maxlength="20" /><br />
				<input type="submit" name="login" class="center" value="Login" />
				<h3>Noch keinen Zugang?</h3>
				<input type="submit" name="register" class="center" value="Registrieren" />
			</form>
		</fieldset>
	</div>
</div>';
}
else {
	// What to show if logged in

	// In the code who valideted the successful login, we have stored the transmitted userdata in the session
	$html = '<h2>Hallo '.$_SESSION['userData']['namePerson.first'].', Sie sind nun angemeldet.</h2>
<p>Sie haben sich mit Sherport-Login angemeldet. Sie können nun auf den geschützten Bereich dieser Seite zugreifen.</p>
<form action="'.($serverSecure? 'https://': 'http://').$serverName.$serverPath.'/index.php" method="post">
	<p>Um sich abzumelden, klicken Sie bitte den Logout-Button.</p>
	<input type="submit" name="logout" class="center" value="Logout" />
</form>
';
	// output the transmitted userdata if logged in
	$html.= '<p>Folgende Daten wurden uns von Sherport übermittelt:</p>'.formatData($_SESSION['userData']);

}

$template = file_get_contents('template.html');
$laufzeit = round(microtime(true) - $zeit_start, 4); // Laufzeit ermitteln
$footer = 'Seitenaufbau in '.$laufzeit.' Sekunden.';
$out = preg_replace_callback('/\$\{[A-Za-z_]+\}/', create_function(
	'$match',
	'return $GLOBALS[substr($match[0], 2, -1)];'
), $template);
echo $out;

// Utility function to format the userdata
function formatData($data) {
	$out = '<dl>';
	foreach ($data as $key => $value) {
		$out.= '<dt>'.$key.'</dt><dd>'.htmlspecialchars($value).'</dd>';
	}
	return $out.'</dl>';
}
