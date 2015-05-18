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

	public function reset($args = []) {

	}
	
	public function add() {
		$vars = $this->Model->getEditPage();
		return $this->view("edit", $vars);
	}

};