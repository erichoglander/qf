<?php
class Button_FormItem_Core extends FormItem {

  public $return_data = false;

  public function inputClass() {
    return parent::inputClass()." btn";
  }
  
  public function value($filter = true) {
    return $this->value;
  }

}