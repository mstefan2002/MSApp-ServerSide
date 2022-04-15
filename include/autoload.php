<?php 
	ini_set("display_errors", "0");
	
	// Testing Time
	$GLOBALS['time_start'] = microtime(true);

	require_once("include/PHPMailer/Exception.php");
	require_once("include/PHPMailer/PHPMailer.php");
	require_once("include/PHPMailer/SMTP.php");
	require_once("include/Var.php");
	require_once("include/Util.php");
	require_once("include/Mailer.php");
	require_once("include/Tables.php");
	require_once("include/User.php");
	require_once("include/Log.php");
	require_once("include/ProcessingRequest.php");
	require_once("include/Output.php");
	require_once("include/Database.php");
	require_once("include/Messages.php");
	require_once("include/EmailVerify.php");
	require_once("include/Deleter.php");
	require_once("include/Temper.php");
	require_once("include/Validator.php");
?>
