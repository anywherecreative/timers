<?php
session_name("pdtime");
session_set_cookie_params(0, '/', SITE_DOMAIN);
session_start();
session_start();
if(isset($_SESSION['user'])):
	//load user defined constants

endif;
require('config.php');
require('constant.php');
if(SHOW_SYSTEM_ERRORS) {
	error_reporting(E_ALL | E_STRICT);
	ini_set("display_errors", 1);
}
require('system.class.php');
require('quickCache.class.php');
require('controller.class.php');
date_default_timezone_set(TIME_ZONE);
$mysql = new mysqli(DB_HOST,DB_USER,DB_PASS, DB_DATABASE);
if ($mysql->connect_errno) {
	die ("Failed to connect to MySQL: (" . $mysql->connect_errno . ") " . $mysql->connect_error);
}

$controller = new Controller();
?>
