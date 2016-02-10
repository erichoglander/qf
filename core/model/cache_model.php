<?php
/**
 * Contains the cache model
 */
/**
 * Cache model
 * @author Eric Höglander
 */
class Cache_Model_Core extends Model {

  /**
   * Delete a data cache
   * @param string $name
   */
  public function delete($name) {
    $this->Db->delete("cache", ["name" => $name]);
  }
  
  /**
   * Get all data caches for listing
   * @return array
   */
  public function getCaches() {
    $rows = $this->Db->getRows("
        SELECT name, expire, OCTET_LENGTH(data) as size FROM `cache`
        ORDER BY name ASC");
    return $rows;
  }

}