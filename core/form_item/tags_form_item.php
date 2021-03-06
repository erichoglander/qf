<?php
class Tags_FormItem_Core extends FormItem {
  
  public $autocomplete_uri;
  
  
  protected function itemValue() {
    return array_filter($this->Io->filter(explode(",", parent::itemValue()), "trim"));
  }
  
  protected function inputType() {
    return "text";
  }
  
  protected function inputClass() {
    return parent::inputClass()." form-tags";
  }
  
  protected function getAttributes() {
    $attr = parent::getAttributes();
    $attr["autocomplete"] = "off";
    $attr["uri"] = $this->autocomplete_uri;
    return $attr;
  }
  
}