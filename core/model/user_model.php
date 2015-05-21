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
		}
		else {
			if ($registration == "email_confirmation") {
				$link = $User->generateEmailConfirmationLink();
				if ($User->save() && $this->sendMail("UserEmailConfirmation", ["id" => $User->id(), "link" => $link])) {
					$User->login();
					setmsg(t("You've been signed into your new account. You must confirm your e-mail address within 24 hours."));
					return true;
				}
				else {
					setmsg(t("An error occurred", "error"));
					return false;
				}
			}
			else if ($registration == "admin_approval") {
				$User->set("status", 0);
				// TODO: Approval mail
				if (!$User->save()) {
					setmsg(t("An error occurred", "error"));
					return false;
				}
				else {
					setmsg(t("Your account registration is now pending approval from the site administrator."));
					return true;
				}
			}
			else {
				if (!$User->save()) {
					setmsg(t("An error occurred", "error"));
					return false;
				}
				else {
					$User->login();
					setmsg(t("Registration complete. You've been signed in to your new account."));
					return true;
				}
			}
		}
	}

	public function reset($User) {
		$link = $User->generateResetLink();
		if ($User->save() && $this->sendMail("UserReset", $User->get("email"), ["id" => $User->id(), "link" => $link])) {
			setmsg(t("An e-mail has been sent with further instructions."));
			return true;
		}
		return false;
	}

	public function changePassword($User, $password) {
		$User->set("pass", $password);
		$User->set("reset", "");
		$User->set("reset_time", 0);
		if ($User->save()) {
			setmsg(t("Your account password has been changed. You can use your new password to sign in."));
			return true;
		}
		else {
			return false;
		}
	}

	public function emailConfirm($User) {
		$User->set("email_confirmation", "");
		$User->set("email_confirmation_time", 0);
		return $User->save();
	}
	
	public function getEditPage() {
		$User = $this->getEntity("User");
		$Form = $this->getEditForm($User);
		if ($Form->submitted()) {
			$values = $Form->values();
			pr($values);
		}
		return [
			"User" => $User,
			"form" => $Form->render()
		];
	}
	public function getEditForm($User) {
		$Form = $this->getForm("UserEdit");
		$Form->loadStructure([
			"id" => $User->id(),
			"name" => $User->get("name"),
			"email" => $User->get("email"),
			"status" => $User->get("status", 1),
		]);
		return $Form;
	}
	
};