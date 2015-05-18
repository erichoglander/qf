<?php
class Submit_FormItem_Core extends FormItem {

	public $return_data = false;
	
	public function itemValue() {
		return $this->value;
	}

}