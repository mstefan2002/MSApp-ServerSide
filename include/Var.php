<?php
class CVar
{
	public static string $saltVerifyCode 	= "";                                 //the salt used for email verification hashCode
	public static string $saltDeleteCode 	= "";                                 //the salt used for delete account hashCode

	public static string $SQLHost 		= "";                                 //the hostname of database
	public static string $SQLDB 		= "";                                 //the name of database
	public static string $SQLUser 		= "";                                 //the username of database
	public static string $SQLPassword 	= "";                                 //the password of database

	public static string $SMTPHost 		= "";                                 //the hostname of SMTP(you can find it at cpanel->mail->connect devices)
	public static string $SMTPUser 		= "";                                 //the adress of the mail
	public static string $SMTPPass 		= "";                                 //the password of the mail
	public static int    $SMTPPort 		= 465;                                //The port of SMTP(you can find it at cpanel->mail->connect devices)
	public static string $SMTPName 		= "No Reply";                         //the name of the mail

	public static string $PathToMSApp 	= "";                                 //API Location

	public static string $LogDefault 	= "Logs.env";                         //default log address
	public static string $LogOutput 	= "LogOutput.env";                    //log address for output
	public static string $LogQuery	 	= "LogQuery.env";                     //log address for query
	public static string $LogProcPOST	= "LogProcessingPOST.env";            //log address for ProcessingPOST
	public static string $LogMailer		= "LogMail.env";                      //log address for Mail
}
?>