<?php
class User_Entity_Core extends Entity {

	public $login_error;


	public function save() {
		if (!$this->get("pass"))
			unset($this->fields["pass"]); # Don't set an empty password
		if (substr($this->get("pass"), 0, 2) !== "1#") {
			$this->set("salt", $this->generateSalt());
			$this->set("pass", $this->hashPassword($this->get("pass"), $this->get("salt")));
		}
		if (!parent::save())
			return false;
		$this->Db->delete("user_role", ["user_id" => $this->id()]);
		foreach ($this->get("roles") as $role)
			$this->Db->insert("user_role", ["user_id" => $this->id(), "role_id" => $role->id]);
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
				if ($role->key === $key)
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
		unset($_SESSION["file_upload"]);
		unset($_SESSION["user_id"]);
	}

	public function login() {
		$_SESSION["user_id"] = $this->id();
		$this->set("login", REQUEST_TIME);
		$this->save();
		addlog(
				$this->Db, 
				"user", 
				t("User session started for :user", "en", [":user" => $this->name()]), 
				["id" => $this->id(), "name" => $this->get("name")],
				"success");
	}

	public function authorize($name, $pass) {
		if ($this->ipFloodProtection()) {
			$this->login_error = "flood";
			addlog($this->Db, "user", t("Login IP flood protection"), null, "warning");
			return false;
		}
		if (!$this->loadByName($name)) {
			$this->login_error = "invalid_user";
			addlog($this->Db, "user", t("Failed login attempt for :name", "en", [":name" => $name]), null, "warning");
			return false;
		}
		if (!$this->allowLogin()) {
			$this->login_error = "inactive";
			addlog($this->Db, "user", t("Login attempt for inactive user :name", "en", [":name" => $this->get("name")]), null, "warning");
			return false;
		}
		if ($this->userFloodProtection()) {
			$this->login_error = "flood";
			addlog($this->Db, "user", t("Login user flood protection for :user", "en", [":user" => $this->get("name")]), null, "warning");
			return false;
		}
		if (!$this->hashPassword($pass, $this->get("salt")) === $this->get("pass")) {
			$this->login_error = "invalid_pass";
			addlog($this->Db, "user", t("Failed login attempt for :name", "en", [":name" => $this->get("name")]), null, "warning");
			return false;
		}
		return true;
	}

	public function ipFloodProtection() {
		$n = $this->Db->numRows("
			SELECT id FROM `login_attempt` 
			WHERE 
				ip = :ip &&
				created > :time", 
			[
				":ip" => $_SERVER["REMOTE_ADDR"],
				":time" => REQUEST_TIME - 60*60*12
			]);
		if ($n > 3)
			return true;
		return false;
	}
	public function userFloodProtection() {
		$n = $this->Db->numRows("
			SELECT id FROM `login_attempt` 
			WHERE 
				user_id = :id &&
				created > :time", 
			[
				":id" => $this->id(),
				":time" => REQUEST_TIME - 60*60*12
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
		$link = hash("sha512", microtime(true)."qfreset".rand(10001, 20000));
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
		$link = hash("sha512", "qfconfirm".microtime(true).rand(20001, 30000));
		$hash = $this->hash($link, "qfemailconfirmationlink");
		$this->set("email_confirmation", $hash);
		$this->set("email_confirmation_time", REQUEST_TIME);
		return $link;
	}

	public function hash($str, $salt) {
		return hash("sha512", $salt.hash("sha512", $str).hash("sha512", $salt."qfpass"));
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