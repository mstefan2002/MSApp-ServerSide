<?php
class User
{
	public string $ip;
	private Database $db;

	public function __construct(Database $db)
	{
		$this->ip = Util::getUserIP();
		$this->db = $db;
	}

	public function verify(string $email, string $password="") : int
	{
		$table = Tables::Accounts(true);
		$fieldEmail = $table->email;
		$fieldPassword = $table->password;

		$result = $this->db->select(
						"`{$fieldPassword}`",				// select
						Tables::Accounts(false),			// location
						"`{$fieldEmail}`=?",				// condition
						null,						// others
						array($email)					// parms
				     );
		if(isset($result->num_rows) && $result->num_rows > 0)
		{
			if(empty($password))
				return -2;

			if($result->num_rows > 1)
				return 2;

			$row = $result->fetch_array(MYSQLI_ASSOC);
       			if($row[$fieldPassword] == $password)
				return 1;

			return 0;
		}
		return -1;
	}
	public function add(string $email, string $password, string $name) : array
	{
		$table = Tables::Accounts(true);
		$fieldEmail = $table->email;
		$fieldPassword = $table->password;
		$fieldName = $table->name;

		$this->db->insert(
					Tables::Accounts(false),
					array($fieldEmail,$fieldPassword,$fieldName),
					array($email,$password,$name)
				);

		return EmailVerify::add($this->db,$email);
	}
}
?>