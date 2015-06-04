<?php
class TranslationScan_Form_Core extends Form {
	
	protected function structure() {
		return [
			"name" => "translation_scan",
			"items" => [
				"parts" => [
					"type" => "checkboxes",
					"label" => t("Parts"),
					"options" => [
						"core" => t("Core"),
						"extend" => t("Extended"),
					],
					"value" => ["core", "extend"],
				],
				"action" => [
					"type" => "select",
					"label" => t("Action"),
					"options" => [
						"info" => t("View information"),
						"add" => t("Add strings"),
					],
					"value" => "add",
					"required" => true,
				],
				"actions" => $this->defaultActions(t("Scan")),
			],
		];
	}

}