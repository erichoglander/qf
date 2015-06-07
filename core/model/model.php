<?php
class Model {

	protected $Config, $Db, $Io, $Cache, $Variable, $User;

	public function __construct($Config, $Db, $Io, $Cache, $Variable, $User) {
		$this->Config = &$Config;
		$this->Db = &$Db;
		$this->Io = &$Io;
		$this->Cache = &$Cache;
		$this->Variable = &$Variable;
		$this->User = &$User;
	}


	protected function getEntity($name, $id = null) {
		return newClass($name."_Entity", $this->Db, $id);
	}
	
	protected function getModel($name) {
		$cname = ucwords($name)."_Model";
		return newClass($cname, $this->Config, $this->Db, $this->Io, $this->Cache, $this->Variable, $this->User);
	}

	protected function sendMail($name, $to, $vars = []) {
		$Mail = newClass($name."_Mail", $this->Db);
		if (!$Mail)
			throw new Exception("Can't find email message ".$name);
		return $Mail->send($to, $vars);
	}
	
};