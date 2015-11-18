<?php
/**
 * Contains the variable class
 */

/**
 * Variable class
 *
 * Used to save variables in the database
 * 
 * @author Eric Höglander
 */
class Variable_Core {
	
	/**
	 * Database object
	 * @var \Db_Core
	 */
	protected $Db;


	/**
	 * Constructor
	 * @param \Db_Core $Db
	 */
	public function __construct($Db) {
		$this->Db = $Db;
	}
	
	/**
	 * Fetches a variable from the database
	 * @param  string $name
	 * @param  mixed  $def
	 * @return mixed
	 */
	public function get($name, $def = null) {
		$row = $this->Db->getRow("SELECT * FROM `variable` WHERE name = :name", [":name" => $name]);
		if (!$row)
			return $def;
		return unserialize($row->data);
	}

	/**
	 * Save a variable in the database
	 * @param string $name
	 * @param mixed $data
	 */
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