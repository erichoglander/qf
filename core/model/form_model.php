<?php
/**
 * Contains the form model
 */
/**
 * Form model
 * @author Eric Höglander
 */
class Form_Model_Core extends Model {
  
  /**
   * Get json formatted form item based on given structure
   * @param  array $structure
   * @return string
   */
  public function formItem($structure) {
    if (empty($structure["form_item_class"])) {
      $a = explode("_", $structure["type"]);
      $cname = "";
      foreach ($a as $b)
        $cname.= ucwords($b);
      $cname.= "_FormItem";
      $class = $this->newClass($cname, $structure);
      if (!$class)
        $class = $this->newClass("FormItem", $structure);
    }
    else {
      $class = $this->newClass($structure["form_item_class"], $structure);
    }
    return $class;
  }
  
  /**
   * Get json formatted file form item and attempt to upload file
   * @see    \File_FormItem_Core
   * @param  array $structure
   * @return string
   */
  public function uploadFile($structure) {
    $structure["submitted"] = true;
    return $this->newClass($structure["form_item_class"], $structure);
  }
  
  /**
   * Get json formatted file form item and remove file
   * @see    \File_FormItem_Core
   * @param  array $structure
   * @return string
   */
  public function removeFile($structure, $File) {
    $structure["value"] = 0;
    if ($File->get("status") == 0)
      $File->delete();
    return $this->newClass($structure["form_item_class"], $structure);
  }
  
  /**
   * Fetch rows for autocomplete
   * @see    \Autocomplete_FormItem_Core
   * @param  array $entity_type
   * @param  array $q The search string
   * @return string
   */
  public function autocomplete($entity_type, $q) {
    if (empty($q))
      return [];
    $Entity = $this->getEntity($entity_type);
    if (!$Entity)
      throw new Exception(t("Invalid entity type"));
    $query = [
      "from" => $Entity->tableName(),
      "cols" => ["id"],
    ];
    if ($Entity->hasField("title")) {
      $query["where"][] = "title LIKE :q";
      $query["vars"][":q"] = $q."%";
      $query["order"] = ["title ASC"];
    }
    else {
      $query["where"][] = "id LIKE :q";
      $query["vars"][":q"] = $q."%";
      $query["order"] = ["id DESC"];
    }
    $rows = $this->Db->getRows($query);
    $arr = [];
    foreach ($rows as $row) {
      $arr[] = [
        "title" => $this->autocompleteTitle($entity_type, $row->id),
        "value" => $row->id,
      ];
    }
    return $arr;
  }
  
  /**
   * Get the title for an autocompleted value
   * @see    \Autocomplete_FormItem_Core
   * @param  array $entity_type
   * @param  array $value
   * @return string
   */
  public function autocompleteTitle($entity_type, $value) {
    $Entity = $this->getEntity($entity_type, $value);
    if ($Entity->id())
      return xss($Entity->get("title", $Entity->id()));
    return null;
  }

}