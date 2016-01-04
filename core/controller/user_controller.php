<?php
class User_Controller_Core extends Controller {

	public function acl($action, $args = []) {
		if ($action == "add" || $action == "edit")
			return ["userAdmin", "userEdit"];
		if ($action == "delete")
			return ["userAdmin", "userDelete"];
		if ($action == "list")
			return ["userAdmin", "userList"];
		if ($action == "signin")
			return ["userSignin"];
		if ($action == "settings")
			return ["userSettings"];
		if ($action == "setpass")
			return ["userSetPass"];
		if ($action == "clearFlood")
			return ["userClearFlood"];
		return null;
	}

	public function indexAction() {
		if ($this->User->id())
			redirect("user/list");
		return $this->loginAction();
	}

	public function loginAction() {
		if ($this->User->id())
			redirect();
		$Form = $this->getForm("UserLogin");
		if ($Form->isSubmitted()) {
			$values = $Form->values();
			$User = $this->getEntity("User");
			$User->loadByName($values["name"]);
			$User->login();
			setmsg(t("You have been signed in"), "success");
			redirect();
		}
		$this->viewData["form"] = $Form->render();
		return $this->view("login");
	}

	public function logoutAction() {
		if ($this->User->id()) {
			setmsg(t("You have been signed out"), "success");
			$this->User->logout();
		}
		redirect();
	}

	public function registerAction() {
		if ($this->User->id())
			redirect();
		$Form = $this->getForm("UserRegister");
		if ($Form->isSubmitted()) {
			$status = $this->Model->register($Form->values());
			if ($status == "email_confirmation") {
				setmsg(t("You've been registered and signed into your new account. "), "success");
				setmsg(t("You must confirm your e-mail address within 24 hours."), "warning");
			}
			else if ($status == "admin_approval") {
				setmsg(t("Your account registration is now pending approval from the site administrator."), "success");
			}
			else if ($status == "register_login") {
				setmsg(t("Registration complete. You've been signed in to your new account."), "success");
			}
			else if (!$status) {
				$this->defaultError();
			}
			if ($status)
				redirect();
		}
		$this->viewData["form"] = $Form->render();
		$this->viewData["status"] = $this->Config->getUserRegistration();
		return $this->view("register");
	}

	public function resetAction() {
		if ($this->User->id())
			redirect();
		$Form = $this->getForm("userReset");
		if ($Form->isSubmitted()) {
			$values = $Form->values();
			$User = $this->getEntity("User");
			$User->loadByEmail($values["email"]);
			if ($this->Model->reset($User)) {
				setmsg(t("An e-mail has been sent with further instructions."), "success");
				redirect();
			}
			else {
				$this->defaultError();
			}
		}
		$this->viewData["form"] = $Form->render();
		return $this->view("reset");
	}

	public function changePasswordAction($args = []) {
		if (count($args) != 2)
			return $this->notFound();
		$User = $this->getEntity("User", $args[0]);
		if (!$User->id() || !$User->verifyResetLink($args[1]))
			return $this->view("reset_invalid");
		$Form = $this->getForm("UserChangePassword");
		if ($Form->isSubmitted()) {
			$values = $Form->values();
			if ($this->Model->changePassword($User, $values["pass"])) {
				setmsg(t("Your account password has been changed. You can use your new password to sign in."), "success");
				redirect();
			}
			else {
				$this->defaultError();
			}
		}
		$this->viewData["name"] = $User->get("name");
		$this->viewData["form"] = $Form->render();
		return $this->view("change_password");
	}

	public function confirmEmailAction($args = []) {
		if (count($args) != 2)
			return $this->notFound();
		$User = $this->getEntity("User", $args[0]);
		if (!$User->id() || !$User->verifyEmailConfirmationLink($args[1]))
			return $this->view("confirm_email_invalid");
		if (!$this->Model->confirmEmail($User))
			$this->defaultError();
		return $this->view("confirm_email");
	}
	
	public function resendEmailConfirmationAction($args = []) {
		if (empty($args[0]))
			return $this->notFound();
		$User = $this->getEntity("User", $args[0]);
		if (!$User->id())
			return $this->notFound();
		if (!$User->get("email_confirmation"))
			return $this->notFound();
		$Form = $this->getForm("userResendEmailConfirmation", ["User" => $User]);
		if ($Form->isSubmitted()) {
			if ($this->Model->resendEmailConfirmation($User, $Form->values())) {
				setmsg(t("An e-mail has been sent with further instructions."), "success");
				redirect();
			}
			else {
				$this->defaultError();
			}
		}
		$this->viewData["form"] = $Form->render();
		return $this->view("resend_email_confirmation");
	}
	
