<?php
class UserEdit_Form_Core extends Form {

	public function validate($values) {
		$User = $this->get("User");
		if ($values["pass"] != $values["pass_confirm"]) {
			$this->setError(t("Passwords mismatch"), "pass");
			return false;
		}
		$row = $this->Db->getRow("SELECT id FROM `user` WHERE name = :name", [":name" => $values["name"]]);
		if ($row && (!$User || $row->id != $User->id())) {
			$this->setError(t("Username unavailable"), "name");
			return false;
		}
		$row = $this->Db->getRow("SELECT id FROM `user` WHERE email = :email", [":email" => $values["email"]]);
		if ($row && (!$User || $row->id != $User->id())) {
			$this->setError(t("E-mail unavailable"), "email");
			return false;
		}
		return true;
	}
	
	public function structure() {
		$User = $this->get("User");
		$role_rows = $this->Db->getRows("SELECT * FROM `role` ORDER BY title ASC");
		$rolesop = [];
		foreach ($role_rows as $row)
			$rolesop[$row->id] = t($row->title);
		$roles = [];
		if ($User) {
			foreach ($User->get("roles") as $role)
				$roles[] = $role->id;
		}
		$structure = [
			"name" => "user_edit",
			"items" => [
				"name" => [
					"type" => "text",
					"label" => t("Username"),
					"value" => ($User ? $User->get("name") : null),
					"validation" => "username",
					"icon" => "user",
					"focus" => ($User ? false : true),
					"required" => true,
				],
				"email" => [
					"type" => "email",
					"label" => t("Email"),
					"value" => ($User ? $User->get("email") : null),
					"required" => true,
				],
				"pass" => [
					"type" => "password",
					"label" => t("Password"),
					"icon" => "key",
				],
				"pass_confirm" => [
					"type" => "password",
					"label" => t("Confirm password"),
					"icon" => "key",
				],
				"roles" => [
					"type" => "checkboxes",
					"label" => t("Roles"),
					"options" => $rolesop,
					"value" => $roles,
				],
				"myfiles" => [
					"type" => "container",
					"items"=> [
						"avatar" => [
							"type" => "file",
							"label" => t("Avatar"),
							"file_extensions" => ["txt", "png", "jpg"],
						],
					],
				],
				"myfiles2" => [
					"type" => "container",
					"items" => [
						"profile" => [
							"type" => "file",
							"label" => t("Profile"),
							"file_extensions" => ["txt", "png", "jpg"],
						],
					],
				],
				"status" => [
					"type" => "checkbox",
					"label" => t("Active"),
					"value" => ($User ? $User->get("status") : 1),
				],
				"actions" => $this->defaultActions(),
			],
		];
		return $structure;
	}

};