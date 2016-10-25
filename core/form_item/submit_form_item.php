<?php
class Submit_FormItem_Core extends FormItem {

  public $submit_data = false;

  public function inputClass() {
    return parent::inputClass()." btn btn-primary";
  }
  
  public function inputValue() {
    return $this->value;
  }
  
  public function itemValue() {
    return $this->postValue() !== null;
  }

}