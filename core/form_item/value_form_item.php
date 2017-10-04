<?php
class Value_FormItem_Core extends FormItem {
  
  protected function emptyValue($val) {
    return true;
  }
  
  public function itemValue() {
    return $this->value;
  }

  public function validated($req = true) {
    return true;
  }

  public function render() {
    return null;
  }

};