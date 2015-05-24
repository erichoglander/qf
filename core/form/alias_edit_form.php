<?php
class AliasEdit_Form_Core extends Form {
	
	public function validate($values) {
		$Alias = $this->get("Alias");
		$row = $this->Db->getRow("SELECT * FROM `alias` WHERE alias = :alias", [":alias" => $values["alias"]]);
		if ($row && (!$Alias || $row->id != $Alias->id())) {
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
					"value" => ($Alias ? $Alias->get("path") : null),
					"focus" => true,
				],
				"alias" => [
					"type" => "text",
					"label" => t("Alias"),
					"required" => true,
					"value" => ($Alias ? $Alias->get("alias") : null),
				],
				"actions" => $this->defaultActions(),
			]
		];
	}

}