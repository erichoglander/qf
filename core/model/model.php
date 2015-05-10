<?php
class Model {

	protected $Db;

	public function __construct($Db) {
		$this->Db = &$Db;
	}


	protected function getForm($name) {
		return newClass($name."_Form", $this->Db);
	}

	protected function getEntity($name, $id = null) {
		return newClass($name."_Entity", $this->Db, $id);
	}
	
};