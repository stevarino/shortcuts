<?php

DEFINE('APP_CONFIG', './app.json');
$APP = json_decode(file_get_contents(APP_CONFIG), true);
$ERRORS = [];


function getGoogleClient() {
	// https://github.com/googleapis/google-api-php-client/blob/main/examples/simple-file-upload.php
	require_once __DIR__ . '/../composer/vendor/autoload.php';
	$client = new Google\Client();

	$client->setAuthConfig([
		'client_id' => $GLOBALS['APP']['client_id'],
		'client_secret' => $GLOBALS['APP']['client_secret'],
	]);	
	$client->setRedirectUri($GLOBALS['APP']['url']);
	return $client;
}

function getOauthService($client) {
	$service = new Google\Service\Oauth2($client);
	$client->addScope(Google\Service\Oauth2::USERINFO_EMAIL);
	return $service;
}

function redirect($url = null) {
	$url = is_null($url)  ? $GLOBALS['APP']['url'] : $url;
	header('Location: ' . $url);
	exit();
}

function isAuthorized() {
	return isset($_SESSION['email']) && in_array($_SESSION['email'], $GLOBALS['APP']['auth']);
}

function isLoggedIn() {
	return isset($_SESSION['email']);
}

function getLink($link) {
	if ($link == 'login') login();
	if ($link == 'logout') logout();
	if (isset($GLOBALS['APP']['links'][$link])) redirect($GLOBALS['APP']['links'][$link]['url']);
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
	print('Page not found.');
	die();
}

function logout() {
	session_start();
	unset($_SESSION['email']);
	redirect();
}

function login($code=null) {
	$client = getGoogleClient();
	$service = getOauthService($client);
	if (is_null($code)) {
		redirect($client->createAuthUrl());
	} else {
		$token = $client->fetchAccessTokenWithAuthCode($code);
		$client->setAccessToken($token);

		$_SESSION['email'] = $service->userinfo->get()['email'];
    	redirect();
	}
}

function save() {
	$contents = json_encode($GLOBALS['APP'], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
	file_put_contents(APP_CONFIG, $contents);
	redirect();
}

function logError($error) {
	array_push($GLOBALS['ERRORS'], $error);
}

function handlePost() {
	$auth = &$GLOBALS['APP']['auth'];
	$links = &$GLOBALS['APP']['links'];
	if(isset($_POST['url'])) {
		if (0 === preg_match('/^[a-z]+://.*$/', $_POST['url'])) {
			logError('Invalid URL');
		} else {
			logError('Invalid email address.');
			$links[$_POST['name']] = array(
				'url' => $_POST['url'], 'author' => $_SESSION['email'], 'created' => time()
			);
			save();
		}
	}
	
	if (isset($_POST['del_url'])) {
		if (isset($links[$_POST['del_url']])) {
			unset($links[$_POST['del_url']]);
			save();
		} else {
			logError('URL not set.');
		}
	}

	if (isset($_POST['del_user'])) {
		$i = array_search($_POST['del_user'], $auth);
		if ($i === false) {
			logError('User not found.');
		} else {
			unset($auth[$i]);
			save();
		}
	}
	if (isset($_POST['new_user'])) {
		if (in_array($_POST['new_user'], $auth)) {
			logError('User already exists.');
		} elseif (0 === preg_match('/^[^@]+@.+[.].+$/', $_POST['new_user'])) {
			logError('Invalid email address.');
		} else {
			array_push($auth, $_POST['new_user']);
			save();
		}
	}
}


if(isset($_GET['link'])) getLink($_GET['link']);
session_start();

if (isset($_GET['code'])) login($_GET['code']);
if (isAuthorized() && !empty($_POST)) handlePost();

?><html><head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-aFq/bzH65dt+w6FI2ooMVUpc+21e0SRygnTpmBvdBgSdnuTN7QbdgL+OapgHtvPp" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/js/bootstrap.bundle.min.js" integrity="sha384-qKXV1j0HvMUeCBQ+QVp7JcfGl760yU08IQ+GpUo5hlbpg51QRiuqHAJz8+BrxE/N" crossorigin="anonymous"></script>	

<link href='/static/style.css' rel='stylesheet' />
<meta name="viewport" content="width=device-width, initial-scale=1" />

<title>Shortcuts</title></head><body>
<div class='header'>
<div class='container'>
<div class='row'>
<div class='col'>
<h1><img src='/static/icon-white.png'> Shortcuts</h1>
</div><div class='col text-end'>
<?php if(isLoggedIn()) { ?>
<p><?= $_SESSION['email'] ?><br /><a href='/logout'>Logout</a></p>
<?php } ?>
</div>
</div>
</div>
</div>
<div class="container">
<?php if (!isLoggedIn()) { ?>
<div class="container text-center"><a href='/login' class='btn btn-lg btn-primary'>Login With Google</a></div>
<?php } else { ?>
<?php if (isAuthorized()) { ?>
<?php if (!empty($ERRORS)) { ?>
<p style='color: red'><?= implode('<br />', $ERRORS) ?></p>
<?php } ?>
<h2>Links</h2>
<form method='POST' action='/'>
<div class="row">
	<div class='col'>
		<input name='name' class="form-control md-3" placeholder="Name" />

	</div><div class='col'>
		<input name='url' class="form-control" placeholder="URL" />
	
	</div><div class='col-auto'>
		<input type='submit' class='btn btn-outline-primary' value='Create' />
	</div>
</div>
</form>

<div class="container tablish  container-fluid">
<?php foreach($APP['links'] as $key => $val) { ?>
	<div class="row">
		<div class='col-auto'><a href='<?= trim($APP['url'], '/') ?>/<?= $key ?>'><?= $key ?></a></div>
		<div class='col bigly'><span><a href='<?= $val['url'] ?>'><?= $val['url'] ?></a></span></div>
		<div class='col-auto'><form method="post">
			<input type="hidden" name="del_url" value="<?= $key ?>" />
			<input type="submit" value="Delete" class='btn btn-outline-danger' />
		</form></div>
	</div>
<?php } ?>
</div>
<h2>Users</h2>
<form method='post'>
<div class="row">
	<div class='col'><input type='email' name='new_user' class="form-control" placeholder="Email Address" />
	
	</div><div class='col-auto'>
		<input type='submit' class='btn btn-outline-primary' value='Create' />
	</div>
</div></form> 

<?php foreach($APP['auth'] as $user) {
	$disabled = $user == $_SESSION['email'] ? 'disabled' : '';
	?>
	<div class="row">
		
		<div class='col bigly'><span><?= $user ?></span></div>
		<div class='col-auto'><form method="post">
			<input type="hidden" name="del_user" value="<?= $user ?>" />
			<input type="submit" value="Delete" class='btn btn-outline-danger' <?= $disabled ?> />
		</form></div>
	</div>
<?php } ?>
</table>
<?php
} // isAuthorized
} // isLoggedIn
?>
</div></body></html>