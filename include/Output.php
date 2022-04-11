<?php
class Output
{
	private array $arr = [];
	private LogF $log;

	public function __construct(LogF $log)
	{
		$this->log = $log;
	}

	public function add(string $key,$value) : void
	{
		if(!empty($key))
			$this->arr[$key] = $value;
		else
			$this->log->Write("[".__METHOD__."][I] The Key is empty");
	}

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

	public function sendError(string|array $string) : void
	{
		$this->arr = [ "type" => 0, "message" => $string ];
		$this->send();
	}
}
?>