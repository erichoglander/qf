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
				if ($this->sendEmailConfirmation($User)) {
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
		return null;
	}

	public function sendEmailConfirmation($User) {
		$link = $User->generateEmailConfirmationLink();
		return $User->save() && $this->sendMail("UserEmailConfirmation", $User->get("email"), ["id" => $User->id(), "link" => $link]);
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
			$User->set("id", 1); # admin account cannot be deactivated
		return $User->save();
	}

	public function saveSettings($User, $values) {
		$change_email = $values["email"] != $User->get("email");
		foreach ($values as $key => $value)
			$User->set($key, $value);
		if ($change_email) {
			if ($this->sendEmailConfirmation($User))
				return "email_confirmation";
			else
				return false;
		}
		if ($User->save())
			return true;
		else
			return false;
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
	
};