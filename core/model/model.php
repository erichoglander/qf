<?php
class Model {

	protected $Config, $Db, $Io, $Cache, $User;

	public function __construct($Config, $Db, $Io, $Cache, $User) {
		$this->Config = &$Config;
		$this->Db = &$Db;
		$this->Io = &$Io;
		$this->Cache = &$Cache;
		$this->User = &$User;
	}


	protected function getEntity($name, $id = null) {
		return newClass($name."_Entity", $this->Db, $id);
	}

	protected function sendMail($name, $to, $vars = []) {
		$Mail = newClass($name."_Mail", $this->Db);
		if (!$Mail)
			throw new Exception("Can't find email message ".$name);
		return $Mail->send($to, $vars);
	}
	
};