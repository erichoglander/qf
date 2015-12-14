<?php
class User_Entity_Core extends Entity {

	public $login_error;


	public function save() {
		if (!$this->get("pass"))
			unset($this->fields["pass"]); # Don't set an empty password
		if ($this->get("pass") && substr($this->get("pass"), 0, 2) !== "1#" && strlen($this->get("pass")) != 130) {
			$this->set("salt", $this->generateSalt());
			$this->set("pass", $this->hashPassword($this->get("pass"), $this->get("salt")));
		}
		if (!parent::save())
			return false;
		$this->Db->delete("user_role", ["user_id" => $this->id()]);
		if (!empty($this->get("roles"))) {
			foreach ($this->get("roles") as $role)
				$this->Db->insert("user_role", ["user_id" => $this->id(), "role_id" => $role->id]);
		}
		return true;
	}

	public function load($id) {
		if (!parent::load($id))
			return false;
		$this->set("roles", $this->Db->getRows(
				"SELECT `role`.* FROM `role` 
				INNER JOIN `user_role` ON 
					`user_role`.role_id = `role`.id
				WHERE 
					`user_role`.user_id = :id", 
				[":id" => $this->id()]));
		return true;
	}

	public function name() {
		if ($this->id())
			return $this->get("name");
		else
			return "Anonymous";
	}

	public function hasRole($key) {
		$roles = $this->get("roles");
		if (!empty($roles)) {
			foreach ($roles as $role) {
				if ($role->machine_name === $key)
					return true;
			}
		}
		return false;
	}

	public function loadByName($name) {
		$row = $this->Db->getRow("SELECT id FROM `user` WHERE `name` = :name", [":name" => $name]);
		if ($row)
			return $this->load($row->id);
		else
			return false;
	}
	public function loadByEmail($email) {
		$row = $this->Db->getRow("SELECT id FROM `user` WHERE `email` = :email", [":email" => $email]);
		if ($row)
			return $this->load($row->id);
		else
			return false;
	}

	public function logout() {
		unset($_SESSION["file_uploaded"]);
		unset($_SESSION["file_upload"]);
		unset($_SESSION["user_id"]);
		unset($_SESSION["superuser_id"]);
	}

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
		if (!$this->allowLogin()) {
			$this->login_error = "inactive";
			addlog("user", "Login attempt for inactive user ".$this->get("name"), null, "warning");
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
		if ($n > 3)
			return true;
		return false;
	}
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

	public function allowLogin() {
		if ($this->get("status") != 1)
			return false;
		if ($this->get("email_confirmation") && REQUEST_TIME - $this->get("email_confirmation_time") > 60*60*24)
			return false;
		return true;
	}

	public function verifyResetLink($link) {
		if (REQUEST_TIME - $this->get("reset_time") < 60*60*24 && 
				$this->get("reset") === $this->hash($link, "qfresetlink"))
			return true;
		return false;
	}

	public function generateResetLink() {
		$link = md5(hash("sha512", microtime(true)."qfreset".rand(10001, 20000)));
		$hash = $this->hash($link, "qfresetlink");
		$this->set("reset", $hash);
		$this->set("reset_time", REQUEST_TIME);
		return $link;
	}

	public function verifyEmailConfirmationLink($link) {
		if (REQUEST_TIME - $this->get("email_confirmation_time") < 60*60*24 &&
				$this->get("email_confirmation") === $this->hash($link, "qfemailconfirmationlink"))
			return true;
		return false;
	}

	public function generateEmailConfirmationLink() {
		$link = md5(hash("sha512", "qfconfirm".microtime(true).rand(20001, 30000)));
		$hash = $this->hash($link, "qfemailconfirmationlink");
		$this->set("email_confirmation", $hash);
		$this->set("email_confirmation_time", REQUEST_TIME);
		return $link;
	}

	public function hash($str, $salt, $len = null) {
		$hash = hash("sha512", $salt.hash("sha512", $str).hash("sha512", $salt."qfpass"));
		if ($len)
			$hash = substr($len, 0, $len);
		return $hash;
	}

	public function hashPassword($pass, $salt) {
		return "1#".$this->hash($pass, $salt);
	}


	protected function generateSalt() {
		return hash("sha512", microtime(true).rand(1, 10000)."qfsalt");
	}
	
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