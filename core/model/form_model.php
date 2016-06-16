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
   * @param  array             $structure
   * @param  \File_Entity_Core $File
   * @return string
   */
  public function removeFile($structure, $File) {
    $structure["value"] = 0;
    if ($File->get("status") == 0)
      $File->delete();
    return $this->newClass($structure["form_item_class"], $structure);
  }
  
  /**
   * Upload a file from an editor
   * @see    \File_FormItem_Core
   * @param  string $item_type
   * @return string
   */
  public function itemUpload($item_type) {
    $class = $item_type."_FormItem";
    $structure = ["type" => $item_type, "name" => "nothing"];
    $FormItem = $this->newClass($class, $structure);
    $file = current($_FILES);
    if (!$FormItem || empty($file))
      return t("An error occurred");
    $FileModel = $this->getModel("File");
    $opt = [
      "validate" => true,
      "status" => 1,
      "folder" => $item_type,
    ];
    try {
      $File = $FileModel->upload($file, $opt);
    }
    catch (Exception $e) {
      return $e->getMessage();
    }
    if ($File && is_callable([$FormItem, "uploadResponse"]))
      return $FormItem->uploadResponse($File);
    return "OK";
  }
  
  /**
   * Fetch renderable rows for autocomplete
   * @see    \Autocomplete_FormItem_Core
   * @param  array $entity_type
   * @param  array $q The search string
   * @return array
   */
  public function autocomplete($entity_type, $q) {
    if (empty($q))
      return [];
    $Entity = $this->getEntity($entity_type);
    if (!$Entity)
      throw new Exception(t("Invalid entity type"));
    $rows = $this->autocompleteRows($entity_type, $q);
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
   * Fetch database rows with ids for autocomplete
   * @see    \Autocomplete_FormItem_Core
   * @param  array $entity_type
   * @param  array $q The search string
   * @return array
   */
  public function autocompleteRows($entity_type, $q) {
    if (is_callable([$this, "autocompleteRows".$entity_type]))
      return call_user_func([$this, "autocompleteRows".$entity_type], $q);
    $Entity = $this->getEntity($entity_type);
    $query = [
      "from" => $Entity->tableName(),
      "cols" => ["id"],
      "limit" => [15],
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
    return $this->Db->getRows($query);
  }
  
  /**
   * Get the title for an autocompleted value
   * @see    \Autocomplete_FormItem_Core
   * @param  array $entity_type
   * @param  array $value
   * @return string
   */
  public function autocompleteTitle($entity_type, $value) {
    if (is_callable([$this, "autocompleteTitle".$entity_type]))
      return call_user_func([$this, "autocompleteTitle".$entity_type], $value);
    $Entity = $this->getEntity($entity_type, $value);
    if (!$Entity->id())
      return null;
    return $Entity->get("title", $Entity->id());
  }
  
  /**
   * Fetch users for autocomplete
   * @see    \Autocomplete_FormItem_Core
   * @param  array $q The search string
   * @return array
   */
  public function autocompleteRowsUser($q) {
    $Entity = $this->getEntity($entity_type);
    $query = [
      "from" => "user",
      "cols" => ["id"],
      "where" => ["name LIKE :q || email LIKE :q"],
      "order" => ["id DESC"],
      "limit" => [15],
      "vars" => [":q" => $q."%"],
    ];
    return $this->Db->getRows($query);
  }
  
  /**
   * Get the title for an autocompleted value
   * @see    \Autocomplete_FormItem_Core
   * @param  array $value
   * @return string
   */
  public function autocompleteTitleUser($value) {
    $User = $this->getEntity("User", $value);
    if (!$User->id())
      return null;
    return $User->get("name")." - ".$User->get("email");
  }

}