<?php
class User_Entity_Core extends Entity {

	public function save() {
		if (substr($this->get("pass"), 0, 2) !== "1#") {
			$this->fields['salt'] = $this->generateSalt();
			$this->fields['pass'] = $this->hash($this->get("pass"), $this->get("salt"));
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
		$this->roles = $this->Db->getRows(
				"SELECT * FROM `role` 
				INNER JOIN `user_role` ON 
					`user_role`.role_id = `role`.id
				WHERE 
					`user_role`.user_id = :id", 
				[":id" => $this->id()]);
		return true;
	}

	public function name() {
		if ($this->id())
			return $this->get("name");
		else
			return "Anonymous";
	}

	public function hasRole($title) {
		$roles = $this->get("roles");
		if (!empty($roles)) {
			foreach ($roles as $role) {
				if ($role->title === $title)
					return true;
			}
		}
		return false;
	}

	public function logout() {
		unset($_SESSION['user_id']);
	}

	public function login() {
		$_SESSION['user_id'] = $this->id();
		$this->set("login", REQUEST_TIME);
		$this->save();
	}

	public function authorize($pass) {
		return $this->hash($pass, $this->get("salt")) === $this->get("pass");
	}

	public function verifyResetLink($link) {
		return $link === $this->hash($this->get("reset"), "qfresetlink");
	}

	public function generateResetLink() {
		$hash = hash("sha512", microtime(true)."qfreset".rand(10001, 20000));
		$link = $this->hash($hash, "qfresetlink");
		$this->set("reset", $hash);
		return $link;
	}

	public function verifyEmailConfirmationLink($link) {
		return $link === $this->hash($this->get("email_confirmation"), "qfemailconfirmationlink");
	}

	public function generateEmailConfirmationLink() {
		$hash = hash("sha512", "qfconfirm".microtime(true).rand(20001, 30000));
		$link = $this->hash($hash, "qfemailconfirmationlink");
		$this->set("email_confirmation", $hash);
		return $link;
	}


	protected function hash($pass, $salt) {
		return "1#".hash("sha512", $salt.hash("sha512", $pass).hash("sha512", $salt."qfpass"));
	}

	protected function generateSalt() {
		return hash("sha512", microtime(true).rand(1, 10000)."qfsalt");
	}
	
	protected function schema() {
		$schema = parent::schema();
		$schema['table'] = "user";
		$schema['fields']['name'] = [
			"type" => "varchar",
		];
		$schema['fields']['email'] = [
			"type" => "varchar",
		];
		$schema['fields']['login'] = [
			"type" => "uint",
		];
		$schema['fields']['salt'] = [
			"type" => "varchar",
		];
		$schema['fields']['pass'] = [
			"type" => "varchar",
		];
		$schema['fields']['reset'] = [
			"type" => "varchar",
		];
	}

};