<?php
class UserEdit_Form_Core extends Form {

  public function validate($values) {
    $User = $this->get("User");
    if ($values["pass"] != $values["pass_confirm"]) {
      $this->setError(t("Passwords mismatch"), "pass");
      return false;
    }
    $U = $this->getEntity("User");
    $U->loadByName($values["name"]);
    if ($U->id() && (!$User || $U->id() != $User->id())) {
      $this->setError(t("Username unavailable"), "name");
      return false;
    }
    $U = $this->getEntity("User");
    $U->loadByEmail($values["email"]);
    if ($U->id() && (!$User || $U->id() != $User->id())) {
      $this->setError(t("E-mail unavailable"), "email");
      return false;
    }
    return true;
  }
  
  
  protected function structure() {
    $User = $this->get("User");
    $role_rows = $this->Db->getRows("SELECT * FROM `role` ORDER BY title ASC");
    $rolesop = [];
    foreach ($role_rows as $row)
      $rolesop[$row->id] = t($row->title);
    $roles = [];
    if ($User) {
      foreach ($User->roles() as $role)
        $roles[] = $role->id;
    }
    $structure = [
      "name" => "user_edit",
      "items" => [
        "name" => [
          "type" => "text",
          "label" => t("Username"),
          "value" => ($User ? $User->get("name") : null),
          "validation" => "username",
          "icon" => "user",
          "focus" => ($User ? false : true),
          "required" => true,
        ],
        "email" => [
          "type" => "email",
          "label" => t("Email"),
          "value" => ($User ? $User->get("email") : null),
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
          "value" => ($User ? $User->get("lang") : null),
        ],
      ];
    }
    $structure["items"]+= [
      "pass" => [
        "type" => "password",
        "label" => t("Password"),
        "icon" => "lock",
        "generator" => true,
        "generator_copy" => "pass_confirm",
      ],
      "pass_confirm" => [
        "type" => "password",
        "label" => t("Confirm password"),
        "icon" => "lock",
      ],
      "roles" => [
        "type" => "checkboxes",
        "label" => t("Roles"),
        "options" => $rolesop,
        "value" => $roles,
      ],
      "status" => [
        "type" => "checkbox",
        "label" => t("Active"),
        "value" => ($User ? $User->get("status") : 1),
      ],
      "actions" => $this->defaultActions(),
    ];
    return $structure;
  }

};