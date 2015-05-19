<?php
class User_Controller_Core extends Controller {

	public function accessControl($action, $args = []) {
		return true; // TODO: Check access
	}

	public function index() {
		return $this->login();
	}

	public function login() {

	}

	public function logout() {

	}

	public function register() {
		if ($this->Config->getUserRegistration() == "closed")
			return $this->notFound();
		$vars = $this->Model->getRegisterPage();
		return $this->view("register", $vars);
	}

	public function reset() {

	}

	public function change_password($args = []) {
		if (count($args) != 2)
			return $this->notFound();
		$vars = $this->verifyReset($args[0], $args[1]);
		return $this->view("change_password", $vars);
	}

	public function confirm_email($args = []) {
		if (count($args) != 2)
			return $this->notFound();
		$vars = $this->verifyEmailConfirmation($args[0], $args[1]);
		return $this->view("confirm_email", $vars);
	}
	
	public function add() {
		$vars = $this->Model->getEditPage();
		return $this->view("edit", $vars);
	}

};