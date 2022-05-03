<?php 
class Validator
{
    /**
	 * Check if the string is a hash like sha256(64 len and all the chars is like a-fA-F0-9)
	 *
	 * @param string $string
	 * 
	 * @return bool
	 * 
	 */
	public static function hash256(string $string) : bool
	{
		if(strlen($string) != 64||!ctype_xdigit($string))
			return false;

		return true;
	}

	/**
	 * Check if the mail is valid(under 256 len and pass the php filter validate email)
	 *
	 * @param string $email
	 * 
	 * @return bool
	 * 
	 */
	public static function mail(string $email) : bool
	{
		if(strlen($email) > 255||!filter_var($email, FILTER_VALIDATE_EMAIL))
			return false;

		return true;
	}

	/**
	 * Check if the len of password is higher then 5 
	 *
	 * @param string $email
	 * 
	 * @return bool
	 * 
	 */
	public static function password(string $password) : bool
	{
		if(strlen($password) < 6)
			return false;

		return true;
	}
	/**
	 * Check if the name is valid(len is `higher then 2` and `lower then 65` and all the chars is like `A-Za-z` or `space`[not all])
	 *
	 * @param string $name
	 * 
	 * @return bool
	 * 
	 */
	public static function name(string $name) : bool
	{
		$len = strlen($name);
		if($len < 3 || $len > 64)
			return false;

		$backupName = $name;
		$backupName = str_replace(" ","",$backupName);
		if($backupName < 2)
			return false;

		for($i=0;$i<$len;++$i)
		{ 
			$aux = $name[$i];
			if($aux == ' ' || 'A' <= $aux &&  $aux <= 'Z' || 'a' <= $aux &&  $aux <= 'z')
				continue;
			return false;
		}
		return true;
	}

	/**
	 * Check if the tag is valid(len is `higher then 2` and `lower then 17` and all the chars is like A-Za-z[minimum 1 char] or _-.)
	 *
	 * @param string $tag
	 * 
	 * @return bool
	 * 
	 */
	public static function tag(string $tag) : bool
	{
		$len = strlen($tag);
		if($len < 3 || $len > 16)
			return false;

		$allowChar="_-.";
		$backupTag = $tag;
		$backupTag = str_replace(str_split($allowChar),"",$backupTag);
		if($backupTag < 1)
			return false;


		for($i=0;$i<$len;++$i)
		{ 
			$aux = $tag[$i];
			if('A' <= $aux &&  $aux <= 'Z' || 'a' <= $aux &&  $aux <= 'z' || str_contains($allowChar,$aux))
				continue;
			return false;
		}
		return true;
	}
}
?>