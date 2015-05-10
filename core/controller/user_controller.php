<?php
class User_Core_Controller extends Controller {

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
		$vars = [];
		$vars['User'] = $this->getEntity("User");
		$vars['Form'] = $this->Model->getEditForm($vars['User']);
		return $this->view("edit", $vars);
	}

};