<?php
class Submit_FormItem_Core extends FormItem {

	public $return_data = false;

	public function inputClass() {
		return parent::inputClass()." btn btn-primary";
	}
	
	public function itemValue() {
		return $this->value;
	}

}