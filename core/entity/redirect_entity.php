<?php
class Redirect_Entity_Core extends Entity  {
	
	public function url() {
		return url($this->get("target"));
	}
	
	public function uri() {
		return uri($this->get("target"));
	}
	
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