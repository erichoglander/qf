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

}