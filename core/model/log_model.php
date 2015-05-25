<?php
class Log_Model_Core extends Model {
	
	public function deleteLog($Log) {
		return $Log->delete();
	}

	public function getLogs() {
		$logs = [];
		$rows = $this->Db->getRows("SELECT id FROM `log` ORDER BY id DESC");
		foreach ($rows as $row)
			$logs[] = $this->getEntity("Log", $row->id); 
		return $logs;
	}

}