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
            "code" => t("Code"),
          ],
          "value" => ["import", "manual", "code"]
        ],
        "language" => [
          "type" => "checkboxes",
          "label" => t("Languages"),
          "options"=> $languagesop,
          "value" => $languagesval,
          "required" => true,
        ],
        "format" => [
          "type" => "select",
          "label" => t("Format"),
          "options" => [
            "json_pretty" => "JSON",
            "json_min" => "JSON (minified)",
            "xml" => "XML",
          ],
        ],
        "actions" => $this->defaultActions(t("Export")),
      ],
    ];
  }

}