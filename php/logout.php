<?php 
// AngularJS & PHP File Manager
// @author 
// @site 
// @copyright (c) 2022
?>
<?php
require_once('authenticate.php');
?>

<?php
	session_destroy();
	header('Location: index.php');
?>