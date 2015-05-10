<?php
class Textarea_FormItem extends FormItem {
	
	protected function getAttributes() {
		$attr = parent::getAttributes();
		unset($attr['type']);
		return $attr;
	}
	
};