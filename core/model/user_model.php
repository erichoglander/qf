<?php
/**
 * Contains the user model
 */
/**
 * User model
 * @author Eric Höglander
 */
class User_Model_Core extends Model {

  /**
   * Register a new user
   * @see    registerFinalize
   * @param  array $values
   */
  public function register($values) {
    $User = $this->getEntity("User");
    if (!array_key_exists("name", $values))
      $values["name"] = $values["email"];
    foreach ($values as $key => $value)
      $User->set($key, $value);
    $User->set("status", 1);
    return $this->registerFinalize($User, $values);
  }
  
  /**
   * Finalize user registration
   * @see    \Config_Core::getUserRegistration()
   * @param  \User_Entity_Core $User
   * @param  array             $values
   * @return string Key of the type of registration
   */
  public function registerFinalize($User, $values) {
    $registration = $this->Config->getUserRegistration();
    $re = null;
    if ($registration == "email_confirmation") {
      if ($this->sendEmailConfirmation($User)) {
        $User->login();
        $re = "email_confirmation";
      }
    }
    else if ($registration == "admin_approval") {
      $User->set("status", 0);
      // TODO: Approval mail
      if ($User->save()) 
        $re = "admin_approval";
    }
    else {
      if ($User->save()) {
        $User->login();
        $re = "register_login";
      }
    }
    if ($re)
      addlog("user", "New user registration ".$User->get("name"));
    return $re;
  }

  /**
   * Send an e-mail to a user to confirm their e-mail address
   * @see    \User_Entity_Core::generateEmailConfirmationLink()
   * @param  \User_Entity_Core $User
   * @return bool
   */
  public function sendEmailConfirmation($User) {
    $code = $User->generateEmailConfirmationLink();
    if (!$User->save())
      return false;
    $link = SITE_URL.url("user/confirm-email/".$User->id()."/".$code);
    return $this->sendMail("UserEmailConfirmation", $User->get("email"), ["link" => $link, "User" => $User]);
  }
  
  /**
   * Resend an e-mail to a user to confirm their e-mail address
   * @see    \User_Entity_Core::generateEmailConfirmationLink()
   * @see    \UserEmailConfirmation_Mail_Core
   * @param  \User_Entity_Core $User
   * @param  array             $values
   * @return bool
   */
  public function resendEmailConfirmation($User, $values) {
    if ($User->get("email") == $User->get("name"))
      $User->set("name", $values["email"]);
    $User->set("email", $values["email"]);
    return $this->sendEmailConfirmation($User);
  }
  
  /**
   * Send an e-mail to a user to reset their password
   * @see    \User_Entity_Core::generateResetLink()
   * @see    \UserReset_Mail_Core
   * @param  \User_Entity_Core $User
   * @return bool
   */
  public function reset($User) {
    $code = $User->generateResetLink();
    if (!$User->save())
      return false;
    $link = SITE_URL.url("user/change-password/".$User->id()."/".$code);
    return $this->sendMail("UserReset", $User->get("email"), ["link" => $link, "User" => $User]);
  }

  /**
   * Change user password
   * @param  \User_Entity_Core $User
   * @param  string            $password
   * @return bool
   */
  public function changePassword($User, $password) {
    $User->set("pass", $password);
    $User->set("reset", "");
    $User->set("reset_time", 0);
    return $User->save();
  }

  /**
   * Confirm a users e-mail address
   * @param  \User_Entity_Core $User
   * @return bool
   */
  public function confirmEmail($User) {
    $User->set("email_confirmation", "");
    $User->set("email_confirmation_time", 0);
    return $User->save();
  }
  
  /**
   * Sign in as specified user
   * 
   * Sets current user_id as superuser_id in current session
   * @param  \User_Entity_Core $User
   */
  public function signInAs($User) {
    $_SESSION["superuser_id"] = $this->User->id();
    $User->login();
  }
  
