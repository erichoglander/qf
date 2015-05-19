<?php
class UserReset_Form extends Form {

	public function validated() {
		$values = $this->values();
		$row = $this->Db->getRow("SELECT id FROM `user` WHERE email = :email", [":email" => $values["email"]]);
		if (!$row) {
			$this->setError(t("There is no registered user with that e-mail address"));
			return false;
		}
		return true;
	}
	
	protected function structure() {
		return [
			"name" => "user_reset",
			"items" => [
				"email" => [
					"type" => "email",
					"label" => t("E-mail address"),
					"required" => true,
				],
				"actions" => [
					"type" => "actions",
					"items" => [
						"submit" => [
							"type" => "submit",
							"value" => t("Submit"),
						],
						"login" => [
							"type" => "button",
							"value" => t("Nevermind"),
							"attributes" => [
								"onclick" => "window.location.href = '/user'",
							],
						],
					],
				],
			]
		];
	}

};