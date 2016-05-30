<?php
class Editor_FormItem_Core extends FormItem {
  
  public $editor_config = [];
  public $editor_default = [];
  
  public function loadDefault() {
    $this->editor_default = [
      "format_tags" => "p",
    ];
  }
  
  protected function editorSrc() {
    return fileUrl("library/ckeditor/ckeditor/ckeditor.js");
  }

  protected function filter($value, $filter) {
    return preg_replace("/<\s*script[^>]+>[^<]+<\s*\/script\s*>/i", "", $value);
  }
  
  protected function preRenderInput(&$vars) {
    $config = $this->editor_config+$this->editor_default;
    $vars["editor_config"] = json_encode($config);
    if (empty($GLOBALS["editor_included"])) {
      $vars["editor_script"] = '<script src="'.$this->editorSrc().'"></script>';
      $GLOBALS["editor_included"] = true;
    }
  }

}