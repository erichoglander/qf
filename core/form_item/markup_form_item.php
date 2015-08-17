<?php
class Markup_FormItem_Core extends FormItem {
	
	public $submit_data = false;

	public function render() {
		return $this->value;
	}

}