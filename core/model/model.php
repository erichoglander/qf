<?php
class Model {

	protected $Db;

	public function __construct($Db) {
		$this->Db = &$Db;
	}
	
};