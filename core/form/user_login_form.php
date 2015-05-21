<?php
class UserLogin_Form extends Form {


	public function validate($values) {
		$User = newClass("User_Entity", $this->Db);
		if (!$User->loadByName($values['name']) || !$User->authorize($values['password'])) {
			$this->setError(t("Incorrect username or password"));
			return false;
		}
		return true;
	}

	
	protected function structure() {
		return [
			"name" => "user_login",
			"items" => [
				"name" => [
					"type" => "text",
					"label" => t("Username"),
					"required" => true,
				],
				"password" => [
					"type" => "password",
					"label" => t("Password"),
					"required" => true,
				],
				"actions" => [
					"type" => "actions",
					"items" => [
						"submit" => [
							"type" => "submit",
							"value" => t("Sign in"),
						],
						"reset" => [
							"type" => "button",
							"value" => t("Forgot password"),
							"attributes" => [
								"onclick" => "window.location.href = '/user/reset'",
							],
						],
					],
				],
			],
		];
	}

};