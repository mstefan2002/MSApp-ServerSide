<?php 
class ProcessingRequest
{
	private LogF $log;
	private Output $output;
	
	/**
	 * Construct
	 *
	 * @param LogF $log
	 * @param Output $output
	 * 
	 */
	public function __construct(LogF $log, Output $output)
	{
		$this->log = $log;
		$this->output = $output;
	}
	/**
	 * Verify some fields of `$_POST`/`$_GET` if exists and their are not empty
	 *
	 * @param string|array $fields             An array with the fields or a string with a field
	 * @param ?array $dontCheckforEmpty        An array with exceptions for verify emptiness
	 * @param int $type					       0 = $_POST,	1 = $_GET
	 * 
	 * @return array|int                       An array or a value like this (`1`=ok, `0`=is empty, `-1`=doesnt exist, `-2`=wtf exception(we got an empty key))
	 * 
	 */
	public function check(string|array $fields,array $dontCheckforEmpty=null, int $type=0) : array|int
	{
		$arr = [];
		$keys = [];

		if(is_string($fields))
			$keys[] = $fields;
		else
			$keys = &$fields;

		if(count($keys) > 0)
		{
			$response = 1;
			$request = null;
			if($type == 0)
				$request = &$_POST;

			else
				$request = &$_GET;

			foreach($keys as $key)
			{
				if(!empty($key))
				{
					if(isset($request[$key]))
					{
						if(!empty($request[$key]))
							$response = 1;
						else
							$response = 0;
					}
					else 
						$response = -1;
				}
				else
				{
					$this->log->Write("[".__METHOD__."][E] We got an empty key");
					$response = -2;
				}
				if(is_string($fields))
					return $response;

				if($response == 0 && !isset($dontCheckforEmpty[$key])||$response < 0)
					$arr[$key] = $response;
			}
		}
		else
			$this->log->Write("[".__METHOD__."][E] We got an empty array");
		return $arr;
	}

	/**
	 * Will encode some fields of `$_POST`/`$_GET` to htmlspecialchar [More info on docs/Util.txt#protect]
	 *
	 * @param string|array $fields
	 * @param int $type					0 = $_POST,	1 = $_GET
	 * 
	 * @return void
	 * 
	 */
	public function protect(string|array $fields, int $type=0) : void
	{
		$keys =[];
		if(is_string($fields))
			$keys[] = $fields;
		else
			$keys = &$fields;

		if(count($keys) > 0)
		{
			$request = null;
			$errorType = "";
			if($type == 0)
			{
				$request = &$_POST;
				$errorType = "_POST";
			}
			else
			{
				$request = &$_GET;
				$errorType = "_GET";
			}

			foreach($keys as $key)
			{
				$response = $this->check($key,null,$type);
				switch($response)
				{
					case 1:
					{
						$request[$key] = Util::protect($request[$key], ENT_QUOTES);
						break;
					}
					case 0:
					{
						$this->log->Write("[".__METHOD__."][I] {$errorType}[\"{$key}\"] is 0 or an empty string");
						break;
					}
					case -1:
					{
						$this->log->Write("[".__METHOD__."][I] {$errorType}[\"{$key}\"] doesnt exist");
						break;
					}
					case -2:
					{
						$this->log->Write("[".__METHOD__."][E] We got an empty key");
						break;
					}
				}
			}
		}
		else
			$this->log->Write("[".__METHOD__."][E] We got an empty array");
	}

	/**
	 * Extract the fields from `$_POST`\`$_GET`
	 *
	 * @param array $fields
	 * @param int $type					0 = $_POST,	1 = $_GET
	 * 
	 * @return array|false
	 * 
	 */
	public function extractFields(array $fields, int $type=0) : array|false
	{
		if(count($fields) == 0)
		{
			$this->log->Write("[".__METHOD__."][E] We got an empty array");
			$this->output->sendError("Internal problems");
		}
		$arr = [];
		$request = null;
		if($type == 0)
			$request = &$_POST;
		else
			$request = &$_GET;

		foreach($fields as $field)
		{
				$arr[$field] = $request[$field];
		}
		return $arr;
	}
}
?>