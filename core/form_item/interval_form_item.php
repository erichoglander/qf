<?php
class Interval_FormItem_Core extends FormItem {
	
	public $interval_min = 0;
	public $interval_max = 100;
	public $interval_round = 1;
	public $interval_suffix;
	
	protected function getAttributes() {
		$attr = parent::getAttributes();
		$attr["interval_min"] = $this->interval_min;
		$attr["interval_max"] = $this->interval_max;
		$attr["interval_round"] = $this->interval_round;
		$attr["interval_suffix"] = $this->interval_suffix;
		unset($attr["name"]);
		unset($attr["type"]);
		return $attr;
	}
	
	protected function itemValue() {
		$value = $this->postValue();
		for ($i=0; $i<2; $i++) {
			if (strpos($this->interval_round, ".") !== false)
				$value[$i] = (float) $value[$i];
			else
				$value[$i] = (int) $value[$i];
			$value[$i] = min(max($value[$i], $this->interval_min), $this->interval_max);
		}
		return $value;
	}
	
}