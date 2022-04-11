<?php 
class Lang
{
	public static function getMailMessage(string $name, string $verifyUrl, string $deleteUrl) : array
	{
		$arr = [];
		$arr[] = "<font size='6'>Hi {$name},<br><br>We're happy you signed up for MySocialsApp. ".
			 "Before being able to use your account you need to verify that this is your email address by clicking here:</font><br><br><br><br>".
			 "<a href='{$verifyUrl}'><button style='cursor: pointer;border-radius:25px;background-color: #1DA1F2;border:none;padding:15px;width:190px;text-align:center;font-size:25px;'>".
			 "<b>Verify Now</b></button></a><br><br><br><font size='6'>Welcome to MySocialsApp!<br>The MSApp Team</font><br><br><br><br><font size='4'>".
			 "Did you receive this email without signing up? <a href='{$deleteUrl}'><u>Click here</u></a>. This verification link will expire in 24 hours.</font>";

		$arr[] = "Hi {$name},\n\nWe're happy you signed up for MySocialsApp. ".
			 "Before being able to use your account you need to verify that this is your email address by clicking here: {$verifyUrl}".
			 "\n\n\nWelcome to MySocialsApp!\nThe MSApp Team\n\n\n\n".
			 "Did you receive this email without signing up? Click here: {$deleteUrl} . This verification link will expire in 24 hours.";
		return $arr;
	}
}