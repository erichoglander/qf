<?php
/**
 * Contains the user entity
 */
/**
 * User entity
 *
 * A base for all users with password and login management
 * Built with extending in mind to easily created advanced users
 * with core functionality taken care of
 * @author Eric Höglander
 */
class User_Entity_Core extends Entity {

	/**
	 * Stores any login error
	 * @see authorize
	 * @var string
	 */
	public $login_error;
	
	
	/**
	 * User roles
	 * @var array
	 */
	protected $roles;


	/**
	 * Save user
	 *
	 * Hashes plain-text passwords before saving
	 * @return bool
	 */
	public function save() {
		if (!$this->get("pass"))
			unset($this->fields["pass"]); # Don't set an empty password
		if ($this->get("pass") && substr($this->get("pass"), 0, 2) !== "1#" && strlen($this->get("pass")) != 130) {
			$this->set("salt", $this->generateSalt());
			$this->set("pass", $this->hashPassword($this->get("pass"), $this->get("salt")));
		}
		return parent::save();
	}

	/**
	 * Readable name of the user
	 * @return string
	 */
	public function name() {
		if ($this->id())
			return $this->get("name");
		else
			return "Anonymous";
	}

	/**
	 * Check if the user has been assigned the specified role
	 * @param  string $key
	 * @return bool
	 */
	public function hasRole($key) {
		foreach ($this->roles() as $role) {
			if ($role->machine_name === $key)
				return true;
		}
		return false;
	}
	
	/**
	 * Get all roles assigned to the user
	 * @return array
	 */
	public function roles() {
		if ($this->roles === null) {
			$this->roles = $this->Db->getRows("
					SELECT `role`.* FROM `user_role`
					INNER JOIN `role` ON 
						`role`.id = `user_role`.role_id
					WHERE
						`user_role`.user_id = :id",
					[":id" => $this->id()]);
		}
		return $this->roles;
	}

	/**
	 * Attempt to load user by username
	 *
	 * This method is used when checking if a username is available
	 * @return string
	 */
	public function loadByName($name) {
		$row = $this->Db->getRow("SELECT id FROM `user` WHERE `name` = :name", [":name" => $name]);
		if ($row)
			return $this->load($row->id);
		else
			return false;
	}

	/**
	 * Attempt to load user by e-mail
	 *
	 * This method is used when checking if an e-mail is available
	 * @return string
	 */
	public function loadByEmail($email) {
		$row = $this->Db->getRow("SELECT id FROM `user` WHERE `email` = :email", [":email" => $email]);
		if ($row)
			return $this->load($row->id);
		else
			return false;
	}

	/**
	 * Log out current user
	 */
	public function logout() {
		unset($_SESSION["file_uploaded"]);
		unset($_SESSION["file_upload"]);
		unset($_SESSION["user_id"]);
		unset($_SESSION["superuser_id"]);
	}

	/**
	 * Set login based on the loaded user
	 */
	public function login() {
		$_SESSION["user_id"] = $this->id();
		$this->set("login", REQUEST_TIME);
		$this->save();
		addlog(
				"user", 
				"User session started for ".$this->name(),
				["id" => $this->id(), "name" => $this->get("name")],
				"success");
	}

	/**
	 * Attempt to authorize a user based on username and password
	 * @param  string $name
	 * @param  string $pass
	 * @return bool
	 */
	public function authorize($name, $pass) {
		if ($this->ipFloodProtection()) {
			$this->login_error = "flood";
			addlog("user", "Login IP flood protection", null, "warning");
			return false;
		}
		if (!$this->loadByName($name)) {
			$this->login_error = "invalid_user";
			addlog("user", "Failed login attempt for ".$name, null, "warning");
			return false;
		}
		if ($this->get("status") == 0) {
			$this->login_error = "inactive";
			addlog("user", "Login attempt for inactive user ".$this->get("name"), null, "warning");
			return false;
		}
		if ($this->hasUnconfirmedEmail()) {
			$this->login_error = "unconfirmed_email";
			addlog("user", "Login attempt for user with unconfirmed email ".$this->get("name"), null, "warning");
			return false;
		}
		if ($this->userFloodProtection()) {
			$this->login_error = "flood";
			addlog("user", "Login user flood protection for ".$this->get("name"), null, "warning");
			return false;
		}
		if ($this->hashPassword($pass, $this->get("salt")) !== $this->get("pass")) {
			$this->login_error = "invalid_pass";
			addlog("user", "Failed login attempt for ".$this->get("name"), null, "warning");
			return false;
		}
		return true;
	}

