<?php
class Config
{
	public static string $saltVerifyCode    = "";                                 //the salt used for email verification hashCode
	public static string $saltDeleteCode    = "";                                 //the salt used for delete account hashCode
	public static string $saltSessionCode   = "";                                 //the salt used for session hashCode
	
	public static string $tokenApi          = "";                                 //accept request only if the token is gived

	public static string $IP_BgAPI          = "";                                 //accept only the ip of the webhost

	public static string $SQLHost           = "";                                 //the hostname of database
	public static string $SQLDB             = "";                                 //the name of database
	public static string $SQLUser           = "";                                 //the username of database
	public static string $SQLPassword       = "";                                 //the password of database

	public static string $SMTPHost          = "";                                 //the hostname of SMTP(you can find it at cpanel->mail->connect devices)
	public static string $SMTPUser          = "";                                 //the adress of the mail
	public static string $SMTPPass          = "";                                 //the password of the mail
	public static int    $SMTPPort          = 465;                                //The port of SMTP(you can find it at cpanel->mail->connect devices)
	public static string $SMTPName          = "No Reply";                         //the name of the mail

	public static string $PathToMSApp       = "https://localhost/";               //API Location

	public static string $LogDefault        = "Logs";                             //default log address
	public static string $LogOutput         = "LogOutput";                        //log address for output
	public static string $LogQuery          = "LogQuery";                         //log address for query
	public static string $LogProcReq        = "LogProcessingPOST";                //log address for ProcessingPOST
	public static string $LogMailer         = "LogMail";                          //log address for Mail
	public static string $LogVerify         = "LogVerify";                        //log address for Verify page
	public static string $LogBgProc         = "LogBackground";                    //log address for BG page

	public static string $TempDefault       = "ServerTemp.json";                  //temp address

	public static int    $SessionLife       = 30;                                 //Interval of days for the life of the session

	public static int    $MailResend        = 30;                                 //Seconds to wait for reseding email

	public static int    $TimerEmailVerify  = 60;                                 //Interval of seconds to check expired email verification
	public static int    $TimerSession      = 60;                                 //Interval of seconds to check expired sessions

}
?>
