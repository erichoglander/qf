<?php
class UserChangePassword_Form_Core extends Form {

	public function validate($values) { 
		if ($values["pass"] !== $values["pass_confirm"]) {
			$this->setError(t("Passwords mismatch"), "pass");
			return false;
		}
		return true;
	}
	

	protected function structure() {
		return [
			"name" => "user_change_password",
			"items" => [
				"pass" => [
					"type" => "password",
					"label" => t("New password"),
					"required" => true,
				],
				"pass_confirm" => [
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