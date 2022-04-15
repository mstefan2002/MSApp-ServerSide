<?php
class LogF
{
	private $file=null;
	private string $filename;

	/**
	 * Construct
	 *
	 * @param string $filename       The name of the file, the `default` value you can find at Var.php->$LogDefault
	 * 
	 */
	public function __construct(string $filename="")
	{
		Util::mkdirIDE("Logs");
		if(!is_writable("Logs"))
			error_log("The dir Logs is not writable", 0);
		else
		{
			if(empty($filename))
				$filename = CVar::$LogDefault;

			$file = "./Logs/{$filename}";
			$this->filename = $file;
			if(!$this->file = fopen($file, "a+"))
				error_log("Cannot open file ({$file})", 0);
		}
	}

	/**
	 * Writing to the file. The format is like: [`date`+`time`][`type request`] - `text`
	 *
	 * @param string $string    The text
	 * @return void
	 * 
	 */
	public function Write(string $string) : void
	{
		date_default_timezone_set('Europe/Bucharest');
		$time = date('Y-m-d H:i:s');
		$type = "null";
		if(isset($_POST['type']))
			$type = $_POST['type'];
		elseif(isset($_GET['type']))
			$type = $_GET['type'];

		$txt = "[{$time}][{$type}] - {$string}\n";
		if(fwrite($this->file, $txt) === FALSE)
			error_log("Cannot write to file ({$this->filename})", 0);
	}
	/**
	 * Destruct
	 */
	private function __destruct()
	{
		if(!is_null($this->file))
			fclose($this->file);
	}
}
?>