<?php
/**
 * Contains the log model
 */
/**
 * Log model
 * @author Eric Höglander
 */
class Log_Model_Core extends Model {
	
	/**
	 * Delete log entry
	 * @return bool
	 */
	public function deleteLog($Log) {
		return $Log->delete();
	}

	/**
	 * Number of log entries in database
	 * @return int
	 */
	public function numLogs() {
		return $this->Db->numRows("SELECT * FROM `log`");
	}

	/**
	 * Get logs from database
	 * @param  int $start
	 * @param  int $stop
	 * @return array
	 */
	public function getLogs($start, $stop) {
		$logs = [];
		$rows = $this->Db->getRows("SELECT id FROM `log` ORDER BY id DESC LIMIT ".$start.", ".$stop);
		foreach ($rows as $row)
			$logs[] = $this->getEntity("Log", $row->id); 
		return $logs;
	}
	
	/**
	 * Creates a sql-query for a search
	 * @param  array $values
	 * @return array Contains sql-query and vars
	 */
	public function listSearchQuery($values) {
		$sql = "SELECT id FROM `log`";
		$vars = [];
		if (!empty($values["q"])) {
			$sql.= " WHERE category LIKE :q";
			$vars[":q"] = $values["q"]."%";
		}
		return [$sql, $vars];
	}
	/**
	 * Number of log entries matching a search
	 * @see    listSearchQuery
	 * @param  array $values
	 * @return int
	 */
	public function listSearchNum($values = []) {
		list($sql, $vars) = $this->listSearchQuery($values);
		return $this->Db->numRows($sql, $vars);
	}
	/**
	 * Search for log entries
	 * @see    listSearchQuery
	 * @param  array $values Search parameters
	 * @param  int   $start
	 * @param  int   $stop
	 * @return array
	 */
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