<?php
/**
 * Contains the Io class
*/
/**
 * Io class
 * @author Eric Höglander
 */
class Io_Core {

	/**
	 * Contains error message from validation
	 * @see validate
	 * @var string
	 */
	protected $error;


	/**
	 * Getter for $error
	 * @return string
	 */
	public function getError() {
		return $this->error;
	}

	/**
	 * Setter for $error
	 * @param string $e
	 */
	public function setError($e) {
		$this->error = $e;
	}
	
	/**
	 * Validates data with one or many validations
	 * @param  mixed $value
	 * @param  string|array $validation
	 * @return bool
	 */
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
	
	/**
	 * Filters data through one or many filters
	 * @param  mixed $value
	 * @param  string|array $filter
	 * @return mixed
	 */
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


	/**
	 * Checks if value is a number
	 *
	 * Valid: -1 123,01
	 * Valid: 01.00
	 * Valid: 81
	 * Invalid: 8e10
	 * @param  mixed $value
	 * @return bool
	 */
	protected function validateNumber($value) {
		if (preg_match("/^\-?[0-9\ ]+[\.|\,]?[0-9]*$/", $value))
			return true;
		$this->setError(t("Invalid number"));
		return false;
	}

	/**
	 * Check if value is an integer
	 * @param  mixed $value
	 * @return bool
	 */
	protected function validateInt($value) {
		if (preg_match("/^\-?[0-9]+$/", $value))
			return true;
		$this->setError(t("Invalid integer"));
		return false;
	}

	/**
	 * Check if value is an unsigned integer
	 * @param  mixed $value
	 * @return bool
	 */
	protected function validateUint($value) {
		if (preg_match("/^[0-9]+$/", $value))
			return true;
		$this->setError(t("Invalid positive integer"));
		return false;
	}

	/**
	 * Check if value is a floating point number
	 * @param  mixed $value
	 * @return bool
	 */
	protected function validateFloat($value) {
		if (preg_match("/^\-?[0-9]+\.?[0-9]*$/", $value))
			return true;
		$this->setError(t("Invalid decimal number"));
		return false;
	}

	/**
	 * Check if value is an unsigned floating point number
	 * @param  mixed $value
	 * @return bool
	 */
	protected function validateUfloat($value) {
		if (preg_match("/^[0-9]+\.?[0-9]*$/", $value))
			return true;
		$this->setError(t("Invalid positive decimal number"));
		return false;
	}

	/**
	 * Check if value is a date given as YYYY-MM-DD
	 * @param  mixed $value
	 * @return bool
	 */
	protected function validateDate($value) {
		if (preg_match("/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/", $value))
			return true;
		$this->setError(t("Invalid date (YYYY-MM-DD)"));
		return false;
	}

	/**
	 * Check if value is a timestamp given as XX:XX or XX:XX:XX
	 * @param  mixed $value
	 * @return bool
	 */
	protected function validateTime($value) {
		if (preg_match("/^([0-9]{2}\:){1,2}[0-9]{2}$/", $value)) 
			return true;
		$this->setError(t("Invalid time (HH:MM or HH:MM:SS)"));
		return false;
	}

	/**
	 * Check if value is a valid machine name 
	 * @param  mixed $value
	 * @return bool
	 */
	protected function validateMachineName($value) {
		if (preg_match("/^[a-z0-9\_]+$/", $value))
			return true;
		$this->setError(t("Invalid machine name (only a-z, 0-9, and _)"));
		return false;
	}

	/**
	 * Check if value is and url
	 * @see filter_var()
	 * @see FILTER_VALIDATE_URL
	 * @param  mixed $value
	 * @return bool
	 */
	protected function validateUrl($value) {
		if (filter_var($value, FILTER_VALIDATE_URL))
			return true;
		$this->setError(t("Invalid url"));
		return false;
	}

	/**
	 * Check if value is an e-mail
	 * @see filter_var
	 * @see FILTER_VALIDATE_EMAIL
	 * @param  mixed $value
	 * @return bool
	 */
	protected function validateEmail($value) {
		if (filter_var($value, FILTER_VALIDATE_EMAIL))
			return true;
		$this->setError(t("Invalid e-mail address"));
		return false;
	}

	/**
	 * Check if value is an ip-address
	 * @see filter_var
	 * @see FILTER_VALIDATE_IP
	 * @param  mixed $value
	 * @return bool
	 */
	protected function validateIp($value) {
		if (filter_var($value, FILTER_VALIDATE_IP))
			return true;
		$this->setError(t("Invalid IP-address"));
		return false;
	}

