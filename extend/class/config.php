<?php
class Config extends Core_Config {
	
	public function __construct() {
		parent::__construct();
		$this->database = [
			"user" => "localuser",
			"pass" => "hallonsaft",
			"db" => "qf_db",
			"host" => "localhost",
		];
		$this->debug = true;
	}

};