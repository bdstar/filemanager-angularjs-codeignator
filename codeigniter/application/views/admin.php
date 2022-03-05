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
	<title>AngularJS & CodeIgniter File Manager</title>
	
	<!-- scripts -->
	<script src="assets/js/lib/jquery.min.js"></script>
	<script src="assets/js/lib/angular.min.js"></script>
	<script src="assets/js/lib/angular-animate.min.js"></script>
	<script src="assets/js/lib/angular-growl.js"></script>
	<script src="assets/js/lib/ng-file-upload.js"></script>
	<script src="assets/js/app.js"></script>
	<!-- /end scripts -->
	
	<!-- css -->
	<link rel="stylesheet" type="text/css" href="assets/css/angular-growl.css">
	<link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="assets/css/style.css">
	<!-- /end css -->
</head>
<body x-ng-app="ngEasyFileApp" x-ng-controller="ngEasyFileAppController" x-ng-init="appData.fn.init(appData);">

<div class="efile-ui-loading" x-ng-class="{'efile-ui-active': appData.inprocess > 0}">
	<div class="efile-ui-loading-progress">
		<div class="efile-ui-loading-color-01"></div>
		<div class="efile-ui-loading-color-02"></div>
		<div class="efile-ui-loading-color-03"></div>
		<div class="efile-ui-loading-color-04"></div>
	</div>
</div>

<div class="efile-ui-topbar">
	<div class="efile-ui-topbar-inner">
		<div class="efile-ui-toolbar">Hello, <span class="efile-ui-username"><?php echo $username; ?></span> | <a href="admin/logout">Logout</a></div>
	</div>
</div>

<div class="efile-ui-header">
	<div class="efile-ui-header-inner">
		<div class="efile-ui-logo">
			<a href="admin">&nbsp;</a>
		</div>
		
		<div class="efile-ui-menu">
			<div id="efile-ui-menu-item-filemanager" class="efile-ui-menu-item" x-ng-class="{'efile-ui-active': appData.mainMenu.filemanager.isActive}" x-ng-init="appData.fn.mainMenuItemInit(appData, 'filemanager');">File Manager</div>
			<div id="efile-ui-menu-item-refresh" class="efile-ui-menu-item" x-ng-click="appData.fn.refreshWorkspace(appData);"><i class="fa fa-fw fa-refresh"></i></div>
		</div>
	</div>
</div>

<div class="efile-ui-data">
	<div class="efile-ui-data-inner">
		<div id="efile-ui-workspace"></div>
	</div>
</div>

<div class="efile-ui-footer">
	<div class="efile-ui-footer-inner">
		<div class="efile-ui-copyright">© 2016 All Rights Reserved.</div>
	</div>
</div>

<div class="efile-ui-modals">
</div>

<div x-growl>
</div>

</body>
</html>