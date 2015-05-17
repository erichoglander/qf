<?php
class Core_Config {
	
	protected $database;
	protected $subdomain, $https;
	protected $menu = [];
	protected $debug = false;

	public function __construct() {
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

};