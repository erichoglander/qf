<?php
class Cache_Model extends Model {

	public function delete($name) {
		$this->Db->delete("cache", ["name" => $name]);
	}
	
	public function getCaches() {
		$rows = $this->Db->getRows("
				SELECT name, expire, OCTET_LENGTH(data) as size FROM `cache`
				ORDER BY name ASC");
		return $rows;
	}

}