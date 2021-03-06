<?php
class Address_FormItem_Core extends FormItem {
  
  public $address_fields = ["line", "postal_code", "locality", "country"];
  public $address_country = "SE";
  public $address_countries;
  public $address_attributes;
  public $address_properties;
  public $label_placeholder = false;
  
  
  public function countries() {
    return countryList();
  }

  public function hasValue() {
    if ($this->multiple) {
      foreach ($this->items as $item) 
        if ($item->hasValue())
          return true;
    }
    else {
      foreach ($this->items as $item) 
        if ($item->hasValue() && $item->name != "country")
          return true;
    }
    return false;
  }
  
  
  protected function loadDefault() {
    $this->address_countries = $this->countries();
  }
  
  protected function preStructure(&$structure) {
    if (isset($structure["address_fields"]))
      $this->address_fields = $structure["address_fields"];
    if (isset($structure["address_properties"]))
      $this->address_properties = $structure["address_properties"];
    if (isset($structure["address_countries"]))
      $this->address_countries = $structure["address_countries"];
    if (isset($structure["address_attributes"]))
      $this->address_attributes = $structure["address_attributes"];
    if (isset($structure["label_placeholder"]))
      $this->label_placeholder = $structure["label_placeholder"];
    
    $structure["items"] = [];
    if (in_array("id", $this->address_fields)) {
      $structure["items"]["id"] = [
        "type" => "value",
      ];
    }
    if (in_array("co", $this->address_fields)) {
      $structure["items"]["co"] = [
        "type" => "text",
        "label" => t("C/o"),
        "filter" => ["strip_tags", "trim"],
      ];
    }
    if (in_array("line", $this->address_fields)) {
      $structure["items"]["line"] = [
        "type" => "text",
        "label" => t("Address"),
        "filter" => ["strip_tags", "trim"],
        "required" => true,
      ];
    }
    if (in_array("line2", $this->address_fields)) {
      $structure["items"]["line2"] = [
        "type" => "text",
        "label" => t("Address 2"),
        "filter" => ["strip_tags", "trim"],
      ];
    }
    if (in_array("postal_code", $this->address_fields)) {
      $structure["items"]["postal_code"] = [
        "type" => "text",
        "label" => t("Postal code"),
        "filter" => ["strip_tags", "trim"],
        "required" => true,
      ];
    }
    if (in_array("locality", $this->address_fields)) {
      $structure["items"]["locality"] = [
        "type" => "text",
        "label" => t("Locality"),
        "filter" => ["strip_tags", "trim"],
        "required" => true,
      ];
    }
    if (in_array("country", $this->address_fields)) {
      $structure["items"]["country"] = [
        "type" => "select",
        "label" => t("Country"),
        "options" => $this->address_countries,
        "value" => $this->address_country,
        "required" => true,
      ];
    }
    if ($this->label_placeholder) {
      foreach ($structure["items"] as $key => $item) {
        if ($item["type"] == "select") {
          $structure["items"][$key]["empty_option"] = "- ".$item["label"]." -";
        }
        else {
          $structure["items"][$key]["attributes"]["placeholder"] = $item["label"];
        }
        unset($structure["items"][$key]["label"]);
      }
    }
    if (!empty($this->address_attributes)) {
      foreach ($this->address_attributes as $key => $attributes) {
        if (isset($structure["items"][$key])) {
          foreach ($attributes as $k => $attr)
            $structure["items"][$key]["attributes"][$k] = $attr;
        }
      }
    }
    if (!empty($this->address_properties)) {
      foreach ($this->address_properties as $key => $properties) {
        if (isset($structure["items"][$key])) {
          foreach ($properties as $k => $prop)
            $structure["items"][$key][$k] = $prop;
        }
      }
    }
  }
  
}