	public function addAction() {
		$Form = $this->getForm("userEdit");
		if ($Form->isSubmitted()) {
			$User = $this->Model->addUser($Form->values());
			if ($User) {
				setmsg(t("User :user added", "en", [":user" => $User->name()]), "success");
				redirect("user/list");
			}
			else {
				$this->defaultError();
			}
		}
		$this->viewData["form"] = $Form->render();
		return $this->view("add");
	}
	
	public function editAction($args = []) {
		if (empty($args[0]))
			return $this->notFound();
		$User = $this->getEntity("User", $args[0]);
		if (!$User->id())
			return $this->notFound();
		$Form = $this->getForm("userEdit", ["User" => $User]);
		if ($Form->isSubmitted()) {
			if ($this->Model->editUser($User, $Form->values())) {
				setmsg(t("User :user saved", "en", [":user" => $User->name()]), "success");
				redirect("user/list");
			}
			else {
				$this->defaultError();
			}
		}
		$this->viewData["form"] = $Form->render();
		return $this->view("edit");
	}

	public function settingsAction() {
		if (!$this->User->id())
			redirect("user/login");
		$Form = $this->getForm("userSettings", ["User" => $this->User]);
		if ($Form->isSubmitted()) {
			$re = $this->Model->saveSettings($this->User, $Form->values());
			if (empty($re)) {
				$this->defaultError();
			}
			else {
				setmsg(t("Settings saved!"), "success");
				if ($re === "email_confirmation")
					setmsg(t("You must confirm your e-mail within 24 hours."), "warning");
				refresh();
			}
		}
		$this->viewData["form"] = $Form->render();
		return $this->view("settings");
	}

	public function deleteAction($args = []) {
		$User = $this->getEntity("User", $args[0]);
		if (!$User->id())
			return $this->notFound();
		$Form = $this->getForm("Confirm", [
			"text" => t("Are you sure you want to delete the user :user?", "en", [":user" => $User->get("name")]),
		]);
		if ($Form->isSubmitted()) {
			if ($this->Model->deleteUser($User)) {
				setmsg(t("User :user deleted", "en", [":user" => $User->name()]), "success");
				redirect("user/list");
			}
			else {
				$this->defaultError();
			}
		}
		$this->viewData["form"] = $Form->render();
		return $this->view("delete");
	}

	public function signinAction($args = []) {
		$User = $this->getEntity("User", $args[0]);
		if (!$User->id())
			return $this->notFound();
		$this->Model->signInAs($User);
		setmsg(t("Now logged in as :user", "en", [":user" => $User->get("name")]), "success");
		redirect();
	}
	
	public function signbackAction() {
		$User = $this->Model->signBack();
		if ($User) {
			setmsg(t("Now logged in as :user", "en", [":user" => $User->get("name")]), "success");
			redirect();
		}
		else {
			return $this->accessDenied();
		}
	}
	
	public function setpassAction() {
		print t("Username").": ";
		$name = trim(fgets(STDIN));
		$User = $this->getEntity("User");
		$User->loadByName($name);
		if (!$User->id())
			return "User not found";
		print t("Password").": ";
		system("stty -echo");
		$pass = trim(fgets(STDIN));
		system("stty echo");
		print PHP_EOL;
		$User->set("pass", $pass);
		$User->save();
		return t("Password changed for :name", "en", [":name" => $name]);
	}
	
	public function clearFloodAction() {
		$this->Model->clearLoginAttempts();
		setmsg(t("Login attempts cleared"), "success");
		redirect();
	}

	public function listAction() {
		$values = (array_key_exists("user_list_search", $_SESSION) ? $_SESSION["user_list_search"] : []);
		$Form = $this->getForm("Search", ["q" => (!empty($values["q"]) ? $values["q"] : null)]);
		if ($Form->isSubmitted()) {
			$_SESSION["user_list_search"] = $Form->values();
			refresh();
		}
		$Pager = newClass("Pager");
		$Pager->setNum($this->Model->listSearchNum($values));
		$this->viewData["users"] = $this->Model->listSearch($values, $Pager->start(), $Pager->ppp);
		$this->viewData["pager"] = $Pager->render();
		$this->viewData["search"] = $Form->render();
		return $this->view("list");
	}

};