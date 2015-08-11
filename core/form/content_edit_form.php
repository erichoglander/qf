<?php
class ContentEdit_Form_Core extends Form {
	
	protected function structure() {
		$Content = $this->get("Content");
		$structure = [
			"name" => "content_edit",
			"items" => [
				"title" => [
					"type" => "text",
					"label" => t("Title"),
					"required" => true,
					"value" => ($Content ? $Content->get("title") : null),
					"filter" => ["strip_tags", "trim"],
					"focus" => !$Content,
				],
			]
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
		if ($this->get("config")) {
			$structure["items"]["fields"] = [
				"type" => "fieldset",
				"label" => t("Field config"),
				"multiple" => true,
				"add_button" => t("Add field"),
				"delete_button" => t("Delete field"),
				"value" => ($Content ? $Content->fields() : null),
				"items" => [
					"type" => [
						"type" => "select",
						"label" => t("Field type"),
						"options" => [
							"text" => t("Text"),
							"textarea" => t("Textarea"),
							"editor" => t("Editor"),
							"image" => t("Image"),
						],
						"required" => true
					],
					"title" => [
						"type" => "text",
						"label" => t("Title"),
						"required" => true,
					],
					"description" => [
						"type" => "text",
						"label" => t("Description"),
					],
				]
			];
		}
		$structure["items"]["actions"] = $this->defaultActions();
		return $structure;
	}

}