<?php
/**
 * Contains the redirect model
 */
/**
 * Redirect model
 * @author Eric Höglander
 */
class Redirect_Model_Core extends Model {
  
  /**
   * Add a redirect
   * @param  array $values
   * @return bool
   */
  public function addRedirect($values) {
    $Redirect = $this->getEntity("Redirect");
    return $this->editRedirect($Redirect, $values);
  }

  /**
   * Save a redirect
   * @param  \Redirect_Entity_Core $Redirect
   * @param  array                 $values
   * @return bool
   */
  public function editRedirect($Redirect, $values) {
    foreach ($values as $key => $value)
      $Redirect->set($key, $value);
    return $Redirect->save();
  }

  /**
   * Delete a redirect
   * @param  \Redirect_Entity_Core $Redirect
   * @return bool
   */
  public function deleteRedirect($Redirect) {
    return $Redirect->delete();
  }

  /**
   * Get all redirects
   * @return array
   */
  public function getRedirectes() {
    $rows = $this->Db->getRows("SELECT id FROM `redirect` ORDER BY source ASC");
    $redirects = [];
    foreach ($rows as $row)
      $redirects[] = $this->getEntity("Redirect", $row->id);
    return $redirects;
  }
  
  /**
   * Creates a sql-query for a search
   * @param  array $values  Search parameters
   * @return array Contains sql-query and vars
   */
  public function listSearchQuery($values = []) {
    $sql = "SELECT id FROM `redirect`";
    $vars = [];
    if (!empty($values["q"])) {
      $sql.= " WHERE source LIKE :q || target LIKE :q";
      $vars[":q"] = "%".$values["q"]."%";
    }
    return [$sql, $vars];
  }
  /**
   * Number of redirects matching a search
   * @see    listSearchQuery
   * @param  array $values Search parameters
   * @return int
   */
  public function listSearchNum($values = []) {
    list($sql, $vars) = $this->listSearchQuery($values);
    return $this->Db->numRows($sql, $vars);
  }
  /**
   * Search for redirects
   * @see    listSearchQuery
   * @param  array $values Search parameters
   * @param  int   $start
   * @param  int   $stop
   * @return array
   */
  public function listSearch($values = [], $start = 0, $stop = 30) {
    list($sql, $vars) = $this->listSearchQuery($values);
    $sql.= " ORDER BY source ASC LIMIT ".$start.", ".$stop;
    $rows = $this->Db->getRows($sql, $vars);
    $list = [];
    foreach ($rows as $row)
      $list[] = $this->getEntity("Redirect", $row->id);
    return $list;
  }

}