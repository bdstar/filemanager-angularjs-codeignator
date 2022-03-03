<?php 
// AngularJS & PHP File Manager
// @author 
// @site 
// @copyright (c) 2022
?>
<?php
//======================================================
// Settings
//======================================================
define("USERNAME", "demo");
define("PASSWORD", "demo");
//======================================================

$username = null;
$password = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if(!empty($_POST["username"]) && !empty($_POST["password"])) {
		$username = $_POST["username"];
		$password = $_POST["password"];

		if($username == USERNAME && $password == PASSWORD) {
			session_start();
			$_SESSION["authenticated"] = 'true';
			$_SESSION["username"] = USERNAME;
			header('Location: index.php');
		} else {
			header('Location: login.php');
		}
	} else {
		header('Location: login.php');
	}
} else {
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<meta name="author" content="Max Lawrence">
	<meta name="copyright" content="Avirtum">
	<meta name="description" content="EasyFile is the angularjs and codeigniter powered script that lets you control your files and folders">
	<title>EasyFile - CodeIgniter File Manager</title>
	
<!-- css -->
	<link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="assets/css/style.css">
	<!-- /end css -->
</head>
<body>

<div class="efile-ui-header">
	<div class="efile-ui-header-inner">
		<div class="efile-ui-logo">
			<a href="/">&nbsp;</a>
		</div>
	</div>
</div>

<div class="efile-ui-data efile-ui-login">
	<div class="efile-ui-data-inner">
		<div class="efile-ui-block">
			<div class="efile-ui-block-header">
				<div class="efile-ui-block-title">Login</div>
			</div>
			<div class="efile-ui-block-content">
				<form class="efile-ui-login-form" method="post">
					<label class="efile-ui-login-label">Username:</label>
					<input class="efile-ui-login-input" name="username" type="text" />
					<label class="efile-ui-login-label">Password:</label>
					<input class="efile-ui-login-input" name="password" type="password" />
					<input class="efile-ui-login-btn" type="submit" value="Submit" />
				</form>
			</div>
		</div>
	</div>
</div>

</body>
</html>
<?php } ?>