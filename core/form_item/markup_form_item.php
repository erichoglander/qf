<?php
class Markup_FormItem extends FormItem {
	
	public $submit_data = false;

	public function render() {
		return $this->value;
	}

}