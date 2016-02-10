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
  
}