<?php
class User_Controller_Core extends Controller {

	public function access($action, $args = []) {
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
		$vars = $this->Model->getRegisterPage();
		return $this->view("register", $vars);
	}

	public function reset() {

	}
	
	public function add() {
		$vars = $this->Model->getEditPage();
		return $this->view("edit", $vars);
	}

};