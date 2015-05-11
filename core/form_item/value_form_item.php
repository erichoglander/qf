<?php
class Value_FormItem extends FormItem {
	
	public function value() {
		return $this->value;
	}

	public function validated() {
		return true;
	}

	public function render() {
		return null;
	}

};