<?php
class Slider_FormItem_Core extends FormItem {
  
  public $slider_min = 0;
  public $slider_max = 100;
  public $slider_round = 1;
  public $slider_suffix;
  public $slider_value_follow = false;
  public $validation = "float";
  
  protected function itemClass() {
    $class = parent::itemClass();
    if ($this->template == "dropdown")
      $class.= " form-slider-dropdown";
    return $class;
  }
  
  protected function getAttributes() {
    $attr = parent::getAttributes();
    $attr["slider_min"] = $this->slider_min;
    $attr["slider_max"] = $this->slider_max;
    $attr["slider_round"] = $this->slider_round;
    $attr["slider_suffix"] = $this->slider_suffix;
    $attr["slider_value_follow"] = ($this->slider_value_follow ? 1 : 0);
    unset($attr["type"]);
    return $attr;
  }
  
}