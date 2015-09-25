<?php
class Checkboxes_FormItem_Core extends FormItem {

	protected function options() {
		return $this->options;
	}
	
	/**
	* Bug in hhvm sometimes causing duplicate values.
	* See issue #1707, https://github.com/facebook/hhvm/issues/1706
	*/
	protected function postValue() {
		return array_unique(parent::postValue());
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