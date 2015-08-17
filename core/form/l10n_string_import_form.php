<?php
class l10nStringImport_Form_Core extends Form {
	
	protected function structure() {
		return [
			"name" => "l10n_string_import",
			"items" => [
				"file" => [
					"type" => "file",
					"label" => t("Translation file"),
					"file_extensions" => ["json"],
					"required" => true,
				],
				"actions" => $this->defaultActions("Import"),
			],
		];
	}

}