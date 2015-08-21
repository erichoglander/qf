<?php
class UserSettings_Form_Core extends Form {

	public function validate($values) {
		$User = $this->get("User");
		if ($values["pass"] != $values["pass_confirm"]) {
			$this->setError(t("Passwords mismatch"), "pass");
			return false;
		}
		$row = $this->Db->getRow("SELECT id FROM `user` WHERE email = :email", [":email" => $values["email"]]);
		if ($row && (!$User || $row->id != $User->id())) {
			$this->setError(t("E-mail unavailable"), "email");
			return false;
		}
		if ($User && $User->get("name") == $User->get("email")) {
			$row = $this->Db->getRow("SELECT id FROM `user` WHERE name = :email", [":email" => $values["email"]]);
			if ($row && $row->id != $User->id()) {
				$this->setError(t("E-mail unavailable"), "email");
				return false;
			}
		}
		return true;
	}
	
	
	protected function structure() {
		$User = $this->get("User");
		$structure = [
			"name" => "user_settings",
			"items" => [
				"email" => [
					"type" => "email",
					"label" => t("Email"),
					"value" => ($User ? $User->get("email") : null),
					"required" => true,
				],
				"pass" => [
					"type" => "password",
					"label" => t("Change password"),
					"icon" => "lock",
				],
				"pass_confirm" => [
					"type" => "password",
					"label" => t("Confirm password"),
					"icon" => "lock",
				],
				"actions" => $this->defaultActions(),
			],
		];
		return $structure;
	}

};