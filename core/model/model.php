<?php
class Model {

	protected $Db;

	public function __construct($Db) {
		$this->Db = &$Db;
	}


	protected function getEntity($name, $id = null) {
		return newClass($name."_Entity", $this->Db, $id);
	}
	
};