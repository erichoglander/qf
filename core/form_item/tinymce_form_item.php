<?php
class Tinymce_FormItem_Core extends FormItem {
  
  public $tinymce_config = [];
  public $tinymce_default = [];
  public $tinymce_extra = [];
  public $tinymce_upload = false;
  
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
      "relative_urls" => false,
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
  
  public function uploadResponse($File) {
    return json_encode(["location" => $File->url()]);
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
    if ($this->tinymce_upload) {
      if (empty($config["images_upload_url"]))
        $config["images_upload_url"] = url("form/itemupload/tinymce");
      if (!array_key_exists("paste_data_images", $config))
        $config["paste_data_images"] = true;
    }
    $attr = $this->getAttributes();
    $config["selector"] = "#".$attr["id"];
    $json = json_encode($config);
    $json = str_replace('"<', '', $json);
    $json = str_replace('>"', '', $json);
    $vars["tinymce_config"] = $json;
  }
  
}