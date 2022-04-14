<?php
class EmailVerify
{
	/**
	 * Create 2 hashes(one for verification, one for delete) for email validation then insert them into the database
	 *
	 * @param Database $db
	 * @param string $email
	 * 
	 * @return array            `0`=verifyHash , `1`=deleteHash
	 */
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
		$urls[] = $path."verify.php?email={$email}&verifyCode={$hashCodes[0]}";
		$urls[] = $path."verify.php?email={$email}&deleteCode={$hashCodes[1]}";

		return $urls;
	}

	/**
	 * Verify if the hash exist in email verification
	 *
	 * @param string $email
	 * @param int $type            `0`=verify,      `1`=delete
	 * 
	 * @return int                 `2`  =wtf exception(many rows with the email), `1`=hash match, `0`=hash doesnt match, 
	 *                             `-1` =email doesnt have any verification
	 * 
	 */
	public static function verify(Database $db, string $email, string $hash, int $type=0) : int
	{
		$table	    = Tables::EmailVerification(true);
		$fieldEmail = $table->email;
		$fieldVerifyCode = $table->verifyCode;
		$fieldDeleteCode = $table->deleteCode;

		$result = $db->select(
			"`{$fieldVerifyCode}`,`$fieldDeleteCode`",                    // select
			Tables::EmailVerification(false),                             // location
			"`{$fieldEmail}`=?",                                          // condition
			null,                                                         // others
			array($email)                                                 // parms
		);

		if(isset($result->num_rows) && $result->num_rows > 0)
		{
			if($result->num_rows > 1)
			{
				return 2;
			}

			$row = $result->fetch_array(MYSQLI_ASSOC);
			if($type == 0 && hash_equals($row[$fieldVerifyCode],$hash)||$type == 1 && hash_equals($row[$fieldDeleteCode],$hash))
			{
				return 1;
			}

			return 0;
		}
		return -1;
	}

	/**
	 * Remove the hashes from the email verification db
	 *
	 * @param Database $db
	 * @param string $email
	 * 
	 * @return void
	 * 
	 */
	public static function remove(Database $db, string $email) : void
	{
		$fieldEmail = Tables::EmailVerification(true)->email;

		$db->delete(
			Tables::EmailVerification(false),
			"`{$fieldEmail}`=?",
			array($email)
		);
	}
}