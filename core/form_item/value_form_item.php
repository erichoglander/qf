<?php
class Value_FormItem_Core extends FormItem {
	
	public function itemValue() {
		return $this->value;
	}

	public function validated() {
		return true;
	}

	public function render() {
		return null;
	}

};