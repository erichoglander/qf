<?php
class Checkboxes_FormItem_Core extends FormItem {

	protected function options() {
		return $this->options;
	}
	
	protected function inputType() {
		return "checkbox";
	}

	protected function getAttributes() {
		$attr = parent::getAttributes();
		$attr["name"].= "[]";
		return $attr;
	}

};