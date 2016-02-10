<?php
class ContentEdit_Form_Core extends Form {
	
	protected function structure() {
		$Content = $this->get("Content");
		$languages = $this->Db->getRows("
				SELECT * FROM `language`
				WHERE status = 1");
		$structure = [
			"name" => "content_edit",
			"items" => [],
		];
		if ($Content) {
			$items = [];
			if ($Content->l10n()) {
				foreach ($languages as $language) {
					$data = $Content->translate("data", $language->lang);
					foreach ($Content->get("config")["fields"] as $i => $field) {
						$items["field_".$i] = [
							"type" => $field["type"],
							"label" => $field["title"],
							"value" => (isset($data[$i]) ? $data[$i] : null),
						];
					}
					$structure["items"][$language->lang] = [
						"type" => "fieldset",
						"collapsible" => true,
						"collapsed" => true,
						"label" => $language->title,
						"items" => $items,
					];
				}
			}
			else {
				foreach ($Content->get("config")["fields"] as $i => $field) {
					$items["field_".$i] = [
						"type" => $field["type"],
						"label" => $field["title"],
						"value" => $Content->get("data")[$i],
					];
				}
				$structure["items"] = $items;
			}
		}
		$structure["items"]["actions"] = $this->defaultActions();
		return $structure;
	}

}