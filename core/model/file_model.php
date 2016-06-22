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
   * Upload a managed file
   * @param  array $file Data from $_FILES
   * @param  array $opt  Upload options
   * @return \File_Entity_Core
   */
  public function upload($file, $opt = []) {
    $opt+= [
      "dir" => "public",
      "validate" => false,
      "status" => 0,
    ];
    if (!empty($opt["validate"]))
      $this->validateUpload($file, $opt);
    $info = pathinfo($file["name"]);
    $path = ($opt["dir"] == "private" ? PRIVATE_PATH : PUBLIC_PATH)."/";
    $uri = ($opt["folder"] ? $opt["folder"]."/" : "");
    $folder = substr($path.$uri, 0, -1);
    if (!file_exists($folder))
      mkdir($folder, 0774, true);
    $name = $this->Io->filter($info["filename"], "filename");
    $ext = strtolower($this->Io->filter($info["extension"], "alphanum"));
    for ($fname = $name.".".$ext, $i = 0; file_exists($path.$uri.$fname); $fname = $name."-".$i.".".$ext, $i++);
    if (!move_uploaded_file($file["tmp_name"], $path.$uri.$fname)) {
      addlog("file", "Insufficient directory permissions", $path.$uri.$fname, "error");
      throw new Exception(t("Insufficient directory permissions, contact administrator"));
    }
    $File = $this->getEntity("File");
    $File->set("dir", $opt["dir"]);
    $File->set("name", $name);
    $File->set("uri", $uri.$fname);
    $File->set("extension", $ext);
    $File->set("status", $opt["status"]);
    if (!$File->save())
      throw new Exception(t("An error occurred while saving file"));
    $_SESSION["file_uploaded"][] = $File->id();
    return $File;
  }
  
  /**
   * Verify a file upload
   * Throws exception on error
   * @param  array $file Data from $_FILES
   * @param  array $opt  Upload options
   */
  public function validateUpload($file, $opt = []) {
    $opt+= [
      "blacklist" => ["php", "phtml", "htaccess"],
      "max_size" => null,
    ];
    $errors = [
      UPLOAD_ERR_INI_SIZE => t("File is too big (server file limit)"),
      UPLOAD_ERR_FORM_SIZE => t("File is too big (server form limit)"),
      UPLOAD_ERR_PARTIAL => t("The file was only partially upload, please try again"),
      UPLOAD_ERR_NO_FILE => t("No file was uploaded"),
      UPLOAD_ERR_NO_TMP_DIR => t("Missing temp folder, contact administrator"),
      UPLOAD_ERR_CANT_WRITE => t("Can't write file to disk, contact administrator"),
      UPLOAD_ERR_EXTENSION => t("Upload stopped by a php extension"),
    ];
    if (isset($errors[$file["error"]]))
      $this->fileError($errors[$file["error"]]);
    $info = pathinfo($file["name"]);
    $ext = strtolower($this->Io->filter($info["extension"], "alphanum"));
    if (!empty($opt["blacklist"]) && in_array($ext, $opt["blacklist"]))
      $this->fileError(t("Unallowed file extension"));
    if (!empty($opt["whitelist"]) && !in_array($ext, $opt["whitelist"]))
      $this->fileError(t("Unallowed file extension. Only :ext", "en", [":ext" => implode(", ", $opt["whitelist"])]));
    if ($opt["max_size"] && $opt["max_size"] < filesize($file["tmp_name"]))
      $this->fileError(t("File is too big. Max size is :size", "en", [":size" => formatBytes($opt["max_size"])]));
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
  
  
  /**
   * Logs a file error and throws an exception
   * @param string
   */
  protected function fileError($e) {
    addlog("file", $e, null, "error");
    throw new Exception($e);
  }
  
}