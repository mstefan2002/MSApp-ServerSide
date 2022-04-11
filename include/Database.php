<?php 
class Database
{
	private mysqli $mysqli;
	private LogF $Log;
	private LogF $LogQuery;
	private Output $OutPut;
	private ?mysqli_stmt $stmt = null;

	public function __construct(LogF $log, LogF $logQuery, Output $output)
	{
		$this->Log = $log;
		$this->LogQuery = $logQuery;
		$this->OutPut = $output;

		$host = CVar::$SQLHost;
		$user = CVar::$SQLUser;
		$pass = CVar::$SQLPassword;
		$db = CVar::$SQLDB;

		$this->mysqli = new mysqli($host, $user, $pass, $db);

		if($this->mysqli->connect_error)
			$this->setFail("[".__METHOD__."] We cant connect to the database, the error: \"{$this->mysqli->connect_error}\"");	
	}

	public function update(string $location, array $arr, string $condition=null, array $parms=null) : mysqli_result|false
	{
		$this->checkString(__METHOD__,	array(0=>"location",1=>$location),	1); 	// [ERROR] Location cant be empty
		$this->checkArray (__METHOD__,	array(0=>"arr",1=>$arr),		1); 	// [ERROR] Array of keys=>values cant be empty

		$query = "UPDATE `{$location}` SET ";
		$wasprev = false;
		if(is_null($parms))
		{
			foreach($arr as $keyF=>$valueF)
			{
				if(gettype($keyF)[0] == 'i')
				{
					$this->setFail("[".__METHOD__."][E] keyF is an integer, so the value is not initialized for the key {$valueF}");
					continue;
				}
				else
				{
					if($valueF=='?')
						$this->setFail("[".__METHOD__."][E] \"{$keyF}\" is '?' but is not using the SQLSafe method");
					else
					{
						if($valueF == '')
							$this->Log->Write("[".__METHOD__."][I] \"{$keyF}\" is empty");
						if($wasprev == true)
							$query.=", ";

						$query.= "`{$keyF}`=";
						$query.= "'{$valueF}'";
						$wasprev = true;
					}
				}
			}
		}
		else
		{
			foreach($arr as $keyF=>$valueF)
			{
				if($wasprev == true)
					$query.=", ";

				if(gettype($keyF)[0] == 'i')
				{
					$query.= "`{$valueF}`=?";
				}
				else
				{
					$query.= "`{$keyF}`=";
					if($valueF=='?'||$valueF=='')
						$query.="?";
					else
						$query.= "'{$valueF}'";
				}
				$wasprev = true;
			}
		}
		if(!is_null($condition))
			$query.=" WHERE {$condition}";

		if(is_null($parms))
			return $this->query($query);
		return $this->cQuery($query,$parms);
	}

	public function insert(string $location, array $arr, array $parms=null) : mysqli_result|false
	{
		$this->checkString(__METHOD__,	array(0=>"location",1=>$location),	1); 	// [ERROR] Location cant be empty
		$this->checkArray (__METHOD__,	array(0=>"arr",1=>$arr),		1); 	// [ERROR] Array of keys=>values cant be empty

		$keys = "";
		$values = "";
		$wasprev = false;
		if(is_null($parms))
		{
			foreach($arr as $keyF=>$valueF)
			{
				if(gettype($keyF)[0] == 'i')
				{
					$this->setFail("[".__METHOD__."][E] keyF is an integer, so the value is not initialized for the key {$valueF}");
					continue;
				}
				else
				{
					if($valueF=='?')
						$this->setFail("[".__METHOD__."][E] \"{$keyF}\" is '?' but is not using the SQLSafe method");
					else
					{
						if($valueF == '')
							$this->Log->Write("[".__METHOD__."][I] \"{$keyF}\" is empty");

						if($wasprev == true)
						{
							$keys.=", ";
							$values.=", ";
						}
						$keys.= "`{$keyF}`";
						$values.= "'{$valueF}'";
						$wasprev = true;
					}
				}
			}
		}
		else
		{
			foreach($arr as $keyF=>$valueF)
			{
				if($wasprev == true)
				{
					$keys.=", ";
					$values.=", ";
				}

				if(gettype($keyF)[0] == 'i')
				{
					$keys.= "`{$valueF}`";
					$values.= "?";
				}
				else
				{
					$keys.= "`{$keyF}`";
					if($valueF=='?')
						$values.="?";
					elseif($valueF=='')
					{
						$this->Log->Write("[".__METHOD__."][I] \"{$keyF}\" is empty, we replace it with '?'");
						$values.="?";
					}
					else
						$values.= "'{$valueF}'";
				}
				$wasprev = true;
			}
		}

		$query = "INSERT INTO `{$location}` ({$keys}) VALUES ({$values})";

		if(is_null($parms))
			return $this->query($query);
		return $this->cQuery($query,$parms);
	}
	public function select(string $select=null,string $location,string $condition=null,string $other=null,array $parms=null) : mysqli_result|false
	{
		$this->checkString(__METHOD__,	array(0=>"location",1=>$location),	1); 	// [ERROR] Location cant be empty

		if(is_null($select))
			$select = "*";

		$query = "SELECT {$select} FROM `{$location}`";
		if(!is_null($condition))
			$query.=" WHERE {$condition}";

		if(!is_null($other))
			$query.=" {$other}";

		if(is_null($parms))
			return $this->query($query);
		return $this->cQuery($query,$parms);
	}
	public function delete(string $location,string $condition=null,array $parms=null) : mysqli_result|false
	{
		$this->checkString(__METHOD__,	array(0=>"location",1=>$location),	1); 	// [ERROR] Location cant be empty

		$query = "DELETE FROM `{$location}`";
		if(!is_null($condition))
			$query.=" WHERE {$condition}";

		if(is_null($condition)||is_null($parms))
			return $this->query($query);
		return $this->cQuery($query,$parms);
	}

