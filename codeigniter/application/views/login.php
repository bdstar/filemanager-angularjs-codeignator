<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
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
			<?php if(validation_errors() != false) { ?>
			<ul class="efile-ui-login-errors">
				<?php	echo validation_errors('<li>', '</li>'); ?>
			</ul>
			<?php } ?>
<?php 
	$attributes = array('class' => 'efile-ui-login-form');
	echo form_open('loginverify', $attributes);
?>
	<label class="efile-ui-login-label">Username:</label>
	<input class="efile-ui-login-input" name="username" type="text" />
	<label class="efile-ui-login-label">Password:</label>
	<input class="efile-ui-login-input" name="password" type="password" />
	<input class="efile-ui-login-btn" type="submit" value="Submit" />
<?php
	echo form_close();
?>
			</div>
		</div>
	</div>
</div>

</body>
</html>