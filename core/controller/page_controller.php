<?php
class Page_Core_Controller extends Controller {

	public function index() {
		return $this->viewRender("index");
	}

};