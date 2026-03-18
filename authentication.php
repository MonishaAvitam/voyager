<?php

ob_start();
session_start();

require 'classes/admin_class.php';
$obj_admin = new Admin_Class();

if (isset($_GET['logout'])) {
	$obj_admin->admin_logout();
}

if (!isset($_SESSION['OTP'])) {

    header("Location: index.php");
	// header("Location: https://csaappstore.com?logout");
	exit();
};

?>
