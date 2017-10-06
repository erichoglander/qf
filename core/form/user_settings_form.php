<?php
class UserSettings_Form_Core extends Form {

  public function validate($values = []) {
    $User = $this->get("User");
    if ($values["pass"] != $values["pass_confirm"]) {
      $this->setError(t("Passwords mismatch"), "pass");
      return false;
    }
    $U = $this->getEntity("User");
    $U->loadByEmail($values["email"]);
    if ($U->id() && $U->id() != $User->id()) {
      $this->setError(t("E-mail unavailable"), "email");
      return false;
    }
    if ($User->get("name") == $User->get("email")) {
      $U = $this->getEntity("User");
      $U->loadByName($values["email"]);
      if ($U->id() && $U->id() != $User->id()) {
        $this->setError(t("E-mail unavailable"), "email");
        return false;
      }
    }
    return true;
  }
  
  
  protected function structure() {
    $User = $this->get("User");
    $structure = [
      "name" => "user_settings",
      "items" => [
        "email" => [
          "type" => "email",
          "label" => t("Email"),
          "value" => $User->get("email"),
          "required" => true,
        ],
      ],
    ];
    if ($this->Config->getLanguageDetection() == "user") {
      $langs = $this->getModel("l10n")->getActiveLanguages();
      $lang_op = [];
      foreach ($langs as $lang)
        $lang_op[$lang->lang] = xss($lang->title);
      $structure["items"]+= [
        "lang" => [
          "type" => "select",
          "label" => t("Language"),
          "empty_option" => t("Default"),
          "options" => $lang_op,
          "value" => $User->get("lang"),
        ],
      ];
    }
    $structure["items"]+= [
      "pass" => [
        "type" => "password",
        "label" => t("Change password"),
        "icon" => "lock",
      ],
      "pass_confirm" => [
        "type" => "password",
        "label" => t("Confirm password"),
        "icon" => "lock",
      ],
      "actions" => $this->defaultActions(),
    ];
    return $structure;
  }

};