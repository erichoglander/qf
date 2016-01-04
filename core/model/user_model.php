<?php
class User_Model_Core extends Model {

	public function register($values) {
		$User = $this->getEntity("User");
		if (!array_key_exists("name", $values))
			$values["name"] = $values["email"];
		foreach ($values as $key => $value)
			$User->set($key, $value);
		$User->set("status", 1);
		return $this->registerFinalize($User, $values);
	}
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

	public function sendEmailConfirmation($User) {
		$code = $User->generateEmailConfirmationLink();
		if (!$User->save())
			return false;
		$link = SITE_URL.url("user/confirm-email/".$User->id()."/".$code);
		return $this->sendMail("UserEmailConfirmation", $User->get("email"), ["link" => $link]);
	}
	
	public function resendEmailConfirmation($User, $values) {
		if ($User->get("email") == $User->get("name"))
			$User->set("name", $values["email"]);
		$User->set("email", $values["email"]);
		return $this->sendEmailConfirmation($User);
	}

	public function reset($User) {
		$code = $User->generateResetLink();
		if (!$User->save())
			return false;
		$link = SITE_URL.url("user/change-password/".$User->id()."/".$code);
		return $this->sendMail("UserReset", $User->get("email"), ["link" => $link]);
	}

	public function changePassword($User, $password) {
		$User->set("pass", $password);
		$User->set("reset", "");
		$User->set("reset_time", 0);
		return $User->save();
	}

	public function confirmEmail($User) {
		$User->set("email_confirmation", "");
		$User->set("email_confirmation_time", 0);
		return $User->save();
	}
	
	public function signInAs($User) {
		$_SESSION["superuser_id"] = $this->User->id();
		$User->login();
	}
	
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
	
	public function clearLoginAttempts() {
		$this->Db->delete("login_attempt");
		addlog("User", "Login attempts cleared", null, "success");
	}
	
	public function addUser($values) {
		$User = $this->getEntity("User");
		if ($this->editUser($User, $values))
			return $User;
		else
			return null;
	}
	
	public function editUser($User, $values) {
		if (!empty($values["roles"])) {
			foreach ($values["roles"] as &$role) {
				if (!is_object($role))
					$role = (object) ["id" => $role];
			}
		}
		foreach ($values as $key => $value)
			$User->set($key, $value);
		if ($User->id() == 1)
			$User->set("status", 1); # admin account cannot be deactivated
		return $User->save();
	}

	public function saveSettings($User, $values) {
		$change_email = $values["email"] != $User->get("email");
		if ($change_email && $User->get("email") == $User->get("name") && !array_key_exists("name", $values))
			$values["name"] = $values["email"];
		foreach ($values as $key => $value)
			$User->set($key, $value);
		if ($change_email && $this->Config->getUserRegistration() == "email_confirmation") {
			if ($this->sendEmailConfirmation($User))
				return "email_confirmation";
			else
				return false;
		}
		return $User->save();
	}

	public function deleteUser($User)  {
		return $User->delete();
	}

	public function getUsers($vars = []) {
		$vars+= [
			"sort" => "name",
			"order" => "asc",
		];
		$rows = $this->Db->getRows("SELECT id FROM `user` ORDER BY ".$vars["sort"]." ".$vars["order"]);
		$users = [];
		foreach ($rows as $row)
			$users[] = $this->getEntity("User", $row->id);
		return $users;
	}
	
	public function listSearchQuery($values) {
		$sql = "SELECT id FROM `user`";
		$vars = [];
		if (!empty($values["q"])) {
			$sql.= " WHERE name LIKE :q || email LIKE :q";
			$vars[":q"] = "%".$values["q"]."%";
		}
		return [$sql, $vars];
	}
	public function listSearchNum($values = []) {
		list($sql, $vars) = $this->listSearchQuery($values);
		return $this->Db->numRows($sql, $vars);
	}
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