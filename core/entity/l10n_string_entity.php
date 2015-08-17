<?php
class l10nString_Entity_Core extends l10n_Entity {

	public function loadFromString($string, $lang, $sid = null) {
		$row = $this->Db->getRow("
				SELECT id FROM `l10n_string`
				WHERE 
					lang = :lang && 
					string = :string",
				[	":lang" => $lang,
					":string" => $string]);
		if ($row)
			return $this->load($row->id);
		else
			return false;
	}

	
	protected function schema() {
		$schema = parent::schema();
		$schema["table"] = "l10n_string";
		$schema["fields"]["string"] = [
			"type" => "varchar",
		];
		$schema["fields"]["input_type"] = [
			"type" => "enum",
			"values" => ["import", "manual", "code"],
			"default" => "code",
		];
		return $schema;
	}

}