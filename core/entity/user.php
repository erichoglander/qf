<?php
class User_Core_Entity extends Entity {

	public function save() {
		if (substr($this->get("pass"), 0, 2) !== "1#") {
			$this->fields['salt'] = $this->generateSalt();
			$this->fields['pass'] = $this->hash($this->get("pass"), $this->get("salt"));
		}
		return parent::save();
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
		$reset = hash("sha512", microtime(true)."qfreset".rand(10001, 20000));
		$link = $this->hash($reset, "qfresetlink");
		$this->set("reset", $reset);
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