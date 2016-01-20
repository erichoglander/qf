<?php
/**
 * Contains the alias entity
 */
/**
 * Alias entity
 * @author Eric Höglander
 */
class Alias_Entity_Core extends Entity  {
	
	/**
	 * Database schema
	 * @return array
	 */
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