	private function setFail(string $string) : void
	{
		$this->Log->Write($string);
		$this->OutPut->sendError("Internal problems");
	}
	private function checkString(string $methodName, array $str, int $errorType=0) : void
	{
		if(empty($str[1]))
		{
			if($errorType == 0)
				$this->Log->Write("[{$methodName}][I] We got an empty string for {$str[0]}");
			else
				$this->setFail("[{$methodName}][E] We got an empty string for {$str[0]}");
		}
	}
	private function checkArray(string $methodName, array $str, int $errorType=0) : void
	{
		if(empty($str[1])||!is_array($str[1])||count($str[1]) == 0)
		{
			if($errorType == 0)
				$this->Log->Write("[{$methodName}][I] We got an empty array for {$str[0]}");
			else
				$this->setFail("[{$methodName}][E] We got an empty array for {$str[0]}");
		}
	}
	public function query(string $query) : mysqli_result|false
	{
		$this->checkString(__METHOD__,	array(0=>"query",1=>$query),	1); 	// [ERROR] Query cant be empty	

		$result = $this->mysqli->query($query);
		if ($result == false)
		{
			$this->setFail("[".__METHOD__."][E] We got an error : {$this->mysqli->error}");
			return false;
		}
		return $result;

	}
	public function cQuery(string $query, array $parms) : mysqli_result|false
	{
		$this->checkString(__METHOD__,	array(0=>"query",1=>$query),	1); 	// [ERROR] Query cant be empty
		$this->checkArray (__METHOD__,	array(0=>"parms",1=>$parms),	0); 	// [INFO]  If we get empty array we should use query instead of cQuery

		$this->stmtInit();
		$this->prepare($query);
		$this->bind_param($parms);
		$this->execute();
		return $this->get_result();
	}

	private function stmtInit() : void
	{
		if(!is_null($this->stmt))
		{
			$this->stmt->close();
			unset($this->stmt);
		}
		if(!($this->stmt = $this->mysqli->stmt_init()))
			$this->setFail("[".__METHOD__."][E] We got an error: {$this->stmt->error}");
	}

	private function prepare(string $query) : void
	{
		$this->LogQuery->Write($query);
		if(!($this->stmt->prepare($query)))
			$this->setFail("[".__METHOD__."][E] We got an error: {$this->stmt->error}");
	}

	private function bind_param(array $parms) : void
	{
		$type = "";
		foreach ($parms as $key=>$value)
		{
			$type.=gettype($value)[0];
		}
		$jsonParms = Util::getJson($parms);
		if(!($this->stmt->bind_param($type, ...$parms)))
			$this->setFail("[".__METHOD__."][E] We got an error for stmt->bind_param({$type}, {$jsonParms}), the error: {$this->stmt->error}");

		$this->LogQuery->Write("bind_parm: {$jsonParms}");
	}

	private function execute() : void
	{
		try
		{
			$this->stmt->execute();
		}
		catch(mysqli_sql_exception $e)
		{
			$this->setFail("[".__METHOD__."][E] We got an error: {$e->getMessage()}");
		}
		$this->LogQuery->Write("");
	}
	public function get_result(): mysqli_result|false
	{
		return $this->stmt->get_result();
	}
	public function lastID() : int|string
	{
		return $this->stmt->insert_id;
	}

	public function __destruct()
	{
		$this->stmt->close();
		$this->mysqli->close();
	}
}