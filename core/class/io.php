<?php
/*
	Class to filter and validate values
*/
class Io_Core {

	protected $error;


	public function getError() {
		return t($this->error);
	}
	public function setError($e) {
		$this->error = $e;
	}
	
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


	protected function validateNumber($value) {
		if (preg_match("/^\-?[0-9]+[\.|\,]?[0-9]*$/", $value))
			return true;
		$this->setError("Invalid number");
		return false;
	}
	protected function validateInt($value) {
		if (preg_match("/^\-?[0-9]+$/", $value))
			return true;
		$this->setError("Invalid integer");
		return false;
	}
	protected function validateUint($value) {
		if (preg_match("/^[0-9]+$/", $value))
			return true;
		$this->setError("Invalid positive integer");
		return false;
	}
	protected function validateFloat($value) {
		if (preg_match("/^\-?[0-9]+\.?[0-9]*$/", $value))
			return true;
		$this->setError("Invalid decimal number");
		return false;
	}
	protected function validateUfloat($value) {
		if (preg_match("/^[0-9]+\.?[0-9]*$/", $value))
			return true;
		$this->setError("Invalid positive decimal number");
		return false;
	}
	protected function validateDate($value) {
		if (preg_match("/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/", $value))
			return true;
		$this->setError("Invalid date (YYYY-MM-DD)");
		return false;
	}
	protected function validateTime($value) {
		if (preg_match("/^([0-9]{2}\:){1,2}[0-9]{2}$/", $value)) 
			return true;
		$this->setError("Invalid time (HH:MM or HH:MM:SS)");
		return false;
	}
	protected function validateEmail($value) {
		if (filter_var($value, FILTER_VALIDATE_EMAIL))
			return true;
		$this->setError("Invalid e-mail address");
		return false;
	}
	protected function validateIp($value) {
		if (filter_var($value, FILTER_VALIDATE_IP))
			return true;
		$this->setError("Invalid IP-address");
		return false;
	}
	protected function validateUsername($value) {
		if ($this->regexBool("/^[a-z0-9\-\_\@\.\*\^\~]+$/i"))
			return true;
		$this->setError("Illegal username");
		return false;
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