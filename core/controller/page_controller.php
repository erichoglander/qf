<?php
class Page_Core_Controller extends Controller {

	public function index() {
		$this->Html->content = $this->viewRender("index");
		return $this->Html->render();
	}

};