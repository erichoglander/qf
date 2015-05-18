<?php
class Model {

	protected $Db, $Io, $User;

	public function __construct($Db, $Io, $User) {
		$this->Db = &$Db;
		$this->Io = &$Io;
		$this->User = &$User;
	}


	protected function getForm($name) {
		return newClass($name."_Form", $this->Db, $this->Io);
	}

	protected function getEntity($name, $id = null) {
		return newClass($name."_Entity", $this->Db, $id);
	}
	
};