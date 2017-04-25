<?php
class RedirectEdit_Form_Core extends Form {
  
  public function validate($values) {
    $Redirect = $this->get("Redirect");
    $row = $this->Db->getRow("
        SELECT * FROM `redirect` 
        WHERE 
          source = :source &&
          (lang IS NULL || lang = :lang || :lang = '')", 
        [  ":source" => $values["source"],
          ":lang" => $values["lang"]]);
    if ($row && (!$Redirect || $row->id != $Redirect->id())) {
      $this->setError(t("Redirect source already exists"), "source");
      return false;
    }
    return true;
  }

  public function structure() {
    $Redirect = $this->get("Redirect");
    
    // Languages
    $rows = $this->Db->getRows("SELECT * FROM `language` ORDER BY title ASC");
    $lang_op = [];
    foreach ($rows as $row)
      $lang_op[$row->lang] = $row->title;
      
    return [
      "name" => "redirect_edit",
      "items" => [
        "source" => [
          "type" => "text",
          "label" => t("Source"),
          "required" => true,
          "value" => ($Redirect ? $Redirect->safe("source") : null),
          "focus" => true,
        ],
        "target" => [
          "type" => "text",
          "label" => t("Target"),
          "description" => xss(t("Enter <front> for the front page")),
          "required" => true,
          "value" => ($Redirect ? $Redirect->safe("target") : null),
        ],
        "code" => [
          "type" => "select",
          "label" => t("Code"),
          "options" => [
            "301" => t("301 - Moved Permanently"),
            "302" => t("302 - Found"),
            "303" => t("303 - See Other"),
            "307" => t("307 - Temporary Redirect"),
          ],
          "required" => true,
          "value" => ($Redirect ? $Redirect->get("code") : "301"),
        ],
        "lang" => [
          "type" => "select",
          "label" => t("Language"),
          "options" => $lang_op,
          "empty_option" => t("- All -"),
          "value" => ($Redirect ? $Redirect->get("lang") : null),
        ],
        "type" => [
          "type" => "select",
          "label" => t("Type"),
          "options" => [
            "normal" => t("Normal"),
            "regexp" => t("Regular expression"),
          ],
          "empty_option" => Null,
          "required" => true,
          "value" => ($Redirect ? $Redirect->get("type") : "normal"),
        ],
        "status" => [
          "type" => "checkbox",
          "label" => t("Active"),
          "value" => ($Redirect ? $Redirect->get("status") : true),
        ],
        "actions" => $this->defaultActions(),
      ]
    ];
  }

}