	/**
	 * Check if value is a valid username
	 * @param  mixed $value
	 * @return bool
	 */
	protected function validateUsername($value) {
		if (preg_match("/^[a-z0-9\-\_\@\.\*\^\~]+$/i", $value))
			return true;
		$this->setError(t("Illegal username"));
		return false;
	}


	/**
	 * Removes leading and trailing whitespace
	 * @see trim()
	 * @param  string $value
	 * @return string
	 */
	protected function filterTrim($value) {
		return trim($value);
	}

	/**
	 * Removes possibility of xss through string
	 * @see htmlspecialchars
	 * @param  string $value
	 * @return string
	 */
	protected function filterXss($value) {
		return htmlspecialchars($value, ENT_QUOTES);
	}

	/**
	 * Strips html-tags
	 * @see strip_tags()
	 * @param  string $value
	 * @return string
	 */
	protected function filterStripTags($value) {
		return strip_tags($value);
	}

	/**
	 * Makes sure to return a number
	 * @param  string $value
	 * @return string
	 */
	protected function filterNumber($value) {
		return preg_replace("/.*(^\-?[0-9\ ]+[\.|\,]?[0-9]*).*$/", "$1", $value);
	}

	/**
	 * Cast as integer
	 * @param  string $value
	 * @return int
	 */
	protected function filterInt($value) {
		return (int) $value;
	}

	/**
	 * Cast as integer and makes sure its not negative
	 * @param  string $value
	 * @return int
	 */
	protected function filterUint($value) {
		return abs((int) $value);
	}

	/**
	 * Cast as float
	 * @param  string $value
	 * @return float
	 */
	protected function filterFloat($value) {
		return (float) $value;
	}

	/**
	 * Cast as float and makes sure its not negative
	 * @param  string $value
	 * @return float
	 */
	protected function filterUfloat($value) {
		return abs((float) $value);
	}

	/**
	 * Cast as double
	 * @param  string $value
	 * @return double
	 */
	protected function filterDouble($value) {
		return (double) $value;
	}

	/**
	 * Cast as double and makes sure its not negative
	 * @param  string $value
	 * @return double
	 */
	protected function filterUdouble($value) {
		return abs((double) $value);
	}

	/**
	 * Only allow a-z characters and 0-9
	 * @param  string $value
	 * @return string
	 */
	protected function filterAlphanum($value) {
		return preg_replace("/[^a-z0-9]/i", "", $value);
	}

	/**
	 * Returns a string safe for filehandling
	 * @param  string $value
	 * @return string
	 */
	protected function filterFilename($value) {
		$value = str_replace(" ", "-", $value);
		$value = str_replace(["å", "ä", "ö", "Å", "Ä", "Ö"], ["a", "a", "o", "A", "A", "O"], $value);
		$value = preg_replace("/[^a-z0-9\-\_\.]/i", "", $value);
		return $value;
	}

	/**
	 * Returns a valid url-alias from a string
	 * @param  string $value
	 * @return string
	 */
	protected function filterAlias($value) {
		$value = str_replace(["Å", "å", "Ä", "ä", "À", "à", "Á", "á", "Æ", "æ"], "a", $value);
		$value = str_replace(["Ö", "ö", "Õ", "õ", "Ó", "ó", "Ò", "ò", "Ø", "ø", "ð"], "o", $value);
		$value = str_replace(["Ë", "ë", "É", "é", "È", "è", "Ê", "ê"], "e", $value);
		$value = str_replace(["Ï", "ï", "Í", "í", "Ì", "ì", "Î", "î"], "i", $value);
		$value = str_replace(["Ü", "ü", "Ú", "ú", "Ù", "ù", "Û", "û"], "u", $value);
		$value = str_replace(["Ç", "ç"], "c", $value);
		$value = str_replace(["Ñ", "ñ"], "n", $value);
		$value = str_replace("ß", "ss", $value);
		$value = str_replace(["Ž", "ž"], "z", $value);
		$value = strtolower($value);
		$value = preg_replace("/\s+/", "-", $value);
		$value = preg_replace("/[^a-z0-9\-\_\/]/", "", $value);
		$value = preg_replace("/[\-]+/", "-", $value);
		return $value;
	}

};