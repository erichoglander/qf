<?php
class Color_FormItem_Core extends FormItem {
  
  public $filter = "color_hex";
  public $jscolor_config = [];
  public $jscolor_default = [];
 
 
  public function loadDefault() {
    $this->jscolor_default = [
      "required" => false,
      "uppercase" => false,
    ];
  }
  
  public function getAttributes() {
    $arr = parent::getAttributes();
    $config = $this->jscolor_config+$this->jscolor_default;
    $arr["data-jscolor"] = xss(json_encode($config));
    return $arr;
  }
 
  public function inputClass() {
    return parent::inputClass()." jscolor";
  }
  
  public function inputType() {
    return "text";
  }
  
  public function js() {
    $path = fileUrl("library/jscolor/jscolor/jscolor.min.js");
    if ($path)
      return [$path];
    return [];
  }
  
}