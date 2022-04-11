<?php
class EmailVerify
{
	public static function add(Database $db,string $email) : array
	{
		$saltVerify = CVar::$saltVerifyCode;
		$saltDelete = CVar::$saltDeleteCode;
		$path	    = CVar::$PathToMSApp;
		$table	    = Tables::EmailVerification(true);
		$hashCodes  = [];

		$number = (string)random_int(1000, 9999);
		$hashCodes[] = hash("sha256","{$number}{$saltVerify}",false);

		$number = (string)random_int(1000, 9999);
		$hashCodes[] = hash("sha256","{$number}{$saltDelete}",false);


		$fieldEmail = $table->email;
		$fieldVerifyCode = $table->verifyCode;
		$fieldDeleteCode = $table->deleteCode;

		$db->insert(
					Tables::EmailVerification(false),
					array($fieldEmail,$fieldVerifyCode=>$hashCodes[0],$fieldDeleteCode=>$hashCodes[1]),
					array($email)
				);

		$urls = [];
		$urls[] = $path."verify.php?email={$email}&deleteCode={$hashCodes[0]}";
		$urls[] = $path."verify.php?email={$email}&verifyCode={$hashCodes[1]}";

		return $urls;
	}
}