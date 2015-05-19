<?php
class Model {

	protected $Config, $Db, $Io, $User;

	public function __construct($Config, $Db, $Io, $User) {
		$this->Config = &$Config;
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

	protected function sendMail($name, $to, $vars = []) {
		$Mail = newClass($name."_Mail");
		return $Mail->send($to, $vars);
	}
	
};