<?php
class Core_Config {
	
	protected $database;
	protected $subdomain, $https;
	protected $menu = Array();

	public function __construct() {
		$this->menu = Array(
			"admin" => Array(
				"body_class" => "admin-menu",
				"links" => Array(
					"home" => Array(
						"title" => "Home",
						"href" => "",
					),
				),
			),
		);
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


};