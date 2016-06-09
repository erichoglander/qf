<?php
/**
 * Contains update class
 */

/**
 * Update class
 *
 * Serves as a base for updates
 * 
 * @author Eric Höglander
 */
class Update_Core {

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
   * Run the update
   * @return bool
   */
  public function execute() {
    return true;
  }

  /**
   * What part he update belongs to, core or extend
   * @return string
   */
  public function part() {
    $name = get_class($this);
    return (strpos($name, "_Core") ? "core" : "extend");
  }

  /**
   * Update number
   * @return int
   */
  public function nr() {
    $name = get_class($this);
    return (int) str_replace(["Update_", "_Core"], "", $name);
  }
  
  
  /**
   * Attempt to execute queries, returns false on first failure
   * @param  array $queries
   * @return bool
   */
  protected function dbQueries($queries) {
    foreach ($queries as $query) {
      if (!$this->Db->query($query))
        return false;
    }
    return true;
  }

}