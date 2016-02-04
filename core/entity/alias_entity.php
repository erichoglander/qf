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
	 * Language object
	 * @var object
	 */
	protected $language;
	
	
	/**
	 * Get language object, if it exists
	 * @return object
	 */
	public function language() {
		if ($this->get("lang") && $this->language === null) {
			$this->language = $this->Db->getRow("
					SELECT * FROM `language`
					WHERE lang = :lang",
					[":lang" => $this->get("lang")]);
		}
		return $this->language;
	}
	
	
	/**
	 * Database schema
	 * @return array
	 */
	protected function schema() {
		$schema = parent::schema();
		$schema["table"] = "alias";
		$schema["fields"]["lang"] = [
			"type" => "varchar",
		];
		$schema["fields"]["path"] = [
			"type" => "varchar",
		];
		$schema["fields"]["alias"] = [
			"type" => "varchar",
		];
		return $schema;
	}

};