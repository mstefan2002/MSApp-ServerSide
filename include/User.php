<?php
class User
{
	public string $ip;
	private Database $db;

	/**
	 * Construct
	 *
	 * @param Database $db
	 * 
	 */
	public function __construct(Database $db)
	{
		$this->ip = Util::getUserIP();
		$this->db = $db;
	}

	/**
	 * Verify if an account exist and optional if the password match
	 *
	 * @param string $email        Email of the account
	 * @param string $password     Password of the account, if is `empty` the password wont be checking
	 * 
	 * @return int                 `2`=wtf exception(many accounts with that email), `1`=password match, `0`=password dont match, 
	 *                             `-1`=the account doesnt exist, `-2`=the account exist
	 * 
	 */
	public function verify(string $email, string $password="") : int
	{
		$table = Tables::Accounts(true);
		$fieldEmail = $table->email;
		$fieldPassword = $table->password;

		$result = $this->db->select(
										"`{$fieldPassword}`",           // select
										Tables::Accounts(false),        // location
										"`{$fieldEmail}`=?",            // condition
										null,                           // others
										array($email)                   // parms
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
	/**
	 * Register new account
	 *
	 * @param string $email
	 * @param string $password
	 * @param string $name
	 * 
	 */
	public function add(string $email, string $password, string $name) : void
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
	}
}
?>