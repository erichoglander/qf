<?php
class l10nStringEdit_Form_Core extends Form {
  
  protected function structure() {
    $languages = $this->get("languages");
    $l10nString = $this->get("l10nString");
    $structure = [
      "name" => "l10n_string_edit",
      "items" => [
        "source" => [
          "type" => "textarea",
          "label" => $languages[$l10nString->get("lang")]->title,
          "value" => xss($l10nString->get("string")),
          "attributes" => [
            "disabled" => true,
          ],
        ],
      ],
    ];
    $i = 0;
    foreach ($languages as $language) {
      if ($language->lang != $l10nString->get("lang")) {
        $structure["items"][$language->lang] = [
          "type" => "textarea",
          "label" => $language->title,
          "value" => ($l10nString->translation($language->lang) ? $l10nString->translation($language->lang)->safe("string") : null),
          "focus" => ($i == 0),
          "filter" => "trim",
        ];
        $i++;
      }
    }
    $structure["items"]["actions"] = $this->defaultActions();
    return $structure;
  }

}