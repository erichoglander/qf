<?php
class l10nStringExport_Form_Core extends Form {
	
	protected function structure() {
		return [
			"name" => "l10n_string_export_form",
			"items" => [
				"input_type" => [
					"type" => "checkboxes",
					"label" => t("Input types"),
					"options" => [
						"code" => t("Code"),
						"import" => t("Imported"),
						"manual" => t("Manual"),
					],
					"value" => ["code", "import", "manual"]
				],
				"actions" => $this->defaultActions(t("Export")),
			],
		];
	}

}