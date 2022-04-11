<?php 
class Database
{
	private mysqli $mysqli;
	private LogF $Log;
	private LogF $LogQuery;
	private Output $OutPut;
	private ?mysqli_stmt $stmt = null;

	/**
	 * Construction
	 * 
	 * @param LogF $log
	 * @param LogF $logQuery
	 * @param Output $output
	 * 
	 */
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

	/** 
	 * SQL UPDATE query
	 * @see docs/Database.txt#update
	 * 
	 * @param string $location 
	 * SQL Table name
	 * @param array $arr
	 * An array with `column name`=>(`value` `[not recommended]`,`?` (then you parse the value to $parms)`[recommended]`,`null` (the same procedure as ?))
	 * @param string|null $condition
	 * The condition for UPDATE query
	 * @param array|null $parms
	 * An array with values of `$arr` and `$condition`
	 * 
	 * @return mysqli_result|false
	 * 
	 */
	public function update(string $location, array $arr, ?string $condition, ?array $parms) : mysqli_result|false
	{
		$this->checkString(__METHOD__,  array(0=>"location",1=>$location),  1);     // [ERROR] Location cant be empty
		$this->checkArray (__METHOD__,  array(0=>"arr",1=>$arr),            1);     // [ERROR] Array of keys=>values cant be empty

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

	/** 
	 * SQL INSERT query
	 * 
	 * @see docs/Database.txt#insert
	 * 
	 * @param string $location 
	 * SQL Table name
	 * @param array $arr
	 * An array with `column name`=>(`value` `[not recommended]`,`?` (then you parse the value to $parms)`[recommended]`,`null` (the same procedure as ?))
	 * @param array|null $parms
	 * An array with values of `$arr`
	 * 
	 * @return mysqli_result|false
	 * 
	 */
	public function insert(string $location, array $arr, ?array $parms) : mysqli_result|false
	{
		$this->checkString(__METHOD__, array(0=>"location",1=>$location), 1);   // [ERROR] Location cant be empty
		$this->checkArray (__METHOD__, array(0=>"arr",1=>$arr),           1);   // [ERROR] Array of keys=>values cant be empty

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

	/** 
	 * SQL SELECT query
	 * 
	 * @see docs/Database.txt#select
	 * 
	 * @param string|null $select
	 * Selected columns to get or `null` to be replaced with `*`
	 * @param string $location
	 * SQL Table name
	 * @param string|null $condition
	 * The condition for SELECT query
	 * @param string|null $other
	 * Other parms like order/join/etc
	 * @param array|null $parms
	 * An array with values of `$select` , `$condition` and `$other`
	 * 
	 * @return mysqli_result|false
	 * 
	 */
	public function select(?string $select,string $location,?string $condition,?string $other,?array $parms) : mysqli_result|false
	{
		$this->checkString(__METHOD__,	array(0=>"location",1=>$location),	1);   // [ERROR] Location cant be empty

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
	/**
	 * SQL DELETE query
	 *
	 * @see docs/Database.txt#delete
	 * 
	 * @param string $location
	 * SQL Table name
	 * @param string|null $condition
	 * The condition for DELETE query
	 * @param array|null $parms
	 * An array with values of `$condition`
	 * 
	 * @return mysqli_result|false
	 * 
	 */
	public function delete(string $location, ?string $condition, ?array $parms) : mysqli_result|false
	{
		$this->checkString(__METHOD__,	array(0=>"location",1=>$location),	1); 	// [ERROR] Location cant be empty

		$query = "DELETE FROM `{$location}`";
		if(!is_null($condition))
			$query.=" WHERE {$condition}";

		if(is_null($condition)||is_null($parms))
			return $this->query($query);
		return $this->cQuery($query,$parms);
	}

	/**
	 * Write to log and then throw the output
	 *
	 * @param string $string
	 * 
	 * @return void
	 * 
	 */
	private function setFail(string $string) : void
	{
		$this->Log->Write($string);
		$this->OutPut->sendError("Internal problems");
	}
	/**
	 * Check if the string is empty
	 *
	 * @param string $methodName
	 * Method Name for log
	 * @param array $str
	 * $str[0]=`string Name`, $str[1]=`value of the string`
	 * @param int $errorType
	 * `0` will log info that the string is empty, `1` will log error and throw the output to failure
	 * 
	 * @return void
	 * 
	 */
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
	/**
	 * Check if the array is empty
	 *
	 * @param string $methodName
	 * Method Name for log
	 * @param array $str
	 * $str[0]=`array Name`, $str[1]=`the array`
	 * @param int $errorType
	 * `0` will log info that the array is empty, `1` will log error and throw the output to failure
	 * 
	 * @return void
	 * 
	 */
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
	/**
	 * Execute a SQL Query
	 *
	 * @param string $query
	 * 
	 * @return mysqli_result|false
	 * 
	 */
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
	/**
	 * Execute a SQL Query with prepare->bind_param
	 *
	 * @param string $query
	 * @param array $parms
	 * 
	 * @return mysqli_result|false
	 * 
	 */
	public function cQuery(string $query, array $parms) : mysqli_result|false
	{
		$this->checkString(__METHOD__,  array(0=>"query",1=>$query),    1);     // [ERROR] Query cant be empty
		$this->checkArray (__METHOD__,  array(0=>"parms",1=>$parms),    0);     // [INFO]  If we get empty array we should use query instead of cQuery

		$this->stmtInit();
		$this->prepare($query);
		$this->bind_param($parms);
		$this->execute();
		return $this->get_result();
	}

	/**
	 * Initializes a mysqli statement
	 *
	 * @return void
	 * 
	 */
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

	/**
	 * Prepare the query to statement
	 *
	 * @param string $query
	 * 
	 * @return void
	 * 
	 */
	private function prepare(string $query) : void
	{
		$this->LogQuery->Write($query);
		if(!($this->stmt->prepare($query)))
			$this->setFail("[".__METHOD__."][E] We got an error: {$this->stmt->error}");
	}

	/**
	 * Parse the parameters to statement
	 *
	 * @param array $parms
	 * 
	 * @return void
	 * 
	 */
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

	/**
	 * Execute the statement
	 *
	 * @return void
	 * 
	 */
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

	/**
	 * Get the result
	 *
	 * @return mysqli_result|false
	 * 
	 */
	public function get_result(): mysqli_result|false
	{
		return $this->stmt->get_result();
	}

	/**
	 * Get the last id from insert
	 *
	 * @return int|string
	 * 
	 */
	public function lastID() : int|string
	{
		return $this->stmt->insert_id;
	}

	/**
	 * Destruct
	 *
	 * @return [type]
	 * 
	 */
	public function __destruct()
	{
		$this->stmt->close();
		$this->mysqli->close();
	}
}