  /**
   * Sign back in as the previous user
   * @see    signInAs
   * @return \User_Entity_Core
   */
  public function signBack() {
    if (empty($_SESSION["superuser_id"]))
      return null;
    $User = $this->getEntity("User", $_SESSION["superuser_id"]);
    unset($_SESSION["superuser_id"]);
    if (!$User->id())
      return null;
    $User->login();
    return $User;
  }
  
  /**
   * Clear all failed login attempts
   */
  public function clearLoginAttempts() {
    $this->Db->delete("login_attempt");
    addlog("User", "Login attempts cleared", null, "success");
  }
  
  /**
   * Add a new user
   * @see    editUser
   * @return \User_Entity_Core
   */
  public function addUser($values) {
    $User = $this->getEntity("User");
    if ($this->editUser($User, $values))
      return $User;
    else
      return null;
  }
  
  /**
   * Save user
   * @param  \User_Entity_Core $User
   * $param  array             $values
   * @return bool
   */
  public function editUser($User, $values) {
    foreach ($values as $key => $value) {
      if (!is_array($value))
        $User->set($key, $value);
    }
    if ($User->id() == 1)
      $User->set("status", 1); # admin account cannot be deactivated
    if (!$User->save())
      return false;
    if (array_key_exists("roles", $values)) {
      $this->Db->delete("user_role", ["user_id" => $User->id()]);
      if (!empty($values["roles"])) {
        foreach ($values["roles"] as $role_id) 
          $this->Db->insert("user_role", ["user_id" => $User->id(), "role_id" => $role_id]);
      }
    }
    return true;
  }

  /**
   * Save user settings
   * @param  \User_Entity_Core $User
   * @param  array             $values
   * @return bool
   */
  public function saveSettings($User, $values) {
    $change_email = $values["email"] != $User->get("email");
    if ($change_email && $User->get("email") == $User->get("name") && !array_key_exists("name", $values))
      $values["name"] = $values["email"];
    foreach ($values as $key => $value) {
      if (!is_array($value))
        $User->set($key, $value);
    }
    if ($change_email && $this->Config->getUserRegistration() == "email_confirmation") {
      if ($this->sendEmailConfirmation($User))
        return "email_confirmation";
      else
        return false;
    }
    return $User->save();
  }

  /**
   * Delete user
   * @param  \User_Entity_Core $User
   * @return bool
   */
  public function deleteUser($User)  {
    return $User->delete();
  }

  /**
   * Get all users
   * @return array
   */
  public function getUsers() {
    $rows = $this->Db->getRows("SELECT id FROM `user` ORDER BY name ASC");
    $users = [];
    foreach ($rows as $row)
      $users[] = $this->getEntity("User", $row->id);
    return $users;
  }
  
  /**
   * Creates an sql-query for a search
   * @param  array $values Search parameters
   * @return array Contains sql-query and vars
   */
  public function listSearchQuery($values) {
    $sql = "SELECT id FROM `user`";
    $vars = [];
    if (!empty($values["q"])) {
      $sql.= " WHERE name LIKE :q || email LIKE :q";
      $vars[":q"] = "%".$values["q"]."%";
    }
    return [$sql, $vars];
  }
  /**
   * Number of users matching a search
   * @param  array $values Search parameters
   * @return int
   */
  public function listSearchNum($values = []) {
    list($sql, $vars) = $this->listSearchQuery($values);
    return $this->Db->numRows($sql, $vars);
  }
  /**
   * Search for users
   * @param  array $values Search parameters
   * @param  int   $start
   * @param  int   $stop
   * @return array
   */
  public function listSearch($values = [], $start = 0, $stop = 30) {
    $list = [];
    list($sql, $vars) = $this->listSearchQuery($values);
    $sql.= " LIMIT ".$start.", ".$stop;
    $rows = $this->Db->getRows($sql, $vars);
    foreach ($rows as $row)
      $list[] = $this->getEntity("User", $row->id);
    return $list;
  }
  
};