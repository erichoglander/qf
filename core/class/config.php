<?php
class Config_Core {
	
	protected $site_name;
	protected $database;
	protected $subdomain, $https;
	protected $menus = [];
	protected $debug = false;
	protected $user_registration = "closed";
	protected $libraries = ["FontAwesome"];


	public function __construct() {
		$this->site_name = BASE_DOMAIN;
		$this->menus = [
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
				],
			],
		];
	}

	public function getSiteName() {
		return $this->site_name;
	}
	public function getDatabase() {
		return $this->database;
	}
	public function getSubdomain() {
		return $this->subdomain;
	}
	public function getHttps() {
		return $this->https;
	}
	public function getMenus() {
		return $this->menus;
	}
	public function getDebug() {
		return $this->debug;
	}
	public function getUserRegistration() {
		return $this->user_registration;
	}
	public function getLibraries() {
		return $this->libraries;
	}

};