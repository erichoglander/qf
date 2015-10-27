<?php
class Cache_Core {

	protected $Db;


	public function __construct($Db) {
		$this->Db = $Db;
	}
	
	public function get($name, $def = null) {
		$row = $this->Db->getRow("SELECT * FROM `cache` WHERE name = :name", [":name" => $name]);
		if (!$row)
			return $def;
		if ($row->expire < REQUEST_TIME) {
			$this->Db->delete("cache", ["name" => $name]);
			return $def;
		}
		return unserialize($row->data);
	}

	public function set($name, $data, $expire = 0) {
		$values = [
			"name" => $name,
			"data" => serialize($data),
			"expire" => $expire,
		];
		$row = $this->Db->getRow("SELECT name FROM `cache` WHERE name = :name", [":name" => $name]);
		if ($row) 
			$this->Db->update("cache", $values, ["name" => $name]);
		else 
			$this->Db->insert("cache", $values);
	}

	public function clear() {
		$this->Db->delete("cache");
		$this->clearImagestyles();
	}
	
	public function clearImageStyles() {
		$this->rmr(PUBLIC_PATH."/images/styles");
	}
	
	
	protected function rmr($path) {
		if (is_dir($path)) {
			$files = array_diff(scandir($path), [".", ".."]);
			foreach ($files as $file)
				$this->rmr($path."/".$file);
			rmdir($path);
		}
		else if (is_file($path)) {
			unlink($path);
		}
	}

}