<?php
class Popup_FormItem extends FormItem {
  
  public $multiple_new_empty = false;
  public $add_callback = "formPopupAddOpen(item, new_item, r)";
  public $popup_callback;
  public $popup_close;
  public $popup_size = "medium";
  public $popup_button;
  public $preview_template;
  
  
  public function loadDefault() {
    $this->popup_button = t("Open");
  }
  
  public function preview() {
    if ($this->multiple)
      return null;
    if ($this->preview_template) {
      $path = $this->templatePreviewPath();
      $vars = [
        "name" => $this->name,
        "parent_name" => $this->parent_name,
        "label" => $this->label,
        "structure" => $this->structure,
        "value" => $this->value(),
      ];
      if (is_callable([$this, "preRenderPreview"]))
        $this->preRenderPreview($vars);
      return '<div class="popup-preview" onclick="formPopupButton(this, '.$this->popupJson().')">'.renderTemplate($path, $vars).'</div>';
    }
    return null;
  }
  
  public function renderPopupButton() {
    if ($this->multiple)
      return null;
    return '<div class="form-button form-popup-button btn" onclick="formPopupButton(this, '.$this->popupJson().')">'.$this->popup_button.'</div>';
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
    $vars["preview"] = $this->preview();
    $vars["popup_button"] = $this->renderPopupButton();
  }

  protected function templatePreviewPath() {
    $prefix = "form_popup_preview";
    $d = "__";
    $names = [];
    if ($this->preview_template)
      $names[] = $prefix."__".$this->preview_template;
    $names[] = $prefix;
    foreach ($names as $name) {
      $path = DOC_ROOT."/extend/template/form/".$name.".php";
      if (file_exists($path))
        return $path;
    }
    foreach ($names as $name) {
      $path = DOC_ROOT."/core/template/form/".$name.".php";
      if (file_exists($path))
        return $path;
    }
    return null;
  }
  
  protected function popupJson() {
    $data = $this->structure;
    $data["form_item_class"] = get_class($this);
    return htmlspecialchars(json_encode($data), ENT_QUOTES);
  }
  
}