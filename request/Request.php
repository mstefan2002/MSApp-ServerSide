<?php 
require_once("request/Request_Login.php");
require_once("request/Request_Register.php");
require_once("request/Request_Resend_Mail.php");
require_once("request/Request_UpdateTag.php");
interface Request_Type
{
    public static function call(Database $db, ProcessingRequest $pRequest, Output $output, Log $log);
}
class Request
{
	public static function login(Database $db, ProcessingRequest $pRequest, Output $output, Log $log)
	{
		Login::call($db,$pRequest,$output,$log);
	}
	public static function register(Database $db, ProcessingRequest $pRequest, Output $output, Log $log)
	{
		Register::call($db,$pRequest,$output,$log);
	}
	public static function resend_mail(Database $db, ProcessingRequest $pRequest, Output $output, Log $log)
	{
		Resend_Mail::call($db,$pRequest,$output,$log);
	}
	public static function updateTag(Database $db, ProcessingRequest $pRequest, Output $output, Log $log)
	{
		UpdateTag::call($db,$pRequest,$output,$log);
	}
}