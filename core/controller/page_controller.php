<?php
class Page_Core_Controller extends Controller {

	public function index() {
		$this->Html->content = $this->view("index");
		$this->Html->h1 = "Front page";
		return $this->Html->renderHtml();
	}

};