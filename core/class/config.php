<?php
class Config_Core {

	public function getSiteName() {
		return BASE_DOMAIN;
	}
	public function getDatabase() {
		return null;
	}
	public function getSubdomain() {
		return null;
	}
	public function getHttps() {
		return false;
	}
	public function getMenus() {
		$menu = [
			"admin" => [
				"body_class" => "admin-menu",
				"acl" => "menuAdmin",
				"links" => [
					"home" => [
						"faicon" => "home",
						"href" => "",
					],
					"logout" => [
						"faicon" => "sign-out",
						"href" => "user/logout",
						"return" => true,
					],
					"settings" => [
						"faicon" => "cog",
						"href" => "user/settings",
					],
					"user" => [
						"title" => "Users",
						"href" => "user/list",
						"links" => [
							"user-add" => [
								"title" => "Add user",
								"href" => "user/add",
							],
						],
					],
					"alias" => [
						"title" => "Aliases",
						"href" => "alias/list",
						"links" => [
							"alias-add" => [
								"title" => "Add alias",
								"href" => "alias/add",
							],
						],
					],
					"redirect" => [
						"title" => "Redirects",
						"href" => "redirect/list",
						"links" => [
							"redirect-add" => [
								"title" => "Add redirect",
								"href" => "redirect/add",
							],
						],
					],
					"system" => [
						"title" => "System",
						"links" => [
							"logs" => [
								"title" => "Logs",
								"href" => "log/list",
							],
							"cache-clear" => [
								"title" => "Clear cache",
								"href" => "cache/clear",
								"return" => true,
							],
							"cron-run" => [
								"title" => "Run cron",
								"href" => "cron",
								"return" => true,
							],
						],
					],
				],
			],
		];
		return $menu;
	}
	public function getDebug() {
		return false;
	}
	public function getUserRegistration() {
		return "closed";
	}
	public function getLibraries() {
		return ["FontAwesome", "CKEditor"];
	}
	public function getAutomaticCron() {
		return true;
	}

};