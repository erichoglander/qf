<?php
class Log_Entity_Core extends Entity {

	protected function onLoad() {
		$this->set("User", $this->getEntity("User", $this->get("user_id")));
		return true;
	}
	
	protected function schema() {
		return [
			"table" => "log",
			"fields" => [
				"user_id" => [
					"type" => "uint",
					"default" => 0,
				],
				"created" => [
					"type" => "uint",
					"default" => 0,
				],
				"type" => [
					"type" => "enum",
					"values" => ["info", "success", "warning", "error"],
				],
				"category" => [
					"type" => "varchar",
				],
				"text" => [
					"type" => "text",
				],
				"data" => [
					"type" => "blob",
					"serialize" => true,
				],
				"ip" => [
					"type" => "varchar",
				],
			],
		];
	}

}