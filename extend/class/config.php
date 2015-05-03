<?php
class Config extends Core_Config {
	
	public function __construct() {
		parent::__construct();
		$this->database = Array(
			"user" => "localuser",
			"pass" => "hallonsaft",
			"db" => "test_db",
			"host" => "localhost",
		);
	}

};