<?php
class ContentEdit_Form_Core extends Form {
	
	protected function structure() {
		$Content = $this->get("Content");
		$structure = [
			"name" => "content_edit",
			"items" => [],
		];
		if ($Content) {
			foreach ($Content->get("config")["fields"] as $i => $field) {
				$structure["items"]["field_".$i] = [
					"type" => $field["type"],
					"label" => $field["title"],
					"value" => $Content->get("data")[$i],
				];
			}
		}
		$structure["items"]["actions"] = $this->defaultActions();
		return $structure;
	}

}