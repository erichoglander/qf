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
   * @return array
   */
  public function listSearchQuery($values) {
    $query = [
      "from" => "`log`",
      "cols" => ["id"],
      "where" => [],
      "order" => ["id DESC"],
      "vars" => [],
    ];
    if (!empty($values["q"])) {
      $query["where"][] = "text LIKE :q";
      $query["vars"][":q"] = "%".$values["q"]."%";
    }
    if (!empty($values["type"])) {
      $query["where"][] = "type = :type";
      $query["vars"][":type"] = $values["type"];
    }
    if (!empty($values["category"])) {
      $query["where"][] = "category = :category";
      $query["vars"][":category"] = $values["category"];
    }
    if (!empty($values["limit"]))
      $query["limit"] = $values["limit"];
    return $query;
  }
  /**
   * Number of log entries matching a search
   * @see    listSearchQuery
   * @param  array $values
   * @return int
   */
  public function listSearchNum($values = []) {
    $query = $this->listSearchQuery($values);
    return $this->Db->numRows($query);
  }
  /**
   * Search for log entries
   * @see    listSearchQuery
   * @param  array $values Search parameters
   * @return array
   */
  public function listSearch($values = []) {
    $query = $this->listSearchQuery($values);
    $rows = $this->Db->getRows($query);
    $list = [];
    foreach ($rows as $row)
      $list[] = $this->getEntity("Log", $row->id);
    return $list;
  }

}