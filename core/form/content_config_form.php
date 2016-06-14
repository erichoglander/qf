<?php
class ContentConfig_Form_Core extends Form {
  
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
        "l10n" => [
          "type" => "checkbox",
          "label" => t("Localization"),
          "value" => ($Content ? $Content->l10n() : null),
        ],
        "fields" => [
          "type" => "fieldset",
          "label" => t("Field config"),
          "multiple" => true,
          "add_button" => t("Add field"),
          "delete_button" => t("Delete field"),
          "value" => ($Content ? $Content->fields() : null),
					"required" => true,
          "items" => [
            "type" => [
              "type" => "select",
              "label" => t("Field type"),
              "options" => [
                "text" => t("Text"),
                "textarea" => t("Textarea"),
                "editor" => t("Editor"),
                "tinymce" => "TinyMCE",
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
        ],
        "actions" => $this->defaultActions(),
      ]
    ];
    return $structure;
  }

}