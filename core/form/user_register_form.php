<?php
class UserRegister_Form extends Form {

	public function validated() {
		if (!parent::validated())
			return false;
		$values = $this->values();
		if ($values['password'] != $values['password_confirm']) {
			$this->setError(t("Passwords mismatch"), "password");
			return false;
		}
		$row = $this->Db->getRow("SELECT id FROM `user` WHERE name = :name", [":name" => $values['name']]);
		if ($row) {
			$this->setError(t("Username is already taken"), "name");
			return false;
		}
		$row = $this->Db->getRow("SELECT id FROM `user` WHERE email = :email", [":email" => $values['email']]);
		if ($row) {
			$this->setError(t("E-mail is already taken"), "email");
			return false;
		}
		return true;
	}
	
	protected function structure() {
		return [
			"name" => "user_register",
			"items" => [
				"name" => [
					"type" => "text",
					"label" => t("Username"),
					"required" => true,
				],
				"email" => [
					"type" => "email",
					"label" => t("E-mail"),
					"required" => true,
				],
				"password" => [
					"type" => "password",
					"label" => t("Password"),
					"required" => true,
				],
				"password_confirm" => [
					"type" => "password",
					"label" => t("Confirm password"),
					"required" => true,
				],
				"actions" => [
					"type" => "actions",
					"items" => [
						"submit" => [
							"type" => "submit",
							"value" => t("Register"),
						],
					],
				],
			],
		];
	}

};