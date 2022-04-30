<?php 
class Tables
{
	/**
	 * Accounts table
	 *
	 * @param bool $value
	 * 
	 * @return string|Accounts_Rows
	 * 
	 */
	public static function Accounts(bool $value) : string|Accounts_Rows
	{
		if($value)
			return new Accounts_Rows();
		return "accounts";
	}
	/**
	 * EmailVerification table
	 *
	 * @param bool $value
	 * 
	 * @return string|EmailVerify_Rows
	 * 
	 */
	public static function EmailVerification(bool $value) : string|EmailVerify_Rows
	{
		if($value)
			return new EmailVerify_Rows();
		return "email_verification";
	}

	/**
	 * Sessions table
	 *
	 * @param bool $value
	 * 
	 * @return string|Sessions_Rows
	 * 
	 */
	public static function Sessions(bool $value) : string|Sessions_Rows
	{
		if($value)
			return new Sessions_Rows();
		return "sessions";
	}

	public static function Logs(bool $value) : string|Logs_Rows
	{
		if($value)
			return new Logs_Rows();
			
		return Config::$LogDefault;
	}
}
class Accounts_Rows
{
	/**
	 * Primary Key     |
	 * Auto Increment  |
	 * Not NULL        |
	 * Type: INT(MaxLen: 11)
	 */
	public string $id           =       "id";


	/**
	 * Unique Key      |
	 * Not NULL        |
	 * Type: VARCHAR(MaxLen: 255)
	 */
	public string $email        =       "email";


	/**
	 * Not NULL        |
	 * Type: VARCHAR(MaxLen: 64)
	 */
	public string $name         =       "fullname";


	/**
	 * NULL            |
	 * Type: VARCHAR(MaxLen: 16)
	 */
	public string $tag          =       "tag";


	/**
	 * Encode Type: SHA256   |
	 * Not NULL              | 
	 * Type: VARCHAR(MaxLen: 64)
	 */
	public string $password     =       "password";


	/**
	 * NULL        |
	 * Type: INT(MaxLen: 1)
	 */
	public string $emailVerify  =       "email_verify";
}
class EmailVerify_Rows
{
	/**
	 * Primary Key        |
	 * Auto Increment     |
	 * Not NULL           |
	 * Type: INT(MaxLen: 11)
	 */
	public string $id           =       "id";


	/**
	 * Unique Key        |
	 * Not NULL          |
	 * Type: VARCHAR(MaxLen: 255)
	 */
	public string $email        =       "email";


	/**
	 * Encode Type: SHA256    |
	 * Not NULL               |
	 * Type: VARCHAR(MaxLen: 64)
	 */
	public string $verifyCode 	=		"verifyCode";


	/**
	 * Encode Type: SHA256    |
	 * Not NULL               |
	 * Type: VARCHAR(MaxLen: 64)
	 */
	public string $deleteCode	=		"deleteCode";


	/**
	 * Default: CURRENT_TIMESTAMP        |
	 * Not NULL                          |
	 * Type: TIMESTAMP
	 */
	public string $created		=		"created";

	/**
	 * Default: CURRENT_TIMESTAMP        |
	 * Not NULL                          |
	 * Type: TIMESTAMP
	 */
	public string $lastMailSend	=		"lastMailSend";
}
class Sessions_Rows
{
	/**
	 * Primary Key        |
	 * Auto Increment     |
	 * Not NULL           |
	 * Type: INT(MaxLen: 11)
	 */
	public string $id           =       "id";


	/**
	 * Unique Key        |
	 * Not NULL          |
	 * Type: VARCHAR(MaxLen: 255)
	 */
	public string $email        =       "email";


	/**
	 * Encode Type: SHA256    |
	 * Not NULL               |
	 * Type: VARCHAR(MaxLen: 64)
	 */
	public string $hash 	    =		"hash";

	/**
	 * Default: CURRENT_TIMESTAMP        |
	 * Not NULL                          |
	 * Type: TIMESTAMP
	 */
	public string $lastUsed		=		"lastUsed";
}

class Logs_Rows
{
	/**
	 * Primary Key        |
	 * Auto Increment     |
	 * Not NULL           |
	 * Type: INT(MaxLen: 11)
	 */
	public string $id           =       "id";


	/**
	 * Not NULL           |
	 * Type: VARCHAR(MaxLen: 16)
	 */
	public string $type         =       "type";

	/**
	 * NULL               |
	 * Type: CHAR(MaxLen: 1)
	 */
	public string $typeLog      =       "typeLog";

	/**
	 * NULL               |
	 * Type: VARCHAR(MaxLen: 64)
	 */
	public string $method       =       "method";


	/**
	 * Not NULL           |
	 * Type: VARCHAR(MaxLen: 255)
	 */
	public string $message      =       "message";

	
	/**
	 * Default: CURRENT_TIMESTAMP        |
	 * Not NULL                          |
	 * Type: TIMESTAMP
	 */
	public string $created		=		"created";
}
?>