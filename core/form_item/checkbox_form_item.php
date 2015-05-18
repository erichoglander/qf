<?php
class Checkbox_FormItem_Core extends FormItem {
	
	protected function itemValue() {
		$value = parent::itemValue();
		if (!$value || $value == "off")
			$value = 0;
		else
			$value = 1;
		return $value;
	}

};