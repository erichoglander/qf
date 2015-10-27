<?php
class Update_Core {

	protected $Db;
	

	public function __construct($Db) {
		$this->Db = $Db;
	}
	
	public function execute() {
		return true;
	}

}