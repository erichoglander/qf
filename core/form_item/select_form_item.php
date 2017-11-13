<?php
class Select_FormItem_Core extends FormItem {
  
  public $val_class = false;
  
  
  protected function getAttributes() {
    $attr = parent::getAttributes();
    unset($attr["type"]);
    return $attr;
  }
  
  protected function preRenderInput(&$vars) {
    $vars["val_class"] = $this->val_class;
  }

};