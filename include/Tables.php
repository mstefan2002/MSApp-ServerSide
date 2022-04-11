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
	public string $id		=		"id";
	public string $name		=		"fullname";
	public string $tag		=		"tag";
	public string $email		=		"email";
	public string $password 	=		"password";
	public string $emailVerify	=		"email_verify";
}
class EmailVerify_Rows
{
	public string $id		=		"id";
	public string $email		=		"email";
	public string $verifyCode 	=		"verifyCode";
	public string $deleteCode	=		"deleteCode";
	public string $created		=		"created";
}

?>