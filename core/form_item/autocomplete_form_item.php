<?php
class Autocomplete_FormItem_Core extends FormItem {
	
	protected $autocomplete_uri;
	
	
	protected function inputType() {
		return "text";
	}
	
	protected function inputClass() {
		return parent::inputClass()." form-autocomplete";
	}
	
	protected function itemClass() {
		return parent::itemClass().($this->value() ? " has-value" : "");
	}
	
	protected function itemValue() {
		if (!$this->submitted)
			$data = $this->value;
		else
			$data = $this->postValue();
		if (array_key_exists("value", $data))
			return $data["value"];
		else
			return null;
	}
	
	protected function getAttributes() {
		$attr = parent::getAttributes();
		$attr["name"].= "[title]";
		$attr["uri"] = $this->autocomplete_uri;
		return $attr;
	}
	
}