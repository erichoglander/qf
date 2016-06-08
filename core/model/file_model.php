<?php
/**
 * Contains file model
 */
/**
 * File model
 * @author Eric Höglander
 */
class File_Model_Core extends Model {
  
  /**
   * Attempt to load private file from an uri
   * @param  string $uri
   * @return \File_Entity_Core
   */
  public function getPrivateFileFromUri($uri) {
    $File = $this->getEntity("File");
    $File->loadFromUri($uri, "private");
    if (!$File->id())
      return null;
    return $File;
  }
  
  /**
   * Delete all unused managed files
   * @return int Number of files deleted
   */
  public function cleanup() {
    $row = $this->Db->getRow("SELECT DATABASE() as db");
    
    // Find tables that use managed files
    $tables = $this->Db->getRows("
      SELECT 
        TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME, REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
      FROM
        INFORMATION_SCHEMA.KEY_COLUMN_USAGE
      WHERE
        REFERENCED_TABLE_SCHEMA = :db AND
        REFERENCED_TABLE_NAME = 'file' AND
        REFERENCED_COLUMN_NAME = 'id'",
      [":db" => $row->db]);
      
    // Find all files that are in use
    $files = [];
    foreach ($tables as $table) {
      $rows = $this->Db->getRows("
          SELECT ".$table->COLUMN_NAME." as id
          FROM ".$table->TABLE_NAME."
          WHERE ".$table->COLUMN_NAME." IS NOT NULL");
      foreach ($rows as $row)
        $files[] = $row->id;
    }
    if (empty($files))
      return 0;
    
    // Fetch and delete all permanent files that aren't in use
    $rows = $this->Db->getRows("
        SELECT id FROM `file`
        WHERE 
          status = 1 &&
          id NOT IN :ids",
        [":ids" => $files]);
    foreach ($rows as $row) {
      $File = $this->getEntity("File", $row->id);
      $File->delete();
    }
    
    return count($rows);
  }
  
}