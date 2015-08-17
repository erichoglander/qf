<?php
class l10nStringExport_Form_Core extends Form {
	
	protected function structure() {
		$languages = $this->get("languages");
		$languagesop = [];
		$languagesval = [];
		foreach ($languages as $lang => $language) {
			$languagesop[$lang] = $language->title;
			$languagesval[] = $lang;
		}
		return [
			"name" => "l10n_string_export_form",
			"items" => [
				"input_type" => [
					"type" => "checkboxes",
					"label" => t("Input types"),
					"options" => [
						"import" => t("Imported"),
						"manual" => t("Manual"),
					],
					"value" => ["import", "manual"]
				],
				"language" => [
					"type" => "checkboxes",
					"label" => t("Languages"),
					"options"=> $languagesop,
					"value" => $languagesval,
					"required" => true,
				],
				"min" => [
					"type" => "checkbox",
					"label" => t("Minimized"),
					"value" => true,
				],
				"actions" => $this->defaultActions(t("Export")),
			],
		];
	}

}