	/**
	 * Check for repeated login attempts based on IP address
	 * @return bool
	 */
	public function ipFloodProtection() {
		$Config = newClass("Config");
		$n = $this->Db->numRows("
			SELECT id FROM `login_attempt` 
			WHERE 
				ip = :ip &&
				created > :time", 
			[
				":ip" => $_SERVER["REMOTE_ADDR"],
				":time" => REQUEST_TIME - $Config->getFloodProtectionTime()
			]);
		if ($n > 5)
			return true;
		return false;
	}

	/**
	 * Check for repeated login attempts based on user id
	 * @return bool
	 */
	public function userFloodProtection() {
		$Config = newClass("Config");
		$n = $this->Db->numRows("
			SELECT id FROM `login_attempt` 
			WHERE 
				user_id = :id &&
				created > :time", 
			[
				":id" => $this->id(),
				":time" => REQUEST_TIME - $Config->getFloodProtectionTime(),
			]);
		if ($n > 5)
			return true;
		return false;
	}

	/**
	 * Check if user has not confirmed the e-mail address
	 * @return bool
	 */
	public function hasUnconfirmedEmail() {
		return $this->get("email_confirmation") && REQUEST_TIME - $this->get("email_confirmation_time") > 60*60*24;
	}

	/**
	 * Attempt to verify a link for resetting password
	 * @return bool
	 */
	public function verifyResetLink($link) {
		if (REQUEST_TIME - $this->get("reset_time") < 60*60*24 && 
				$this->get("reset") === $this->hash($link, "qfresetlink"))
			return true;
		return false;
	}

	/**
	 * Generate a code to be used in a link to reset password
	 * @return string
	 */
	public function generateResetLink() {
		$link = md5(hash("sha512", microtime(true)."qfreset".rand(10001, 20000)));
		$hash = $this->hash($link, "qfresetlink");
		$this->set("reset", $hash);
		$this->set("reset_time", REQUEST_TIME);
		return $link;
	}

	/**
	 * Attempt to verify user e-mail address
	 * @return bool
	 */
	public function verifyEmailConfirmationLink($link) {
		if ($this->get("email_confirmation") === $this->hash($link, "qfemailconfirmationlink"))
			return true;
		return false;
	}

	/**
	 * Generate a code to be used in a link to verify user e-mail address
	 * @return string
	 */
	public function generateEmailConfirmationLink() {
		$link = md5(hash("sha512", "qfconfirm".microtime(true).rand(20001, 30000)));
		$hash = $this->hash($link, "qfemailconfirmationlink");
		$this->set("email_confirmation", $hash);
		$this->set("email_confirmation_time", REQUEST_TIME);
		return $link;
	}

	/**
	 * Hash a string with a salt
	 * @param  string $str
	 * @param  string $salt
	 * @return string
	 */
	public function hash($str, $salt) {
		return hash("sha512", $salt.hash("sha512", $str).hash("sha512", $salt."qfpass"));
	}

	/**
	 * Hash password 
	 * @see hash
	 * @return string
	 */
	public function hashPassword($pass, $salt) {
		return "1#".$this->hash($pass, $salt);
	}


	/**
	 * Generate a salt to be used in hashes
	 * @return string
	 */
	protected function generateSalt() {
		return hash("sha512", microtime(true).rand(1, 10000)."qfsalt");
	}
	
	/**
	 * Database schema
	 * @return array
	 */
	protected function schema() {
		$schema = parent::schema();
		$schema["table"] = "user";
		$schema["fields"]["name"] = [
			"type" => "varchar",
		];
		$schema["fields"]["email"] = [
			"type" => "varchar",
		];
		$schema["fields"]["login"] = [
			"type" => "uint",
		];
		$schema["fields"]["salt"] = [
			"type" => "varchar",
		];
		$schema["fields"]["pass"] = [
			"type" => "varchar",
		];
		$schema["fields"]["reset"] = [
			"type" => "varchar",
		];
		$schema["fields"]["reset_time"] = [
			"type" => "uint",
		];
		$schema["fields"]["email_confirmation"] = [
			"type" => "varchar",
		];
		$schema["fields"]["email_confirmation_time"] = [
			"type" => "uint",
		];
		return $schema;
	}

};