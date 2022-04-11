<?php 
class ProcessingPOST
{
	private LogF $log;

	/**
	 * Construct
	 *
	 * @param LogF $log
	 * 
	 */
	public function __construct(LogF $log)
	{
		$this->log = $log;
	}
	/**
	 * Verify a field of `$_POST` if exist and is not empty
	 *
	 * @param string $key
	 * 
	 * @return int
	 * `1`=ok, `0`=is empty, `-1`=doesnt exist, `-2`=wtf exception(we got an empty key)
	 * 
	 */
	public function checkPOST(string $key) : int
	{
		if(!empty($key))
		{
			if(isset($_POST[$key]))
			{
				if(!empty($_POST[$key]))
					return 1;
				return 0;
			}
			return -1;
		}
		else
			$this->log->Write("[".__METHOD__."][E] We got an empty key");
		return -2;
	}
	/**
	 * Verify some fields of `$_POST` if exists and their are not empty
	 *
	 * @param array $keys
	 * An array with the fields
	 * @param array $dontCheckforEmpty=null
	 * An array with exceptions for verify emptiness
	 * 
	 * @return array
	 * An array with values like this (`1`=ok, `0`=is empty, `-1`=doesnt exist, `-2`=wtf exception(we got an empty key))
	 * 
	 */
	public function checkPOSTS(array $keys,array $dontCheckforEmpty=null) : array
	{
		$arr = [];
		if(count($keys) > 0)
		{
			$i = 0;
			$response = 1;
			foreach($keys as $key)
			{
				$response = $this->checkPOST($key);
				if($response == 0 && !isset($dontCheckforEmpty[$key])||$response < 0)
					$arr[$key] = $response;
			}
		}
		else
			$this->log->Write("[".__METHOD__."][E] We got an empty array");
		return $arr;
	}

	/**
	 * Will encode a field of `$_POST` to htmlspecialchar [More info on docs/Util.txt#protect]
	 *
	 * @param string $key
	 * 
	 * @return void
	 * 
	 */
	public function protectPOST(string $key) : void
	{
		if(!empty($key))
		{
			if(isset($_POST[$key]))
			{
				if(!empty($_POST[$key]))
					$_POST[$key] = Util::protect($_POST[$key], ENT_QUOTES);
				else
					$this->log->Write("[".__METHOD__."][I] _POST[\"{$key}\"] is 0 or an empty string");
			}
			else
				$this->log->Write("[".__METHOD__."][I] _POST[\"{$key}\"] doesnt exist");
		}
		else
			$this->log->Write("[".__METHOD__."][E] We got an empty key");
	}
	/**
	 * Will encode some fields of `$_POST` to htmlspecialchar [More info on docs/Util.txt#protect]
	 *
	 * @param array $keys
	 * An array with the fields
	 * 
	 * @return void
	 * 
	 */
	public function protectPOSTS(array $keys) : void
	{
		if(count($keys) > 0)
			foreach($keys as $key)
				$this->protectPOST($key);
		else
			$this->log->Write("[".__METHOD__."][E] We got an empty array");
	}
}
?>