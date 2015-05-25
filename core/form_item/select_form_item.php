<?php
class Select_FormItem_Core extends FormItem {
	
	protected function getAttributes() {
		$attr = parent::getAttributes();
		unset($attr["type"]);
		return $attr;
	}

};