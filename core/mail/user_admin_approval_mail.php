<?php
class UserAdminApproval_Mail_Core extends MailMessage {
  
  public function prepare($vars = []) {
    $this->Mail->subject = "Account registration approval";
    $this->Mail->message = 
      '<p>A new account registration has come in on '.$this->Config->getSiteName().'</p>'.
      '<p>To activate their account, visit this link and set to active: <a href="'.$vars["link"].'">'.$vars["link"].'</a></p>';
  }

}