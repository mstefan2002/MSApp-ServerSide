<?php
class Output
{
	private array $arr = [];
	private Log $log;
	private ?Temper $temp;

	/**
	 * Construct
	 *
	 * @param Log $log
	 * @param Temper|null $temp
	 * 
	 */
	public function __construct(Log $log, ?Temper $temp)
	{
		$this->log = $log;
		$this->temp = $temp;
	}

	/**
	 * Change log type
	 *
	 * @param Log $log
	 * 
	 * @return void
	 * 
	 */
	public function changeLog(Log $log) : void
	{
		$this->log = $log;
	}
	/**
	 * Add array to output
	 *
	 * @param string $key
	 * @param mixed $value
	 * 
	 * @return void
	 * 
	 */
	public function add(string $key,$value) : void
	{
		if(!empty($key))
			$this->arr[$key] = $value;
		else
			$this->log->Write("[".__METHOD__."][I] The Key is empty");
	}

	/**
	 * Send the output and stop the script
	 *
	 * @return void
	 * 
	 */
	public function send() : void
	{
		$time_end = microtime(true);
		$execution_time = ($time_end - $GLOBALS['time_start']);
		$this->add("time",$execution_time);
		if(count($this->arr) == 0)
			$this->log->Write("[".__METHOD__."][E] The Output(array) is empty");
		else
			echo Util::getJson($this->arr);

		$this->callBackground();
		exit();
	}

	/**
	 * Send an error output with a message
	 *
	 * @param string|array $string
	 * 
	 * @return void
	 * 
	 */
	public function sendError(string|array $string) : void
	{
		$this->arr = [ "type" => 0, "message" => $string ];
		$this->send();
	}

	/**
	 * Send 403 Not Allowed
	 *
	 * @return void
	 * 
	 */
	public function sendHtmlError() : void
	{
		header("HTTP/1.0 403 Not Allowed");
		$this->callBackground();
		exit();
	}

	//TO DO
	/**
	 * Send html response for verify page
	 *
	 * @param string $html
	 * 
	 * @return void
	 * 
	 */
	public function sendHtmlResponse(string $html) : void
	{
		echo $html;
		$this->callBackground();
		exit();
	}

	/**
	 * Call the background task(we use this until we buy a server)
	 *
	 * @return void
	 * 
	 */
	private function callBackground() : void
	{
		if(!is_null($this->temp) && $this->temp->checkTimer("nextBackgroundCall"))
		{
			$arr = parse_url(Config::$PathToMSApp);
			$fp = fsockopen($arr["host"], 80, $errno, $errstr, 1);
			if (!$fp)
			{
				$this->log->Write("We got en error when we tried to connect to localhost/bg : {$errstr} ({$errno})");
			}
			else
			{
				$out = "GET {$arr['path']}background.php HTTP/1.1\r\n";
				$out .= "Host: {$arr['host']}\r\n";
				$out .= "Connection: Close\r\n\r\n";
				fwrite($fp, $out);
				fclose($fp);
			}
		}
	}
}
?>