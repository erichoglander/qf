<?php
class Config_Core {
	
	protected $site_name;
	protected $database;
	protected $subdomain, $https;
	protected $menu = [];
	protected $debug = false;
	protected $user_registration = "closed";
	protected $libraries = ["FontAwesome"];


	public function __construct() {
		$this->site_name = BASE_DOMAIN;
		$this->menu = [
			"admin" => [
				"body_class" => "admin-menu",
				"links" => [
					"home" => [
						"title" => "Home",
						"href" => "",
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
	public function getMenu() {
		return $this->menu;
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