<?php 
class Util
{
	/**
	 * Encode the string with htmlspecialchar
	 *
	 * @param string $string
	 * 
	 * @return string
	 * 
	 */
	public static function protect(string $string) : string
	{
		$aux = htmlspecialchars($string, ENT_QUOTES);
		return $aux;
	}

	/**
	 * Get user ip
	 *
	 * @return string
	 * 
	 */
	public static function getUserIP() : string
	{
		if (isset($_SERVER["HTTP_CF_CONNECTING_IP"]))
		{
			$_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
			$_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
		}
		$client  = @$_SERVER['HTTP_CLIENT_IP'];
		$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
		$remote  = $_SERVER['REMOTE_ADDR'];

		if(filter_var($client, FILTER_VALIDATE_IP))
			$ip = $client;
		elseif(filter_var($forward, FILTER_VALIDATE_IP))
			$ip = $forward;
		else
			$ip = $remote;

		return $ip;
	}

	/**
	 * Get Json from an array
	 *
	 * @param array $arr
	 * 
	 * @return string
	 * If the json encode fail, will return an empty json = []
	 * 
	 */
	public static function getJson(array $arr) : string
	{
		if(!$string = json_encode($arr))
			$string = "[]";
		return $string;
	}

	/**
	 * Check if the password is sha256(64 len and all the chars is like a-fA-F0-9)
	 *
	 * @param string $password
	 * 
	 * @return bool
	 * 
	 */
	public static function validPassword(string $password) : bool
	{
		if(strlen($password) != 64||!ctype_xdigit($password))
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
	public static function validMail(string $email) : bool
	{
		if(strlen($email) > 255||!filter_var($email, FILTER_VALIDATE_EMAIL))
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
	public static function validName(string $name) : bool
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
	public static function validTag(string $tag) : bool
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