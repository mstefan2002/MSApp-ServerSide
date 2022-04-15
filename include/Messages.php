<?php 
class Messages
{
	/**
	 * Create 2 messages for email verification, one for html type and one for text type
	 *
	 * @param string $name           Person name
	 * @param string $verifyUrl      Link for email verification
	 * @param string $deleteUrl      Link for deleting account
	 * 
	 * @return array                 `0`= Html, `1` = Text
	 */
	public static function getMailMessage(string $name, string $verifyUrl, string $deleteUrl) : array
	{
		$arr = [];
		$arr[] = "<font size='6'>Hi {$name},<br><br>We're happy you signed up for MySocialsApp. ".
			 "Before being able to use your account you need to verify that this is your email address by clicking here:</font><br><br><br><br>".
			 "<a href='{$verifyUrl}'><button style='cursor: pointer;border-radius:25px;background-color: #1DA1F2;border:none;padding:15px;width:190px;text-align:center;font-size:25px;'>".
			 "<b>Verify Now</b></button></a><br><br><br><font size='6'>Welcome to MySocialsApp!<br>The MSApp Team</font><br><br><br><br><font size='4'>".
			 "Did you receive this email without signing up? <a href='{$deleteUrl}'><u>Click here</u></a>.<br> This verification link will expire in 24 hours and your account will be deleted.</font>";

		$arr[] = "Hi {$name},\n\nWe're happy you signed up for MySocialsApp. ".
			 "Before being able to use your account you need to verify that this is your email address by clicking here: {$verifyUrl}".
			 "\n\n\nWelcome to MySocialsApp!\nThe MSApp Team\n\n\n\n".
			 "Did you receive this email without signing up? Click here: {$deleteUrl} . \nThis verification link will expire in 24 hours and your account will be deleted.";
		return $arr;
	}

	
	/**
	 * Create a message for invalid parms at verify Page
	 *
	 * @return string         html type
	 * 
	 */
	public static function getVerifyInvalidMessage() : string
	{
		$str = "<html><body><b>Uh oh. We've run into a problem. Please try again later</b>";
		return $str;
	}

	/**
	 * Create a message for invalid email
	 *
	 * @return string         html type
	 * 
	 */
	public static function getInvalidMailMessage() : string
	{
		$str = "<html><body><b><b>Email is not valid</b>";
		return $str;
	}


	/**
	 * Create a message for invalid hash
	 *
	 * @return string         html type
	 * 
	 */
	public static function getInvalidHashMessage() : string
	{
		$str = "<html><body><b>The link is not valid</b>";
		return $str;
	}


	/**
	 * Create a message for successful account validation
	 *
	 * @return string         html type
	 * 
	 */
	public static function getSuccessValidationMessage() : string
	{
		$str = "<html><body><b>You verified the account!</b>";
		return $str;
	}


	/**
	 * Create a message for successful account deletion
	 *
	 * @return string         html type
	 * 
	 */
	public static function getSuccessDeleteMessage() : string
	{
		$str = "<html><body><b>We deleted the account!</b>";
		return $str;
	}
}