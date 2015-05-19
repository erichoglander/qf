<?php
class User_Model_Core extends Model {

	public function getLoginPage() {
		$vars = [];
		if ($this->User->id()) {
			if ($this->Config->getUserRegistration() == "closed")
				redirect();
			else
				redirect("user/register");
		}
		$Form = $this->getLoginForm();
		if ($Form->submitted()) {
			$values = $Form->values();
			$User = $this->getEntity("User");
			$User->loadByName($values["name"]);
			setmsg(t("You have been signed in"));
			$User->login();
			redirect();
		}
		return [
			"form" => $Form->render(),
		];
	}
	public function getLoginForm() {
		$Form = $this->getForm("UserLogin");
		$Form->loadStructure();
		return $Form;
	}

	public function getRegisterPage() {
		$vars = [];
		$vars["status"] = $this->Config->getUserRegistration();
		$num_users = $this->Db->numRows("SELECT id FROM `user`");
		if ($num_users === 0)
			$vars["status"] = "open";
		if ($vars["status"] == "closed")
			return $vars;
		$Form = $this->getRegisterForm();
		if ($Form->submitted()) {
			$values = $Form->values();
			$User = $this->getEntity("User");
			$User->set("name", $values['name']);
			$User->set("email", $values['email']);
			$User->set("pass", $values['password']);
			$User->set("status", 1);
			if ($num_users === 0) {
				$User->save();
				$User->login();
				redirect();
			}
			else {
				if ($this->Config->getUserRegistration() == "email_confirmation") {
					$link = $User->generateEmailConfirmationLink();
					if ($User->save() && $this->sendMail("UserEmailConfirmation", ["id" => $User->id(), "link" => $link])) {
						$User->login();
						setmsg(t("You've been signed into your new account. You must confirm your e-mail address within 24 hours."));
						redirect();
					}
					else {
						setmsg(t("An error occurred", "error"));
					}
				}
				else if ($this->Config->getUserRegistration() == "admin_approval") {
					$User->set("status", 0);
					// TODO: Approval mail
					if (!$User->save())
						setmsg(t("An error occurred", "error"));
					else {
						setmsg(t("Your account registration is now pending approval from the site administrator."));
						redirect();
					}
				}
				else {
					if (!$User->save())
						setmsg(t("An error occurred", "error"));
					else {
						$User->login();
						setmsg(t("Registration complete. You've been signed in to your new account."));
						redirect();
					}
				}
			}
		}
		$vars["form"] = $Form->render();
		return $vars;
	}
	public function getRegisterForm() {
		$Form = $this->getForm("UserRegister");
		$Form->loadStructure();
		return $Form;
	}

	public function getResetPage() {
		if ($this->User->id())
			redirect();
		$Form = $this->getResetForm();
		if ($Form->submitted()) {
			$values = $Form->values();
			$User = $this->getEntity("User");
			$User->loadByEmail($values["email"]);
			$link = $User->generateResetLink();
			if ($User->save() && $this->sendMail("UserReset", $values["email"], ["id" => $User->id(), "link" => $link]))
				setmsg(t("An e-mail has been sent with further instructions."));
			else
				setmsg(t("An error occurred", "error"));
			redirect();
		}
		return [
			"form" => $Form->render(),
		];
	}
	public function getResetForm() {
		$Form = $this->getForm("UserReset");
		$Form->loadStructure();
		return $Form;
	}

	public function getChangePasswordPage($id, $link) {
		$User = $this->getEntity("User", $id);
		if (!$User->id() || !$User->verifyResetLink($link))
			return ["status" => 0];
		$Form = $this->getChangePasswordForm();
		if ($Form->submitted()) {
			$values = $Form->values();
			$User->set("pass", $values["password"]);
			$User->set("reset", "");
			$User->set("reset_time", 0);
			if ($User->save()) {
				$User->login();
				setmsg(t("Your account password has been changed and you have been automatically signed in."));
				redirect();
			}
			else {
				setmsg(t("An error occurred", "error"));
			}
		}
		return [
			"status" => 1, 
			"name" => $User->get("name"), 
			"form" => $Form->render(),
		];
	}
	public function getChangePasswordForm() {
		$Form = $this->getForm("UserChangePassword");
		$Form->loadStructure();
		return $Form;
	}

	public function getEmailConfirmationPage($id, $link) {
		$User = $this->getEntity("User", $id);
		if (!$User->id() || !$User->verifyEmailConfirmationLink($link))
			return ["status" => 0];
		$User->set("email_confirmation", "");
		$User->set("email_confirmation_time", 0);
		$User->save();
		return ["status" => 1];
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