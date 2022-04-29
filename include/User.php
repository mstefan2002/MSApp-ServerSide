<?php
class User
{
	public string $ip;
	private Database $db;
	private string $email;

	/**
	 * Construct
	 *
	 * @param Database $db
	 * @param string $email
	 * 
	 */
	public function __construct(Database $db, string $email)
	{
		$this->ip    = Util::getUserIP();
		$this->db    = $db;
		$this->email = $email;
	}

	/**
	 * Verify if an account exist and optional if the password match
	 *
	 * @param string $password     Password of the account, if is `empty` the password wont be checking
	 * 
	 * @return int                 `2`=wtf exception(many accounts with that email), `1`=password match, `0`=password dont match, 
	 *                             `-1`=the account doesnt exist, `-2`=the account exist
	 * 
	 */
	public function verify(string $password="") : int
	{
		$table = Tables::Accounts(true);
		$fieldEmail = $table->email;
		$fieldPassword = $table->password;

		$result = $this->db->select(
										"`{$fieldPassword}`",           // select
										Tables::Accounts(false),        // location
										"`{$fieldEmail}`=?",            // condition
										null,                           // others
										array($this->email)             // parms
				    				);

		if(isset($result->num_rows) && $result->num_rows > 0)
		{
			if(empty($password))
			{
					return -2;
			}

			if($result->num_rows > 1)
			{
				return 2;
			}

			$row = $result->fetch_array(MYSQLI_ASSOC);
			if(hash_equals($row[$fieldPassword],$password))
			{
				return 1;
			}

			return 0;
		}
		return -1;
	}
	/**
	 * Register new account
	 *
	 * @param string $password
	 * @param string $name
	 * 
	 */
	public function add(string $password, string $name) : void
	{
		$table = Tables::Accounts(true);
		$fieldEmail = $table->email;
		$fieldPassword = $table->password;
		$fieldName = $table->name;

		$this->db->insert(
					Tables::Accounts(false),
					array($fieldEmail,$fieldPassword,$fieldName),
					array($this->email,$password,$name)
				);
	}

	/**
	 * Update the email verification field in DB
	 *
	 * @return void
	 * 
	 */
	public function setValidate() : void
	{
		$table = Tables::Accounts(true);
		$fieldEmail = $table->email;
		$fieldEmailVerify = $table->emailVerify;

		$this->db->update(
							Tables::Accounts(false),
							array($fieldEmailVerify=>1),
							"`{$fieldEmail}`=?",
							array($this->email)
		);
	}

	/**
	 * Check if the account have email validation
	 * 
	 * @return int                 `2`=wtf exception(many accounts with that email), `1`=is validated, `0`=is not validated, 
	 *                             `-1`=the account doesnt exist
	 * 
	 */
	public function getValidation() : int
	{
		$table = Tables::Accounts(true);
		$fieldEmail = $table->email;
		$fieldVerify = $table->emailVerify;

		$result = $this->db->select(
										"`{$fieldVerify}`",             // select
										Tables::Accounts(false),        // location
										"`{$fieldEmail}`=?",            // condition
										null,                           // others
										array($this->email)             // parms
				    				);

		if(isset($result->num_rows) && $result->num_rows > 0)
		{
			if($result->num_rows > 1)
			{
				return 2;
			}

			$row = $result->fetch_array(MYSQLI_ASSOC);
			return $row[$fieldVerify];
		}
		return -1;
	}


	/**
	 * Delete the account
	 *
	 * @return void
	 * 
	 */
	public function remove() : void
	{
		$table = Tables::Accounts(true);
		$fieldEmail = $table->email;

		$this->db->delete(
							Tables::Accounts(false),
							"`{$fieldEmail}`=?",
							array($this->email)
		);
	}
}
?>