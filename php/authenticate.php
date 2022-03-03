<?php 
// AngularJS & PHP File Manager
// @author 
// @site 
// @copyright (c) 2022
?>
<?php
session_start();
if(empty($_SESSION["authenticated"]) || $_SESSION["authenticated"] != 'true') {
	header('Location: login.php');
}
?>