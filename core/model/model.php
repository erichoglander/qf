<?php
class Model {

	protected $Db, $Io;

	public function __construct($Db, $Io) {
		$this->Db = &$Db;
		$this->Io = &$Io;
	}


	protected function getForm($name) {
		return newClass($name."_Form", $this->Db, $this->Io);
	}

	protected function getEntity($name, $id = null) {
		return newClass($name."_Entity", $this->Db, $id);
	}
	
};