<?php
class Checkboxes_FormItem_Core extends FormItem {

	public function options() {
		return $this->options;
	}
	
	public function inputType() {
		return "checkbox";
	}

	public function getAttributes() {
		$attr = parent::getAttributes();
		$attr['name'].= "[]";
		return $attr;
	}

};