<?php
class Log_Model_Core extends Model {
	
	public function deleteLog($Log) {
		return $Log->delete();
	}

	public function numLogs() {
		return $this->Db->numRows("SELECT * FROM `log`");
	}

	public function getLogs($start, $stop) {
		$logs = [];
		$rows = $this->Db->getRows("SELECT id FROM `log` ORDER BY id DESC LIMIT ".$start.", ".$stop);
		foreach ($rows as $row)
			$logs[] = $this->getEntity("Log", $row->id); 
		return $logs;
	}
	
	public function listSearchQuery($values) {
		$sql = "SELECT id FROM `log`";
		$vars = [];
		if (!empty($values["q"])) {
			$sql.= " WHERE category LIKE :q";
			$vars[":q"] = $values["q"]."%";
		}
		return [$sql, $vars];
	}
	public function listSearchNum($values = []) {
		list($sql, $vars) = $this->listSearchQuery($values);
		return $this->Db->numRows($sql, $vars);
	}
	public function listSearch($values = [], $start = 0, $stop = 50) {
		list($sql, $vars) = $this->listSearchQuery($values);
		$sql.= " ORDER BY id DESC LIMIT ".$start.", ".$stop;
		$rows = $this->Db->getRows($sql, $vars);
		$list = [];
		foreach ($rows as $row)
			$list[] = $this->getEntity("Log", $row->id);
		return $list;
	}

}