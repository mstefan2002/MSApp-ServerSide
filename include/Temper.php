<?php 
class Temper
{
    private array $arr;
	private string $filename;

	/**
	 * Construct
	 *
	 * @param string $filename       The name of the temper, the `default` value you can find at Var.php->$TempDefault
	 * 
	 */
	public function __construct(string $filename="")
	{
		Util::mkdirIDE("Temp");
		if(!is_writable("Temp"))
			error_log("The dir Temp is not writable", 0);
		else
		{
			if(empty($filename))
				$filename = CVar::$TempDefault;

			$file = "./Temp/{$filename}";
			$this->filename = $file;

            if(!file_exists($file))
            {
                if(!fopen($file, "a+"))
                {
                    error_log("Cannot open file ({$file})", 0);
                    exit();
                }
            }
            else
            {
                $content = file_get_contents($file);
                if(!empty($content))
                    $this->arr = json_decode(file_get_contents($file),true);
            }
		}
	}

    /**
     * Check if the time of the key expire
     *
     * @param string $key
     * 
     * @return bool
     * 
     */
    public function checkTimer(string $key) : bool
    {
        if(isset($this->arr[$key]))
        {
            if($this->arr[$key] > time())
                return false;
        }
        return true;
    }

    /**
     * Set timer
     *
     * @param string $key
     * @param int $time    The unit of measurement is second
     * 
     * @return void
     * 
     */
    public function setTimer(string $key, int $time) : void
    {
        $TotalSeconds = time()+$time;
        $this->arr[$key] = $TotalSeconds;
        if(!isset($this->arr["nextBackgroundCall"]) || $this->arr["nextBackgroundCall"] > $TotalSeconds) // check if the next background call is higher then the TotalSeconds
            $this->arr["nextBackgroundCall"] = $TotalSeconds;

        file_put_contents($this->filename,json_encode($this->arr));
    }
}
?>