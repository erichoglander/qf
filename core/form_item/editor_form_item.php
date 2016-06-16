<?php
class Editor_FormItem_Core extends FormItem {
  
  public $editor_config = [];
  public $editor_default = [];
  public $editor_upload = false;
  
  public function loadDefault() {
    $this->editor_default = [
      "format_tags" => "p",
    ];
  }
  
  public function uploadResponse($File) {
   return '
      <script type="text/javascript">
        window.parent.CKEDITOR.tools.callFunction('.$_GET["CKEditorFuncNum"].', "'.$File->url().'");
      </script>';
  }
  
  
  protected function editorSrc() {
    return fileUrl("library/ckeditor/ckeditor/ckeditor.js");
  }

  protected function filter($value, $filter) {
    return preg_replace("/<\s*script[^>]+>[^<]+<\s*\/script\s*>/i", "", $value);
  }
  
  protected function preRenderInput(&$vars) {
    $config = $this->editor_config+$this->editor_default;
    if ($this->editor_upload && empty($config["filebrowserUploadUrl"]))
      $config["filebrowserUploadUrl"] = url("form/itemupload/editor");
    $vars["editor_config"] = json_encode($config);
    if (empty($GLOBALS["editor_included"])) {
      $vars["editor_script"] = '<script src="'.$this->editorSrc().'"></script>';
      $GLOBALS["editor_included"] = true;
    }
  }

}