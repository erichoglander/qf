<?php
class Alias_Entity_Core extends Entity  {
	
	protected function schema() {
		$schema = parent::schema();
		$schema["table"] = "alias";
		$schema["fields"]["path"] = [
			"type" => "varchar",
		];
		$schema["fields"]["alias"] = [
			"type" => "varchar",
		];
		return $schema;
	}

};