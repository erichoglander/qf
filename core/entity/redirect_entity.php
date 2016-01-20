<?php
/**
 * Contains the redirect entity
 */
/**
 * Redirect entity
 *
 * Used to handle stored http redirects
 * @author Eric Höglander
 */
class Redirect_Entity_Core extends Entity  {
	
	/**
	 * Url of the target
	 * @see url()
	 * @return string
	 */
	public function url() {
		return url($this->get("target"));
	}
	
	/**
	 * Uri of the target
	 * @see uri()
	 * @return string
	 */
	public function uri() {
		return uri($this->get("target"));
	}
	
	/**
	 * Load redirect by source uri
	 * @param  string $source
	 * @return bool
	 */
	public function loadBySource($source) {
		$row = $this->Db->getRow("
				SELECT * FROM `redirect`
				WHERE 
					status = 1 &&
					source = :source",
				[":source" => $source]);
		if ($row) {
			$this->load($row->id);
			return true;
		}
		return false;
	}
	
	
	/**
	 * Database schema
	 * @return array
	 */
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
			"values" => ["301", "302", "303", "307"],
			"default" => "301",
		];
		return $schema;
	}

};