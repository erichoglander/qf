<?php
/**
 * Contains the cache class
 */

/**
 * Cache class
 *
 * Used to cache data in the database
 * 
 * @author Eric Höglander
 */
class Cache_Core {

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
	 * Get cached data
	 * @param  string $name
	 * @param  mixed  $def  Default value if the cache doesn't exist
	 * @return mixed
	 */
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

	/**
	 * Save data in database
	 * @param string  $name
	 * @param mixed   $data
	 * @param int     $expire
	 */
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

	/**
	 * Clears all caches
	 */
	public function clear() {
		$this->Db->delete("cache");
		$this->clearImagestyles();
	}
	
	/**
	 * Delete all public styled images
	 */
	public function clearImageStyles() {
		$this->rmr(PUBLIC_PATH."/images/styles");
	}
	

	/**
	 * Remove path and, if its a directory, everything in it
	 * @param string $path
	 */
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