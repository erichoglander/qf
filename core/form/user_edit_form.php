<?php
class UserEdit_Core_Form extends Form {

	public function onSubmit() {
		
	}

	public function validated() {
		if (!parent::validated())
			return false;
		$values = $this->values();
		if ($values['password'] != $values['password_confirm']) {
			$this->setError(t("Passwords mismatch"), "password");
			return false;
		}
		return true;
	}
	
	public function structure($User) {
		$structure = [
			"name" => "user_edit",
			"items" => [
				"name" => [
					"type" => "text",
					"label" => t("Username"),
					"value" => $User->get("name"),
					"required" => true,
				],
				"email" => [
					"type" => "email",
					"label" => t("Email"),
					"value" => $User->get("email"),
					"required" => true,
				],
				"password" => [
					"type" => "password",
					"label" => t("Password"),
				],
				"password_confirm" => [
					"type" => "password",
					"label" => t("Confirm password"),
				],
				"actions" => [
					"type" => "actions",
					"items" => [
						"submit" => [
							"type" => "submit",
							"value" => t("Save"),
						],
						"cancel" => [
							"type" => "button",
							"value" => t("Cancel"),
							"attributes" => [
								"onclick" => "window.history.go(-1)",
							],
						],
					],
				],
			],
		];
		return $structure;
	}

};