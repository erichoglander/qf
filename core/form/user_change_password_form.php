<?php
class UserChangePassword_Form extends Form {

	public function validate($values) { 
		if ($values["password"] !== $values["password_confirm"]) {
			$this->setError(t("Passwords mismatch"), "password");
			return false;
		}
		return true;
	}
	

	protected function structure() {
		return [
			"name" => "user_change_password",
			"items" => [
				"password" => [
					"type" => "password",
					"label" => t("New password"),
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
							"value" => t("Change password"),
						],
					],
				],
			],
		];
	}

};