<?php
class UserLogin_Form extends Form {
	
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