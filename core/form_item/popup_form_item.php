<?php
class Popup_FormItem extends FormItem {
  
  public $multiple_new_empty = false;
  public $add_callback = "formPopupAddOpen(item, new_item, r)";
  public $popup_callback;
  public $popup_close;
  public $popup_size = "medium";
  public $popup_button;
  
  
  public function loadDefault() {
    $this->popup_button = t("Open");
  }
  
  public function renderPopupButton() {
    if ($this->multiple)
      return null;
    $data = $this->structure;
    $data["form_item_class"] = get_class($this);
    $json = htmlspecialchars(json_encode($data), ENT_QUOTES);
    return '<div class="form-button form-popup-button btn" onclick="formPopupButton(this, '.$json.')">'.$this->popup_button.'</div>';
  }
  
  public function getItemAttributes() {
    $attr = parent::getItemAttributes();
    $attr["name"] = ($this->parent_multiple ? $this->parent_name : $this->name);
    $attr["size"] = $this->popup_size;
    if ($this->popup_close)
      $attr["close"] = $this->popup_close;
    if ($this->popup_callback)
      $attr["callback"] = $this->popup_callback;
    return $attr;
  }
  
  
  protected function preRender(&$vars) {
    $vars["popup_button"] = $this->renderPopupButton();
  }
  
  
}