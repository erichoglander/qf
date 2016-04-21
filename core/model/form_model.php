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
      $class = newClass($cname, $this->Db, $this->Io, $structure);
      if (!$class)
        $class = new FormItem($this->Db, $this->Io, $structure);
    }
    else {
      $class = newClass($structure["form_item_class"], $this->Db, $this->Io, $structure);
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
    return newClass($structure["form_item_class"], $this->Db, $this->Io, $structure);
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
    return newClass($structure["form_item_class"], $this->Db, $this->Io, $structure);
  }

}