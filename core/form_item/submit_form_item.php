<?php
class Submit_FormItem extends FormItem {

	public $return_data = false;
	
	public function itemValue() {
		return $this->value;
	}

}