<?php
class LogD implements Log
{
	private Database $db;
	private string $tableName;

	/**
	 * Construct
	 *
	 * @param Database $db
	 * @param string $tableName      The name of the table, the `default` value you can find at Config.php->$LogDefault
	 * 
	 */
	public function __construct(Database $db, string $tableName="")
	{
		$this->db = $db;
		if(empty($tableName))
		{
			$tableName=Config::$LogDefault;
		}
		
		$table = Tables::Logs(true);
		$fieldId = $table->id;
		$fieldType = $table->type;
		$fieldTypeLog = $table->typeLog;
		$fieldMethod = $table->method;
		$fieldMessage = $table->message;
		$fieldTime = $table->created;

		$this->db->query(
							"CREATE TABLE IF NOT EXISTS `{$tableName}` (".
								"`{$fieldId}`      int(11)      NOT NULL AUTO_INCREMENT PRIMARY KEY,".
								"`{$fieldType}`    varchar(16)  NOT NULL,".
								"`{$fieldTypeLog}` char(1)      NULL,". 
								"`{$fieldMethod}`  varchar(64)  NULL,". 
								"`{$fieldMessage}` varchar(255) NOT NULL,". 
								"`{$fieldTime}`    timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP".
							")ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
						);
		
		$this->tableName = $tableName;
	}


	/**
	 * Insert the log into database

	 * @param string $message
	 * 
	 * @return void
	 * 
	 */
	public function Write(string $message) : void
	{
		$method = "";
		$typeLog = "";
		
		$method = Util::get_string_between($message,'[',']');
		if(!empty($method))
			$message = str_replace("[{$method}]","",$message);

		$typeLog = Util::get_string_between($message,'[',']');
		if(!empty($typeLog))
			$message = str_replace("[{$typeLog}] ","",$message);

		$table = Tables::Logs(true);
		$fieldType = $table->type;
		$fieldTypeLog = $table->typeLog;
		$fieldMethod = $table->method;
		$fieldMessage = $table->message;

		$type = "null";
		if(isset($_POST['type']))
			$type = $_POST['type'];
		elseif(isset($_GET['type']))
			$type = $_GET['type'];

		if(is_null($method))
			$method = "null";

		$this->db->insert(
							$this->tableName,
							array($fieldType, $fieldTypeLog, $fieldMethod, $fieldMessage),
							array($type,      $typeLog,      $method,      $message)
		);
	}
}
?>