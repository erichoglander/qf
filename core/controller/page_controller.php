<?php
class Page_Controller_Core extends Controller {

	public function indexAction() {
		return $this->view("index");
	}

};