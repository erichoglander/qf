<?php
class Admin_Theme_Core extends Theme {
	
	public function __construct($Db) {
		parent::__construct($Db);
		$this->name = "admin";
		$this->css = ["default.css", "page.css", "menu.css", "form.css"];
		$this->js = [];
	}

};