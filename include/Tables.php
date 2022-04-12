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

}
class Accounts_Rows
{
	/**
	 * Primary Key     |
	 * Auto Increment  |
	 * Not NULL        |
	 * Type: INT(MaxLen: 11)
	 *
	 * @var string
	 */
	public string $id           =       "id";


	/**
	 * Unique Key      |
	 * Not NULL        |
	 * Type: VARCHAR(MaxLen: 255)
	 *
	 * @var string
	 */
	public string $email        =       "email";


	/**
	 * Not NULL        |
	 * Type: VARCHAR(MaxLen: 64)
	 *
	 * @var string
	 */
	public string $name         =       "fullname";


	/**
	 * Not NULL        |
	 * Type: VARCHAR(MaxLen: 16)
	 *
	 * @var string
	 */
	public string $tag          =       "tag";


	/**
	 * Encode Type: SHA256   |
	 * Not NULL              | 
	 * Type: VARCHAR(MaxLen: 64)
	 *
	 * @var string
	 */
	public string $password     =       "password";


	/**
	 * NULL        |
	 * Type: INT(MaxLen: 1)
	 *
	 * @var string
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
	 *
	 * @var string
	 */
	public string $id           =       "id";


	/**
	 * Unique Key        |
	 * Not NULL          |
	 * Type: VARCHAR(MaxLen: 255)
	 *
	 * @var string
	 */
	public string $email        =       "email";


	/**
	 * Encode Type: SHA256    |
	 * Not NULL               |
	 * Type: VARCHAR(MaxLen: 64)
	 *
	 * @var string
	 */
	public string $verifyCode 	=		"verifyCode";


	/**
	 * Encode Type: SHA256    |
	 * Not NULL               |
	 * Type: VARCHAR(MaxLen: 64)
	 *
	 * @var string
	 */
	public string $deleteCode	=		"deleteCode";


	/**
	 * Default: CURRENT_TIMESTAMP        |
	 * Not NULL                          |
	 * Type: TIMESTAMP
	 *
	 * @var string
	 */
	public string $created		=		"created";
}

?>