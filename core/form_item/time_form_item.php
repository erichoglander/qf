<?php
class Time_FormItem_Core extends FormItem {
	
	public $validation = "time";


	protected function inputClass() {
		return parent::inputClass()." form-time timepicker-input";
	}
	
	protected function inputType() {
		return "text";
	}
	
	protected function getAttributes() {
		$attr = parent::getAttributes();
		$attr["autocomplete"] = "off";
		return $attr;
	}

}