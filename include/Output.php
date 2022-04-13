<?php
class Output
{
	private array $arr = [];
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
		exit();
	}
}
?>