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
			if ($this->Config->getUserRegistration() == "email_confirmation") {
				$link = $User->generateEmailConfirmationLink();
				$User->save();
				$this->sendMail("UserEmailConfirmation", ["id" => $User->id(), "link" => $link]);
			}
			else if ($this->Config->getUserRegistration() == "admin_approval") {
				$User->status = 0;
				$User->save();
				// TODO: Approval mail
			}
			else {
				$User->save();
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