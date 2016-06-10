<?php
class Tinymce_FormItem_Core extends FormItem {
  
  public $tinymce_config = [];
  public $tinymce_default = [];
  public $tinymce_extra = [];
  
  public function loadDefault() {
    $this->tinymce_default = [
      "plugins" => "lists link contextmenu autoresize paste",
      "autoresize_top_margin" => 0,
      "autoresize_bottom_margin" => 10,
      "autoresize_min_height" => 100,
      "content_css" => fileUrl("library/tinymce/css/default.css"),
      "menubar" => false,
      "statusbar" => false,
      "toolbar" => "bold italic | bullist numlist | link",
    ];
  }
  
  public function js() {
    return ["//cdn.tinymce.com/4/tinymce.min.js"];
  }
  
  public function getAttributes() {
    $attr = parent::getAttributes();
    if (empty($attr["id"]))
      $attr["id"] = "tinymce_".str_replace(["[", "]"], ["__", ""], $this->inputName());
    return $attr;
  }

  protected function filter($value, $filter) {
    return preg_replace("/<\s*script[^>]+>[^<]+<\s*\/script\s*>/i", "", $value);
  }
  
  protected function preRenderInput(&$vars) {
    $config = $this->tinymce_config+$this->tinymce_default;
    if (!empty($this->tinymce_extra)) {
      foreach ($this->tinymce_extra as $key => $val)
        $config[$key].= $val;
    }
    $attr = $this->getAttributes();
    $config["selector"] = "#".$attr["id"];
    $vars["tinymce_config"] = json_encode($config);
  }
  
}