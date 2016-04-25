<?php
class Fieldset_FormItem_Core extends FormItem {
  
  public $collapsible = false;
  public $collapsed = false;
  
  
  protected function loadStructure($structure) {
    parent::loadStructure($structure);
    $this->item_structure["collapsed"] = false;
  }
  
  protected function itemClass() {
    $class = parent::itemClass();
    if ($this->collapsible && !$this->multiple) {
      $class.= " form-collapsible";
      $class.= ($this->collapsed && !$this->childError() ? " collapsed" : " expanded");
    }
    return $class;
  }
  
}