<?php
class User_Controller_Core extends Controller {

	public function accessControl($action, $args = []) {
		if ($action == "add" || $action == "edit")
			return $this->User->id() == 1;
		return true;
	}

	public function index() {
		return $this->login();
	}

	public function login() {
		$Form = $this->getForm("UserLogin");
		if ($Form->isSubmitted()) {
			$values = $Form->values();
			$User = $this->getEntity("User");
			$User->loadByName($values["name"]);
			$User->login();
			setmsg(t("You have been signed in"));
			redirect();
		}
		$this->viewData["form"] = $Form->render();
		return $this->view("login");
	}

	public function logout() {
		if ($this->User->id())
			setmsg(t("You have been signed out"));
		$this->User->logout();
		redirect();
	}

	public function register() {
		if ($this->User->id())
			redirect();
		$Form = $this->getForm("UserRegister");
		if ($Form->isSubmitted()) {
			if ($this->Model->register($Form->values()))
				redirect();
		}
		$this->viewData["form"] = $Form->render();
		return $this->view("register");
	}

	public function reset() {
		if ($this->User->id())
			redirect();
		$Form = $this->getForm("reset");
		if ($Form->isSubmitted()) {
			$values = $Form->values();
			$User = $this->getEntity();
			$User->loadByEmail($values["email"]);
			if ($this->Model->reset($User)) 
				redirect();
		}
		$this->viewData['form'] = $Form->render();
		return $this->view("reset");
	}

	public function change_password($args = []) {
		if (count($args) != 2)
			return $this->notFound();
		$User = $this->getEntity("UserChangePassword", $args[0]);
		if (!$User->id() || !$User->verifyResetLink($args[1]))
			return $this->view("reset_invalid");
		$Form = $this->getForm("UserChangePassword");
		if ($Form->isSubmitted()) {
			$values = $Form->values();
			if ($this->Model->change_password($User, $values["password"])) 
				redirect();
		}
		$this->viewData["name"] = $User->get("name");
		$this->viewData["form"] = $Form->render();
		return $this->view("change_password");
	}

	public function confirm_email($args = []) {
		if (count($args) != 2)
			return $this->notFound();
		$User = $this->getEntity("User", $id);
		if (!$User->id() || !$User->verifyEmailConfirmationLink($link))
			return $this->view("confirm_email_invalid");
		if (!$this->Model->emailConfirm($User))
			setmsg(t("An error occurred", "error"));
		return $this->view("confirm_email");
	}
	
	public function add() {
		$Form = $this->getForm("userEdit");
		$this->viewData["User"] = $this->getEntity("User");
		$this->viewData["form"] = $Form->render();
		return $this->view("edit");
	}

};