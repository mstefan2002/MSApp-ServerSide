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
	 * Execute select query on accounts table
	 *
	 * @param string|null $fields
	 * @param string|null $condition
	 * @param string|null $other
	 * @param array|null $parms
	 * 
	 * @return array|int
	 * 
	 */
	private function select(?string $fields=null, ?string $condition=null, ?string $other=null, ?array $parms=null) : array|int
	{
		$result = $this->db->select(
										$fields,                        // select
										Tables::Accounts(false),        // location
										$condition,                     // condition
										$other,                         // others
										$parms                          // parms
				    				);

		if(isset($result->num_rows) && $result->num_rows > 0)
		{
			if($result->num_rows > 1)
				return 2;
			return $result->fetch_array(MYSQLI_ASSOC);
		}
		return -1;
	}

	/**
	 * Verify if an account exist with that tag
	 *
	 * @param string $tag
	 * 
	 * @return int                 `1`=exist, `0`=doesnt exist, 
	 * 
	 */
	public function verifyTag(string $tag) : int
	{
		$table = Tables::Accounts(true);
		$fieldID = $table->id;
		$fieldTag = $table->tag;


		$result = $this->select(
									"`{$fieldID}`",                 // select
									"`{$fieldTag}`=?",              // condition
									null,                           // other
									array($tag)                     // parms
				    			);

		if(is_array($result)||$result == 2)
			return 1;

		return 0;
	}

	/**
	 * Update the tag field in DB
	 *
	 * @param string $tag
	 * 
	 */
	public function setTag(string $tag) : void
	{
		$table = Tables::Accounts(true);
		$fieldEmail = $table->email;
		$fieldTag = $table->tag;

		$this->db->update(
							Tables::Accounts(false),
							array($fieldTag),
							"`{$fieldEmail}`=?",
							array($tag, $this->email)
		);
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

		$result = $this->select(
									"`{$fieldPassword}`",           // select
									"`{$fieldEmail}`=?",            // condition
									null,                           // other
									array($this->email)             // parms
				    			);

		if(is_array($result))
		{
			if(empty($password))
				return -2;

			if(password_verify($password,$result[$fieldPassword]))
				return 1;

			return 0;
		}
		return $result;
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


		$passwordEncrypted = password_hash($password,PASSWORD_DEFAULT);

		$this->db->insert(
					Tables::Accounts(false),
					array($fieldEmail,$fieldPassword,$fieldName),
					array($this->email,$passwordEncrypted,$name)
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

		$result = $this->select(
										"`{$fieldVerify}`",             // select
										"`{$fieldEmail}`=?",            // condition
										null,                           // others
										array($this->email)             // parms
				    				);

		if(is_array($result))
			return $result[$fieldVerify];

		return $result;
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