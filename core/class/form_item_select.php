<?php
class FormItemSelect extends FormItem {
	
	protected function getAttributes() {
		$attr = parent::getAttributes();
		unset($attr['type']);
		return $attr;
	}

};