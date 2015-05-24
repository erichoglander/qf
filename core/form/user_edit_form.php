<?php
class UserEdit_Form_Core extends Form {

	public function validate($values) {
		if ($values['password'] != $values["password_confirm"]) {
			$this->setError(t("Passwords mismatch"), "password");
			return false;
		}
		$row = $this->Db->getRow("SELECT id FROM `user` WHERE name = :name", [":name" => $values["name"]]);
		if ($row && $row->id != $this->get("id")) {
			$this->setError(t("Username unavailable"), "name");
			return false;
		}
		$row = $this->Db->getRow("SELECT id FROM `user` WHERE email = :email", [":email" => $values["email"]]);
		if ($row && $row->id != $this->get("id")) {
			$this->setError(t("E-mail unavailable"), "email");
			return false;
		}
		return true;
	}
	
	public function structure() {
		$role_rows = $this->Db->getRows("SELECT * FROM `role` ORDER BY title ASC");
		$roles = [];
		foreach ($role_rows as $row)
			$roles[$row->id] = t($row->title);
		$structure = [
			"name" => "user_edit",
			"items" => [
				"name" => [
					"type" => "text",
					"label" => t("Username"),
					"value" => $this->get("name"),
					"focus" => true,
					"required" => true,
				],
				"email" => [
					"type" => "email",
					"label" => t("Email"),
					"value" => $this->get("email"),
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
				"roles" => [
					"type" => "checkboxes",
					"label" => t("Roles"),
					"options" => $roles,
					"value" => $this->get("roles"),
				],
				"status" => [
					"type" => "checkbox",
					"label" => t("Active"),
					"value" => $this->get("status", 1),
				],
				"actions" => $this->defaultActions(),
			],
		];
		return $structure;
	}

};