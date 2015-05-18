<?php
class Checkboxes_FormItem_Core extends FormItem {
	
	public function getAttributes() {
		$attr = parent::getAttributes();
		$attr['name'].= "[]";
	}

};