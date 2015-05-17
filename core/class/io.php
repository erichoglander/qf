<?php
/*
	Class to filter and validate values
*/
class Io_Core {
	
	public function validate($value, $validation) {
		if (is_array($value)) {
			foreach ($value as $i => $val)
				$value[$i] = $this->validate($val, $validation);
		}
		else if (is_array($validation)) {
			foreach ($validation as $v)
				$value = $this->validate($value, $v);
		}
		else {
			$fname = "validate";
			$arr = explode("_", $validation);
			foreach ($arr as $a)
				$fname.= ucwords($a);
			if (is_callable([$this, $fname]))
				return $this->$fname($value);
			else
				return $value;
		}
		return $value;
	}
	
	public function filter($value, $filter) {
		if (is_array($value)) {
			foreach ($value as $i => $val)
				$value[$i] = $this->filter($val, $filter);
		}
		else if (is_array($filter)) {
			foreach ($filter as $f)
				$value = $this->filter($value, $f);
		}
		else {
			$fname = "filter";
			$arr = explode("_", $filter);
			foreach ($arr as $a)
				$fname.= ucwords($a);
			if (is_callable([$this, $fname]))
				return $this->$fname($value);
			else
				return $value;
		}
		return $value;
	}


	protected function regexBool($regex, $value) {
		return (preg_match($regex, $value) ? true: false);
	}

	protected function validateInt($value) {
		return $this->regexBool("/^\-?[0-9]+$/", $value);
	}
	protected function validateUint($value) {
		return $this->regexBool("/^[0-9]+$/", $value);
	}
	protected function validateFloat($value) {
		return $this->regexBool("/^\-?[0-9]+\.?[0-9]*$/", $value);
	}
	protected function validateUfloat($value) {
		return $this->regexBool("/^[0-9]+\.?[0-9]*$/", $value);
	}
	protected function validateDate($value) {
		return $this->regexBool("/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/", $value);
	}
	protected function validateEmail($value) {
		return (filter_var($value, FILTER_VALIDATE_EMAIL) ? true : false);
	}
	protected function validateIp($value) {
		return (filter_var($value, FILTER_VALIDATE_IP) ? true : false);
	}

	protected function filterTrim($value) {
		return trim($value);
	}
	protected function filterXss($value) {
		return htmlspecialchars($value, ENT_QUOTES);
	}
	protected function filterStripTags($value) {
		return strip_tags($value);
	}
	protected function filterInt($value) {
		return (int) $value;
	}
	protected function filterUint($value) {
		return abs((int) $value);
	}
	protected function filterFloat($value) {
		return (float) $value;
	}
	protected function filterUfloat($value) {
		return abs((float) $value);
	}
	protected function filterDouble($value) {
		return (double) $value;
	}
	protected function filterUdouble($value) {
		return abs((double) $value);
	}

};