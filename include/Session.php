<?php
class Session
{
	/**
	 * Verify if there is a session for that email
	 * 
	 * @param Database $db
	 * @param string $email
	 * @param string $hash
	 * 
	 * @return int                 `2`  =wtf exception(many rows with the email), `1`=hash match, `0`=hash doesnt match, 
	 *                             `-1` =email doesnt have any session
	 * 
	 */
	public static function verify(Database $db, string $email, string $hash) : int
	{
		$table	    = Tables::Sessions(true);
		$fieldEmail = $table->email;
		$fieldHash = $table->hash;

		$result = $db->select(
			"`{$fieldHash}`",                                             // select
			Tables::Sessions(false),                                      // location
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
			if(hash_equals($row[$fieldHash],$hash))
			{
				return 1;
			}

			return 0;
		}
		return -1;
	}

	/**
	 * Delete the session
	 *
	 * @return void
	 * 
	 */
	public static function remove(Database $db,string $email) : void
	{
		$table = Tables::Sessions(true);
		$fieldEmail = $table->email;

		$db->delete(
							Tables::Sessions(false),
							"`{$fieldEmail}`=?",
							array($email)
		);
	}


	/**
	 * Register new account
	 *
	 * @param string $password
	 * @param string $name
	 * 
	 */
	public static function createSession(Database $db,string $email) : string
	{
		$db->start_transaction();
		self::remove($db,$email);

		$saltSession = Config::$saltSessionCode;
		$table       = Tables::Sessions(true);
		$fieldEmail  = $table->email;
		$fieldHash   = $table->hash;

		$number = (string)random_int(1000, 9999);
		$hash = hash("sha256","{$number}{$saltSession}{$email}{$number}",false);

		$db->insert(
					Tables::Sessions(false),
					array($fieldEmail,$fieldHash),
					array($email,$hash)
				);
		$db->end_transaction();

		return $hash;
	}


	/**
	 * Check if the session expired
	 *
	 * @param Database $db
	 * @param ?string $email   If is null, we will check all the emails
	 * 
	 * @return array           An array with email value
	 * 
	 */
	public static function checkExpired(Database $db, ?string $email) : array
	{
		$arr = [];

		$days          = Config::$SessionLife;
		$table	       = Tables::Sessions(true);
		$fieldEmail    = $table->email;
		$fieldLastUsed = $table->lastUsed;

		$result = null;
		if(is_null($email))
		{
			$result = $db->select(
				"`{$fieldEmail}`",
				Tables::Sessions(false),
				"DATE_ADD(`{$fieldLastUsed}`, INTERVAL {$days} DAY) < CURRENT_TIMESTAMP",
				null,
				null
			);
		}
		else
		{
			$result = $db->select(
				"`{$fieldEmail}`",
				Tables::Sessions(false),
				"`{$fieldEmail}`=? AND DATE_ADD(`{$fieldLastUsed}`, INTERVAL {$days} DAY) < CURRENT_TIMESTAMP",
				null,
				array($email)
			);
		}

		if(isset($result->num_rows) && $result->num_rows > 0)
		{
			while($row = $result->fetch_array(MYSQLI_ASSOC))
			{
				$arr[] = $row[$fieldEmail];
			}
		}
		return $arr;
	}
}
?>