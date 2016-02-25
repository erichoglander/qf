<?php
class UserResendEmailConfirmation_Form_Core extends Form {
  
  public function validate($values) {
    $User = $this->get("User");
    if ($User->get("email") != $values["email"]) {
      $U = $this->getEntity("User");
      if ($U->loadByEmail($values["email"]) || $U->loadByName($values["email"])) {
        $this->setError(t("E-mail is already taken"), "email");
        return false;
      }
    }
    return true;
  }
  
  protected function structure() {
    $User = $this->get("User");
    $form = [
      "name" => "user_resend_email_confirmation",
      "items" => [
        "email" => [
          "type" => "email",
          "label" => t("E-mail"),
          "value" => $User->get("email"),
          "required" => true,
        ],
        "submit" => [
          "type" => "submit",
          "value" => t("Send"),
        ],
      ],
    ];
    return $form;
  }
  
}