<?php
class UserLogin_Form_Core extends Form {


	public function validate($values) {
		$User = newClass("User_Entity", $this->Db);
		if (!$User->authorize($values["name"], $values["pass"])) {
			$this->Db->insert("login_attempt", [
				"created" => REQUEST_TIME,
				"ip" => $_SERVER["REMOTE_ADDR"],
				"user_id" => (int) $User->id(),
			]);
			if ($User->login_error == "flood")
				$this->setError(t("You have attempted to sign in too many times."));
			else if ($User->login_error == "inactive")
				$this->setError(t("Account is inactive."));
			else if ($User->login_error == "unconfirmed_email")
				$this->setError(
					t("Your e-mail address has not been confirmed. To be able to login, confirm your e-mail address.").
					' <a href="'.url("user/resend-email-confirmation/".$User->id()).'">'.t("Resend confirmation e-mail.").'</a>');
			else
				$this->setError(t("Wrong username or password."));
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
					"icon" => "user",
					"required" => true,
					"focus" => true,
				],
				"pass" => [
					"type" => "password",
					"label" => t("Password"),
					"icon" => "lock",
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
								"onclick" => "window.location.href = '".url("user/reset")."'",
							],
						],
					],
				],
			],
		];
	}

};