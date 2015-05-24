<?php
class AliasEdit_Form_Core extends Form {
	
	public function validate($values) {
		$row = $this->Db->getRow("SELECT * FROM `alias` WHERE alias = :alias", [":alias" => $values["alias"]]);
		if ($row && $row->id != $this->get("id")) {
			$this->setError(t("Alias already exists"), "alias");
			return false;
		}
		return true;
	}

	public function structure() {
		return [
			"name" => "alias_edit",
			"items" => [
				"path" => [
					"type" => "text",
					"label" => t("Path"),
					"required" => true,
					"value" => $this->get("path"),
					"focus" => true,
				],
				"alias" => [
					"type" => "text",
					"label" => t("Alias"),
					"required" => true,
					"value" => $this->get("alias"),
				],
				"actions" => $this->defaultActions(),
			]
		];
	}

}