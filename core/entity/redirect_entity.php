<?php
class Redirect_Entity_Core extends Entity  {
	
	protected function schema() {
		$schema = parent::schema();
		$schema["table"] = "redirect";
		$schema["fields"]["source"] = [
			"type" => "varchar",
		];
		$schema["fields"]["target"] = [
			"type" => "varchar",
		];
		$schema["fields"]["code"] = [
			"type" => "enum",
			"values" => ["301", "302"],
		];
		return $schema;
	}

};