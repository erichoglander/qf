<?php
class Date_FormItem_Core extends FormItem {
  
  public $validation = "date";


  protected function inputClass() {
    return parent::inputClass()." form-date datepicker-input";
  }
  
  protected function inputType() {
    return "text";
  }
  
  protected function getAttributes() {
    $attr = parent::getAttributes();
    $attr["autocomplete"] = "off";
    return $attr;
  }

}