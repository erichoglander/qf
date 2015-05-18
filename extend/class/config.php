<?php
class Config extends Config_Core {
	
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