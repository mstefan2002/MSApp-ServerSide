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
	 * @return string  If the json encode fail, will return an empty json = []
	 * 
	 */
	public static function getJson(array $arr) : string
	{
		if(!$string = json_encode($arr))
			$string = "[]";
		return $string;
	}

	/**
	 * Make a .htaccess 
	 *
	 * @param string $dir
	 * 
	 * @return void
	 * 
	 */
	public static function mkhtacc(string $dir) : void
	{
		$file = $dir."/.htaccess";
		if(!file_exists($file))
		{
			$f = fopen($file, "a+");
			fwrite($f, "Deny from all");
			fclose($f);
		}
	}
	/**
	 * Make a dir if doesnt exist
	 *
	 * @param string $dir
	 * 
	 * @return void
	 * 
	 */
	public static function mkdirIDE(string $dir) : void
	{
		if (!file_exists($dir))
			mkdir($dir);

		self::mkhtacc($dir);
	}
}
?>