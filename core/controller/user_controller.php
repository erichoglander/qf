<?php
class User_Controller_Core extends Controller {

	public function accessControl($action, $args = []) {
		if ($action == "add" || $action == "edit" || $action == "delete" || $action == "list" || $action == "signin")
			return $this->User->id() == 1;
		return true;
	}

	public function index() {
		if ($this->User->id())
			redirect("user/listing");
		return $this->login();
	}

	public function login() {
		if ($this->User->id())
			redirect();
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
		if ($this->User->id()) {
			setmsg(t("You have been signed out"));
			$this->User->logout();
		}
		redirect();
	}

	public function register() {
		if ($this->User->id())
			redirect();
		$Form = $this->getForm("UserRegister");
		if ($Form->isSubmitted()) {
			$status = $this->Model->register($Form->values());
			if ($status == "email_confirmation")
				setmsg(t("You've been signed into your new account. You must confirm your e-mail address within 24 hours."));
			else if ($status == "admin_approval")
				setmsg(t("Your account registration is now pending approval from the site administrator."));
			else if ($status == "admin_login")
				setmsg(t("An admin account has been created and you have been signed in."));
			else if ($status == "register_login")
				setmsg(t("Registration complete. You've been signed in to your new account."));
			else if (!$status)
				setmsg(t("An error occurred", "error"));
			if ($status)
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
			if ($this->Model->reset($User)) {
				setmsg(t("An e-mail has been sent with further instructions."));
				redirect();
			}
			else {
				setmsg(t("An error occurred"), "error");
			}
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
			if ($this->Model->change_password($User, $values["password"])) {
				setmsg(t("Your account password has been changed. You can use your new password to sign in."));
				redirect();
			}
			else {
				setmsg(t("An error occurred"), "error");
			}
		}
		$this->viewData["name"] = $User->get("name");
		$this->viewData["form"] = $Form->render();
		return $this->view("change_password");
	}

	public function confirm_email($args = []) {
		if (count($args) != 2)
			return $this->notFound();
		$User = $this->getEntity("User", $args[0]);
		if (!$User->id() || !$User->verifyEmailConfirmationLink($args[1]))
			return $this->view("confirm_email_invalid");
		if (!$this->Model->confirmEmail($User))
			setmsg(t("An error occurred", "error"));
		return $this->view("confirm_email");
	}
	
	public function add() {
		$Form = $this->getForm("userEdit");
		if ($Form->isSubmitted()) {
			$User = $this->Model->addUser($Form->values());
			if ($User) {
				setmsg(t("User :user added", "en", [":user" => $User->name()]));
				redirect("user/listing");
			}
			else {
				setmsg(t("An error occurred", "error"));
			}
		}
		$this->viewData["form"] = $Form->render();
		return $this->view("add");
	}
	
	public function edit($args = []) {
		if (empty($args[0]))
			return $this->notFound();
		$User = $this->getEntity("User", $args[0]);
		if (!$User->id())
			return $this->notFound();
		$Form = $this->getForm("userEdit", [
			"id" => $User->id(),
			"name" => $User->get("name"),
			"email" => $User->get("email"),
			"status" => $User->get("status"),
		]);
		if ($Form->isSubmitted()) {
			if ($this->Model->editUser($User, $Form->values())) {
				setmsg(t("User :user saved", "en", [":user" => $User->name()]));
				redirect("user/listing");
			}
			else {
				setmsg(t("An error occurred", "error"));
			}
		}
		$this->viewData["form"] = $Form->render();
		return $this->view("edit");
	}

	public function delete($args = []) {
		$User = $this->getEntity("User", $args[0]);
		if (!$User->id())
			return $this->notFound();
		$Form = $this->getForm("Confirm", [
			"text" => t("Are you sure you want to delete the user :user?", "en", [":user" => $User->get("name")]),
		]);
		if ($Form->isSubmitted()) {
			if ($this->Model->deleteUser($User)) {
				setmsg(t("User :user deleted", "en", [":user" => $User->name()]));
				redirect("user/listing");
			}
			else {
				setmsg(t("An error occurred", "error"));
			}
		}
		$this->viewData["form"] = $Form->render();
		return $this->view("delete");
	}

	public function signin($args = []) {
		$User = $this->getEntity("User", $args[0]);
		if (!$User->id())
			return $this->notFound();
		$User->login();
		setmsg(t("Now logged in as :user", "en", [":user" => $User->get("name")]));
		redirect("user/listing");
	}

	public function listing() {
		$this->viewData["users"] = $this->Model->getUsers();
		return $this->view("listing");
	}

};