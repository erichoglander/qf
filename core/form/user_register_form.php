<?php
class UserRegister_Form_Core extends Form {

  public function validate($values) {
    if ($values["pass"] != $values["pass_confirm"]) {
      $this->setError(t("Passwords mismatch"), "pass");
      return false;
    }
    if (array_key_exists("name", $values)) {
      if ($values["name"] != strip_tags($values["name"])) {
        $this->setError(t("Username contains illegal characters"), "name");
        return false;
      }
      $U = newClass("User_Entity", $this->Db);
      $U->loadByName($values["name"]);
      if ($U->id()) {
        $this->setError(t("Username is already taken"), "name");
        return false;
      }
    }
    else {
      $U = newClass("User_Entity", $this->Db);
      $U->loadByName($values["email"]);
      if ($U->id()) {
        $this->setError(t("E-mail is already taken"), "email");
        return false;
      }
    }
    $U = newClass("User_Entity", $this->Db);
    $U->loadByEmail($values["email"]);
    if ($U->id()) {
      $this->setError(t("E-mail is already taken"), "email");
      return false;
    }
    return true;
  }
  
  
  protected function structure() {
    return [
      "name" => "user_register",
      "items" => [
        "name" => [
          "type" => "text",
          "label" => t("Username"),
          "icon" => "user",
          "required" => true,
        ],
        "email" => [
          "type" => "email",
          "label" => t("E-mail"),
          "required" => true,
        ],
        "pass" => [
          "type" => "password",
          "label" => t("Password"),
          "icon" => "lock",
          "required" => true,
        ],
        "pass_confirm" => [
          "type" => "password",
          "label" => t("Confirm password"),
          "icon" => "lock",
          "required" => true,
        ],
        "actions" => [
          "type" => "actions",
          "items" => [
            "submit" => [
              "type" => "submit",
              "value" => t("Register"),
            ],
          ],
        ],
      ],
    ];
  }

};