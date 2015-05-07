<?php
class Entity {
	
	protected $Db;

	public function __construct(&$Db, $id = null) {
		$this->Db = $Db;
		if ($id)
			$this->load($id);
	}

	public function load($id) {

	}


	protected function schema() {
		return [
			"table" => "",
			"fields" => [
				"status" => [
					"type" => "uint",
				],
				"created" => [
					"type" => "uint",
				],
				"updated" => [
					"type" => "uint",
				],
			],
		];
	}

};