<?php
/**
 * Containers the alias model
 */
/**
 * Alias model
 * @author Eric Höglander
 */
class Alias_Model_Core extends Model {
  
  /**
   * Attempts to create an alias
   * @param  string $path
   * @param  string $alias
   * @param  bool   $unique If true, deletes any existing aliases for $path
   * @return \Alias_Entity_Core
   */
  public function createAlias($path, $alias, $unique = false) {
    $alias = $this->Io->filter($alias, "alias");
    $row = $this->Db->getRow("
        SELECT * FROM `alias`
        WHERE alias = :alias",
        [":alias" => $alias]);
    if ($row) {
      if ($row->path != $path)
        return null;
      if ($row->status == 0)
        $this->Db->update("alias", ["status" => 1], ["id" => $row->id]);
      return $this->getEntity("Alias", $row->id);
    }
    if ($unique)
      $this->Db->delete("alias", ["path" => $path]);
    $Alias = $this->getEntity("Alias");
    $Alias->set("path", $path);
    $Alias->set("alias", $alias);
    if (!$Alias->save())
      return null;
    return $Alias;
  }
  
  /**
   * Batch create and/or update aliases for an entity type
   * @param  string $entity_type
   * @param  string $action create/update/all
   * @return int
   */
  public function batchAliases($entity_type, $action) {
    $n = 0;
    $Head = $this->getEntity($entity_type);
    $rows = $this->Db->getRows("SELECT id FROM `".$Head->tableName()."`");
    foreach ($rows as $row) {
      $Entity = $this->getEntity($entity_type, $row->id);
      $update = $action == "all";
      if ($action == "delete") {
        $Entity->deleteAlias();
        $n++;
      }
      else if (in_array($action, ["create", "update"])) {
        $path = $Entity->getPath();
        $row = $this->Db->getRow("
            SELECT * FROM `alias`
            WHERE path = :path",
            [":path" => $path]);
        $update = 
            $row && $action == "update" || 
            !$row && $action == "create";
      }
      if ($update && $Entity->createAlias())
        $n++;
    }
    return $n;
  }
  
  /**
   * Add a new alias
   * @see    editAlias
   * @param  array $values
   * @return bool
   */
  public function addAlias($values) {
    $Alias = $this->getEntity("Alias");
    return $this->editAlias($Alias, $values);
  }

  /**
   * Edit an alias
   * @param  \Alias_Entity_Core $Alias
   * @param  array              $values
   * @return bool
   */
  public function editAlias($Alias, $values) {
    foreach ($values as $key => $value)
      $Alias->set($key, $value);
    return $Alias->save();
  }

  /**
   * Delete an alias
   * @param  \Alias_Entity_Core $Alias
   * @return bool
   */
  public function deleteAlias($Alias) {
    return $Alias->delete();
  }

  /**
   * Get all aliases
   * @return array
   */
  public function getAliases() {
    $rows = $this->Db->getRows("SELECT id FROM `alias` ORDER BY alias ASC");
    $aliases = [];
    foreach ($rows as $row)
      $aliases[] = $this->getEntity("Alias", $row->id);
    return $aliases;
  }
  
  /**
   * Database query for alias search
   * @param  array $values Search parameters
   * @return array Contains sql-query and vars
   */
  public function listSearchQuery($values) {
    $sql = "SELECT id FROM `alias`";
    $vars = [];
    if (!empty($values["q"])) {
      $sql.= " WHERE path LIKE :q || alias LIKE :q";
      $vars[":q"] = "%".$values["q"]."%";
    }
    return [$sql, $vars];
  }
  /**
   * Get number of aliases that matches the search
   * @see    listSearchQuery
   * @param  array $values   Search parameters
   * @return int
   */
  public function listSearchNum($values = []) {
    list($sql, $vars) = $this->listSearchQuery($values);
    return $this->Db->numRows($sql, $vars);
  }
  /**
   * Search for aliases
   * @see    listSearchQuery
   * @param  array $values Search parameters
   * @param  int   $start
   * @param  int   $stop
   * @return array
   */
  public function listSearch($values = [], $start = 0, $stop = 30) {
    list($sql, $vars) = $this->listSearchQuery($values);
    $sql.= " ORDER BY path ASC LIMIT ".$start.", ".$stop;
    $rows = $this->Db->getRows($sql, $vars);
    $list = [];
    foreach ($rows as $row)
      $list[] = $this->getEntity("Alias", $row->id);
    return $list;
  }

}