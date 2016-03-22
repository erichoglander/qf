<?php
/**
 * Contains the cron model
 */
/**
 * Cron model
 * @author Eric Höglander
 */
class Cron_Model_Core extends Model {
  
  /**
   * Executes the cron jobs
   */
  public function run() {
    $this->temporaryFiles();
  }

  /**
   * Deletes temporary files
   */
  public function temporaryFiles() {
    $rows = $this->Db->getRows("
        SELECT id FROM `file` 
        WHERE 
          status = 0 && 
          created < :time",
        [":time" => REQUEST_TIME - 60*60*24]);
    $deleted = [];
    foreach ($rows as $row) {
      $File = newClass("File_Entity", $this->Db, $row->id);
      $deleted[] = [
        "id" => $File->id(),
        "name" => $File->get("name"),
        "uri" => $File->get("uri"),
      ];
      $File->delete();
    }
    if (!empty($deleted)) 
      addlog("file", "Deleted ".count($deleted)." temporary files", $deleted, "success");
  }
  
}