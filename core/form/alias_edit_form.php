<?php
class AliasEdit_Form_Core extends Form {
  
  public function validate($values = []) {
    $Alias = $this->get("Alias");
    $row = $this->Db->getRow("
        SELECT * FROM `alias` 
        WHERE 
          alias = :alias &&
          (lang IS NULL || lang = :lang || :lang = '')", 
        [  ":alias" => $values["alias"],
          ":lang" => $values["lang"]]);
    if ($row && (!$Alias || $row->id != $Alias->id())) {
      $this->setError(t("Alias already exists"), "alias");
      return false;
    }
    return true;
  }

  public function structure() {
    $Alias = $this->get("Alias");
    
    // Languages
    $rows = $this->Db->getRows("SELECT * FROM `language` ORDER BY title ASC");
    $lang_op = [];
    foreach ($rows as $row)
      $lang_op[$row->lang] = $row->title;
    
    return [
      "name" => "alias_edit",
      "items" => [
        "path" => [
          "type" => "text",
          "label" => t("Path"),
          "required" => true,
          "value" => ($Alias ? $Alias->get("path") : null),
          "focus" => true,
        ],
        "alias" => [
          "type" => "text",
          "label" => t("Alias"),
          "required" => true,
          "value" => ($Alias ? $Alias->get("alias") : null),
        ],
        "lang" => [
          "type" => "select",
          "label" => t("Language"),
          "options" => $lang_op,
          "empty_option" => t("- All -"),
          "value" => ($Alias ? $Alias->get("lang") : null),
        ],
        "status" => [
          "type" => "checkbox",
          "label" => t("Active"),
          "value" => ($Alias ? $Alias->get("status") : true),
        ],
        "actions" => $this->defaultActions(),
      ]
    ];
  }

}