<?php
class Admin_Theme extends Theme {
	
	public function __construct($Db) {
		parent::__construct($Db);
		$this->name = "admin";
		$this->css = Array("default.css");
		$this->js = Array();
	}

};