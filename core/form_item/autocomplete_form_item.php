<?php
class Autocomplete_FormItem_Core extends FormItem {
  
  public $autocomplete_uri;
  public $autocomplete_entity;
  
  protected $FormModel;
  
  
  public function value($filter = true) {
    $value = parent::value($filter);
    if (!is_array($value))
      $value = ["value" => $value];
    if (!$this->submitted && !$this->multiple)
      $value["title"] = $this->autocompleteTitle($value);
    return $value;
  }
  
  
  protected function inputType() {
    return "text";
  }
  
  protected function inputClass() {
    return parent::inputClass()." form-autocomplete";
  }
  
  protected function itemClass() {
    return parent::itemClass().(!$this->emptyValue($this->value()) ? " has-value" : "");
  }
  
  protected function emptyValue($val) {
    return empty($val["value"]);
  }
  
  protected function autocompleteUri() {
    if ($this->autocomplete_uri)
      return $this->autocomplete_uri;
    else if ($this->autocomplete_entity)
      return "form/autocomplete/".$this->autocomplete_entity;
    return null;
  }
  
  protected function autocompleteTitle($value) {
    if (array_key_exists("title", $value))
      return $value["title"];
    if ($this->autocomplete_entity) {
      if (empty($value["value"]))
        return null;
      if (!$this->FormModel)
        $this->FormModel = $this->getModel("Form");
      return xss($this->FormModel->autocompleteTitle($this->autocomplete_entity, $value["value"]));
    }
    return null;
  }
  
  protected function getAttributes() {
    $attr = parent::getAttributes();
    $attr["autocomplete"] = "off";
    $attr["name"].= "[title]";
    $attr["uri"] = $this->autocompleteUri();
    return $attr;
  }
  
}