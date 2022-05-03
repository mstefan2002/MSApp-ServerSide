<?php 
class Database
{
	private mysqli $mysqli;
	private LogF $Log;
	private Output $OutPut;
	private ?mysqli_stmt $stmt = null;
	private ?array $avoidCode=null;
	private int $countQuery=0;
	private bool $usingTransaction=false;

	/**
	 * Construction
	 * 
	 * @param LogF $log
	 * @param Output $output
	 * 
	 */
	public function __construct(LogF $log, Output $output)
	{
		$this->Log = $log;
		$this->OutPut = $output;

		$host = Config::$SQLHost;
		$user = Config::$SQLUser;
		$pass = Config::$SQLPassword;
		$db = Config::$SQLDB;
		try
		{
			$this->mysqli = new mysqli($host, $user, $pass, $db);
		}
		catch(mysqli_sql_exception $e)
		{
			$this->setFail("[".__METHOD__."][E] We cant connect to the database, the error: {$e->getMessage()}[{$e->getCode()}]");
		}
	}

	/** 
	 * SQL UPDATE query
	 * @see docs/Database.txt#update
	 * 
	 * @param string $location         SQL Table name
	 * @param array $arr               An array with `column name`=>(`value` `[not recommended]`,`?` (then you parse the value to $parms)`[recommended]`,
	 *                                 `null` (the same procedure as ?))
	 * @param string|null $condition   The condition for UPDATE query
	 * @param array|null $parms        An array with values of `$arr` and `$condition`
	 * 
	 * @return mysqli_result|bool
	 * 
	 */
	public function update(string $location, array $arr, ?string $condition, ?array $parms) : mysqli_result|bool
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
	 * @param string $location         SQL Table name
	 * @param array $arr               An array with `column name`=>(`value` `[not recommended]`,`?` (then you parse the value to $parms)`[recommended]`,`null` (the same procedure as ?))
	 * @param array|null $parms        An array with values of `$arr`
	 * 
	 * @return mysqli_result|bool
	 * 
	 */
	public function insert(string $location, array $arr, ?array $parms) : mysqli_result|bool
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
	 * @param string|null $select       Selected columns to get or `null` to be replaced with `*`
	 * @param string $location          SQL Table name
	 * @param string|null $condition    The condition for SELECT query
	 * @param string|null $other        Other parms like order/join/etc
	 * @param array|null $parms         An array with values of `$select` , `$condition` and `$other`
	 * 
	 * @return mysqli_result|bool
	 * 
	 */
	public function select(?string $select,string $location,?string $condition,?string $other,?array $parms) : mysqli_result|bool
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
	 * @param string $location          SQL Table name
	 * @param string|null $condition    The condition for DELETE query
	 * @param array|null $parms         An array with values of `$condition`
	 * 
	 * @return mysqli_result|bool
	 * 
	 */
	public function delete(string $location, ?string $condition, ?array $parms) : mysqli_result|bool
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
		if($this->usingTransaction == true)
			$this->rollBack();

		$this->OutPut->sendError("Internal problems");
	}
	/**
	 * Check if the string is empty
	 *
	 * @param string $methodName      Method Name for log
	 * @param array $str              $str[0]=`string Name`, $str[1]=`value of the string`
	 * @param int $errorType          `0` will log info that the string is empty, `1` will log error and throw the output to failure
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
	 * @param string $methodName     Method Name for log
	 * @param array $str             $str[0]=`array Name`, $str[1]=`the array`
	 * @param int $errorType         `0` will log info that the array is empty, `1` will log error and throw the output to failure
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
	 * Prevents the script from shutting down when the following errors are found in trycatch
	 *
	 * @param array $avoidCode    If we trycatch an error that is in this array the script will continue run
	 * @param int $countQuery          The number of query we prevent
	 * 
	 * @return void
	 * 
	 */
	public function catchErrorCode(array $avoidCode, int $countQuery=1) : void
	{
		$this->avoidCode = $avoidCode;
		$this->countQuery = $countQuery;
	}

	/**
	 * Execute a SQL Query
	 *
	 * @param string $query
	 * 
	 * @return mysqli_result|bool
	 * 
	 */
	public function query(string $query) : mysqli_result|bool
	{
		$this->checkString(__METHOD__,	array(0=>"query",1=>$query),	1); 	// [ERROR] Query cant be empty	
		try
		{
			$result = $this->mysqli->query($query);
		}
		catch(mysqli_sql_exception $e)
		{
			if($this->countQuery >0 && !is_null($this->avoidCode) && array_search($e->getCode(),$this->avoidCode) !== false)
			{
				--$this->countQuery;
				return false;
			}
			else
				$this->setFail("[".__METHOD__."][E] We got an error: {$e->getMessage()}[{$e->getCode()}]\nFor this query: {$query}");
		}
		if($this->countQuery > 0)
			--$this->countQuery;
		return $result;

	}
	/**
	 * Execute a SQL Query with prepare->bind_param
	 *
	 * @param string $query
	 * @param array $parms
	 * 
	 * @return mysqli_result|bool
	 * 
	 */
	public function cQuery(string $query, array $parms) : mysqli_result|bool
	{
		$this->checkString(__METHOD__,  array(0=>"query",1=>$query),    1);     // [ERROR] Query cant be empty
		$this->checkArray (__METHOD__,  array(0=>"parms",1=>$parms),    0);     // [INFO]  If we get empty array we should use query instead of cQuery

		$type = "";
		foreach ($parms as $key=>$value)
		{
			$type.=gettype($value)[0];
		}
		$jsonParms = Util::getJson($parms);

		try
		{
			if(!is_null($this->stmt))
			{
				$this->stmt->close();
				unset($this->stmt);
			}
			$this->stmt = $this->mysqli->stmt_init();
			$this->stmt->prepare($query);
			$this->stmt->bind_param($type, ...$parms);
			$this->stmt->execute();
		}
		catch(mysqli_sql_exception $e)
		{
			if($this->countQuery >0 && !is_null($this->avoidCode) && array_search($e->getCode(),$this->avoidCode) !== false)
			{
				--$this->countQuery;
				return false;
			}
			else
				$this->setFail("[".__METHOD__."][E] We got an error: {$e->getMessage()}[{$e->getCode()}]\nstmt->bind_param({$type}, {$jsonParms})");
		}
		if($this->countQuery > 0)
			--$this->countQuery;
		return $this->get_result();
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
	 * Get error code from mysqli/statement
	 *
	 * @return int
	 * 
	 */
	public function get_errorCode() : int
	{
		if(!is_null($this->stmt) && $this->stmt->errno != 0)
			return $this->stmt->errno;
			
		return $this->mysqli->errno;
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
	 * Start a transaction
	 *
	 * @return void
	 * 
	 */
	public function start_transaction() : void
	{
		$this->mysqli->begin_transaction();
		$this->usingTransaction = true;
	}

	/**
	 * Close a transaction
	 *
	 * @return void
	 * 
	 */
	public function end_transaction() : void
	{
		$this->mysqli->commit();
		$this->usingTransaction = false;
	}

	public function rollBack() : void
	{
		$this->mysqli->rollback();
		$this->usingTransaction = false;
	}
	/**
	 * Destruct
	 */
	public function __destruct()
	{
		$this->stmt->close();
		$this->mysqli->close();
	}
}