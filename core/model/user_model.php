<?php
class User_Model_Core extends Model {

	public function register($values) {
		$registration = $this->Config->getUserRegistration();
		$num_users = $this->Db->numRows("SELECT id FROM `user`");
		$User = $this->getEntity("User");
		$User->set("name", $values['name']);
		$User->set("email", $values['email']);
		$User->set("pass", $values['password']);
		$User->set("status", 1);
		if ($num_users === 0) {
			$User->save();
			$User->login();
			return "admin_login";
		}
		else {
			if ($registration == "email_confirmation") {
				$link = $User->generateEmailConfirmationLink();
				if ($User->save() && $this->sendMail("UserEmailConfirmation", ["id" => $User->id(), "link" => $link])) {
					$User->login();
					return "email_confirmation";
				}
			}
			else if ($registration == "admin_approval") {
				$User->set("status", 0);
				// TODO: Approval mail
				if ($User->save()) 
					return "admin_approval";
			}
			else {
				if ($User->save())
					return "register_login";
			}
		}
	}

	public function reset($User) {
		$link = $User->generateResetLink();
		return $User->save() && $this->sendMail("UserReset", $User->get("email"), ["id" => $User->id(), "link" => $link]);
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
	
	public function addUser($values) {
		$User = $this->getEntity("User");
		foreach ($values as $key => $value)
			$User->set($key, $value);
		if ($User->save())
			return $User;
		else
			return null;
	}
	
	public function editUser($values) {
		$User = $this->getEntity("User");
		foreach ($values as $key => $value)
			$User->set($key, $value);
		return $User->save();
	}
	
};