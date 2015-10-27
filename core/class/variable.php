<?php
class Variable_Core {
	
	protected $Db;


	public function __construct($Db) {
		$this->Db = $Db;
	}
	
	public function get($name, $def = null) {
		$row = $this->Db->getRow("SELECT * FROM `variable` WHERE name = :name", [":name" => $name]);
		if (!$row)
			return $def;
		return unserialize($row->data);
	}

	public function set($name, $data) {
		$values = [
			"name" => $name,
			"data" => serialize($data),
		];
		$row = $this->Db->getRow("SELECT name FROM `variable` WHERE name = :name", [":name" => $name]);
		if ($row) 
			$this->Db->update("variable", $values, ["name" => $name]);
		else 
			$this->Db->insert("variable", $values);
	}

}