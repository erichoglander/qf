<?php
class Updater_Core {
	
	protected $Db, $Variable;
	
	
	public function __construct($Db) {
		$this->Db = &$Db;
		$this->Variable = newClass("Variable", $this->Db);
	}
	
	public function runUpdate($value) {
		$method = "update_".$value;
		if (!is_callable([$this, $method]))
			return false;
		if (!$this->$method())
			return false;
		$last = $this->Variable->get("core_update", 0);
		$this->Variable->set("core_update", max($value, $last));
		return true;
	}
	
	public function getUpdates() {
		$updates = [];
		$last = $this->Variable->get("core_update", 0);
		$list = get_class_methods($this);
		foreach ($list as $method) {
			if (strpos($method, "update_") === 0) {
				$value = (int) substr($method, 7);
				if ($value > $last)
					$updates[] = $value;
			}
		}
		if (!empty($updates))
			sort($updates, SORT_NUMERIC);
		return $updates;
	}
	
	
	
	/**
	* CORE UPDATES BELOW
	*/
	
	
};