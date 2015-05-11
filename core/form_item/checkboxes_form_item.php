<?php
class Checkboxes_FormItem extends FormItem {
	
	public function getAttributes() {
		$attr = parent::getAttributes();
		$attr['name'].= "[]";
	}

};