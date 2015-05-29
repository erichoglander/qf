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
	* CORE UPDATES
	*/
	protected function update_1() {
		$num = $this->Db->numRows("SHOW TABLES LIKE 'image_style'");
		if (!$num) {
			$sql = "
					CREATE TABLE IF NOT EXISTS `image_style` (
						`key` varchar(64) COLLATE utf8_swedish_ci NOT NULL,
						`title` varchar(128) COLLATE utf8_swedish_ci NOT NULL,
						`type` varchar(64) COLLATE utf8_swedish_ci NOT NULL,
						`width` int(10) unsigned NOT NULL,
						`height` int(10) unsigned NOT NULL,
						PRIMARY KEY (`key`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci";
			return $this->Db->query($sql);
		}
		return true;
	}
	
};