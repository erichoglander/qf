<?php
class Log_Model_Core extends Model {
	
	public function deleteLog($Log) {
		return $Log->delete();
	}

	public function getLog($id) {
		$Log = $this->getEntity("Log", $id);
		if (!$Log->id())
			return null;
		$Log->set("User", $this->getEntity("User", $Log->get("user_id")));
		return $Log;
	}

	public function getLogs() {
		$logs = [];
		$rows = $this->Db->getRows("SELECT id FROM `log` ORDER BY id DESC");
		foreach ($rows as $row)
			$logs[] = $this->getLog($row->id); 
		return $logs;
	}

}