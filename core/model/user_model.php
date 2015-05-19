<?php
class User_Model_Core extends Model {

	public function getRegisterPage() {
		$Form = $this->getRegisterForm();
		if ($Form->submitted()) {
			$values = $Form->values();
			$User = $this->getEntity("User");
			$User->name = $values['email'];
			$User->email = $values['email'];
			$User->pass = $values['pass'];
			$User->status = 1;
			if ($this->Db->numRows("SELECT id FROM `user`") === 0) {
				$User->name = "admin";
				$User->save();
				$User->login();
				$this->redirect();
			}
			else {
				if ($this->Config->getUserRegistration() == "email_confirmation") {
					$link = $User->generateEmailConfirmationLink();
					if (!$User->save() || !$this->sendMail("UserEmailConfirmation", ["id" => $User->id(), "link" => $link]))
						setmsg(t("An error occurred", "error"));
					else {
						$User->login();
						setmsg(t("You've been signed into your new account. You must confirm your e-mail address within 24 hours."));
						$this->redirect();
					}
				}
				else if ($this->Config->getUserRegistration() == "admin_approval") {
					$User->status = 0;
					// TODO: Approval mail
					if (!$User->save())
						setmsg(t("An error occurred", "error"));
					else {
						setmsg(t("Your account registration is now pending approval from the site administrators."));
						$this->redirect();
					}
				}
				else {
					if (!$User->save())
						setmsg(t("An error occurred", "error"));
					else {
						$User->login();
						setmsg(t("Registration complete. You've been signed in to your new account."));
						$this->redirect();
					}
				}
			}
		}
		return [
			"form" => $Form->render()
		];
	}
	public function getRegisterForm() {
		$Form = $this->getForm("UserRegister");
		$Form->loadStructure();
		return $Form;
	}

	public function verifyEmailConfirmation($id, $link) {
		$User = $this->getEntity("User", $id);
		if (!$User->id() || !$User->verifyEmailConfirmationLink($link))
			return ["status" => 0];
		$User->set("email_confirmation", "");
		$User->set("email_confirmation_time", 0);
		$User->save();
		return ["status" => 1];
	}

	public function verifyReset($id, $link) {
		$User = $this->getEntity("User", $id);
		if (!$User->id() || !$User->verifyResetLink($link))
			return ["status" => 0];
		$User->set("reset", "");
		$User->set("reset_time", 0);
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