<?php
/**
 * Contains the l10n model
 */
/**
 * l10n model
 * @author Eric Höglander
 */
class l10n_Model_Core extends Model {

	/**
	 * Get all active languages
	 * @return array
	 */
	public function getActiveLanguages() {
		$rows = $this->Db->getRows("SELECT * FROM `language` WHERE status = 1");
		$languages = [];
		foreach ($rows as $row)
			$languages[$row->lang] = $row;
		return $languages;
	}

	/**
	 * Import string translations
	 *
	 * Data given in an array of objects, containing
	 * lang, string, and translations.
	 * Those translations themselves also containing lang and string.
	 *
	 * @see    export
	 * @param  array $l10n_strings
	 * @return int   The number of imported strings
	 */
	public function import($l10n_strings = []) {
		$n = 0;
		foreach ($l10n_strings as $l10n_string) {
			if (empty($l10n_string->string) || empty($l10n_string->lang))
				continue;
			$source = $this->Db->getRow("
					SELECT id FROM `l10n_string` 
					WHERE 
						lang = :lang &&
						string = :string &&
						sid IS NULL",
					[	":lang" => $l10n_string->lang,
						":string" => $l10n_string->string]);
			if ($source) {
				$l10nString = $this->getEntity("l10nString", $source->id);
			}
			else {
				$l10nString = $this->getEntity("l10nString");
				$l10nString->set("lang", $l10n_string->lang);
				$l10nString->set("input_type", "import");
				$l10nString->set("string", $l10n_string->string);
				$l10nString->save();
			}
			foreach ($l10n_string->translations as $lang => $translation) {
				if (empty($translation->string))
					continue;
				if ($source && $l10nString->translation($lang)) {
					if ($l10nString->translation($lang)->get("string") == $translation->string) 
						continue;
					if ($l10nString->translation($lang)->get("input_type") == "manual")
						continue;
					$l10nString->translation($lang)->set("string", $translation->string);
					$l10nString->translation($lang)->set("input_type", "import");
					$l10nString->translation($lang)->save();
					$n++;
				}
				else {
					$l10nString->newTranslation($lang);
					$l10nString->translation($lang)->set("lang", $lang);
					$l10nString->translation($lang)->set("string", $translation->string);
					$l10nString->translation($lang)->set("sid", $l10nString->id());
					$l10nString->translation($lang)->set("input_type", "import");
					$l10nString->translation($lang)->save();
					$n++;
				}
			}
		}
		return $n;
	}

	/**
	 * Export all string translations to json
	 * @see    import
	 * @param  array $values Search parameters
	 * @return string
	 */
	public function export($values) {
		$l10n_strings = [];
		$sql = "SELECT id, lang, string FROM `l10n_string` WHERE sid IS NULL";
		$rows = $this->Db->getRows($sql);
		if (!empty($rows)) {
			$sql = "SELECT lang, string, updated FROM `l10n_string` WHERE sid = :id";
			if (!empty($values["input_type"])) 
				$sql.= " && input_type IN ('".implode("','", $values["input_type"])."')";
			if (!empty($values["language"]))
				$sql.= " && lang IN ('".implode("','", $values["language"])."')";
			foreach ($rows as $row) {
				$row->translations = [];
				$translations = $this->Db->getRows($sql, [":id" => $row->id]);
				if (!empty($translations)) {
					unset($row->id);
					foreach ($translations as $translation)
						$row->translations[$translation->lang] = $translation;
					$l10n_strings[] = $row;
				}
			}
		}
		if (empty($values["min"]))
			$json = json_encode($l10n_strings, JSON_PRETTY_PRINT);
		else
			$json = json_encode($l10n_strings);
		return $json;
	}

	/**
	 * Save a localized string
	 * @param  \l10nString_Entity_Core $l10nString
	 * @param  array                   $values     Associative array of $lang => $string
	 * @return bool
	 */
	public function editString($l10nString, $values) {
		if (!$l10nString->id())
			return false;
		foreach ($values as $lang => $string) {
			if (!$l10nString->translation($lang)) {
				$l10nString->newTranslation($lang);
				$l10nString->translation($lang)->set("lang", $lang);
				$l10nString->translation($lang)->set("input_type", "manual");
				$l10nString->translation($lang)->set("string", $string);
				$l10nString->translation($lang)->set("sid", $l10nString->id());
				if (!$l10nString->translation($lang)->save())
					return false;
			}
			else {
				if ($l10nString->translation($lang)->get("string") != $string) {
					$l10nString->translation($lang)->set("input_type", "manual");
					$l10nString->translation($lang)->set("string", $string);
					if (!$l10nString->translation($lang)->save())
						return false;
				}
			}
		}
		return true;
	}

	/**
	 * Delete a localized string
	 * @param  \l10nString_Entity_Core $l10nString
	 * @return bool
	 */
	public function deleteString($l10nString) {
		return $l10nString->deleteAll();
	}

	/**
	 * Creates database query for a string search
	 * @param  string $q Search string
	 * @return array  Contains sql-query and vars
	 */
	public function searchQuery($q) {
		$vars = [];
		$sql = "SELECT * FROM `l10n_string` WHERE sid IS NULL";
		if ($q) {
			$sql.= " && string LIKE :q";
			$vars[":q"] = "%".$q."%";
		}
		return [$sql, $vars];
	}
	/**
	 * Number of matches for a search
	 * @see    searchQuery
	 * @param  string $q
	 * @return int
	 */
	public function searchNum($q = null) {
		list($sql, $vars) = $this->searchQuery($q);
		return $this->Db->numRows($sql, $vars);
	}
	/**
	 * Get localized strings matching a search
	 * @see    searchQuery
	 * @param  string $q     Search string
	 * @param  int    $start
	 * @param  int    $stop
	 * @return array
	 */
	public function search($q = null, $start = 0, $stop = 30) {
		list($sql, $vars) = $this->searchQuery($q);
		$sql.= " ORDER BY created DESC";
		$sql.= " LIMIT ".$start.", ".$stop;
		$sources = $this->Db->getRows($sql, $vars);
		$l10n_strings = [];
		foreach ($sources as $source) {
			$l10nString = $this->getEntity("l10nString", $source->id);
			$l10n_strings[] = $l10nString;
		}
		return $l10n_strings;
	}

	/**
	 * Scan code for translation calls and add them to database if needed
	 * @see    scan
	 * @param  array $parts Which parts of the codebase to scan
	 * @return int   $n     Number of strings added to database
	 */
	public function scanAdd($parts) {
		$arr = $this->scan($parts);
		$n = 0;
		foreach ($arr as $l10n_string) {
			$l10nString = $this->getEntity("l10nString");
			if (!$l10nString->loadFromString($l10n_string->string, $l10n_string->lang)) {
				$l10nString->set("lang", $l10n_string->lang);
				$l10nString->set("string", $l10n_string->string);
				$l10nString->set("input_type", "code");
				$l10nString->save();
				$n++;
			}
		}
		return $n;
	}
	
	/**
	 * Scan code for translation calls and return information about the scan
	 * @see    scan
	 * @param  array $parts Which parts of the codebase to scan
	 * @return array
	 */
	public function scanInfo($parts) {
		$arr = $this->scan($parts);
		$info = ["total" => count($arr), "new" => 0];
		$l10nString = $this->getEntity("l10nString");
		foreach ($arr as $l10n_string) {
			if (!$l10nString->loadFromString($l10n_string->string, $l10n_string->lang))
				$info["new"]++;
		}
		return $info;
	}
	
	/**
	 * Scan code for translation calls
	 * @see    scanFiles
	 * @param  array $parts Which parts of the codebase to scan
	 * @return array
	 */
	public function scan($parts) {
		if (in_array("core", $parts))
			$arr[] = DOC_ROOT."/core";
		if (in_array("extend", $parts))
			$arr[] = DOC_ROOT."/extend";
		if (empty($arr))
			return null;
		return $this->scanFiles($arr);
	}
	
	/**
	 * Scan folders for translation calls
	 * @see    scanString
	 * @param  array $arr Folders to scan
	 * @return array
	 */
	public function scanFiles($arr) {
		$t = [];
		foreach ($arr as $file) {
			if (is_dir($file)) {
				$files = glob($file."/*");
				$t = array_merge($t, $this->scanFiles($files));
			}
			else if (substr($file, -4) == ".php") {
				$str = @file_get_contents($file);
				if ($str) {
					$sources = $this->scanString($str);
					if (!empty($sources))
						$t = array_merge($t, $sources);
				}
			}
		}
		return array_unique($t, SORT_REGULAR);
	}
	
	/**
	 * Scan a string for translation calls
	 * @param  string $str
	 * @return array  Data of translation calls with the strings and languages
	 */
	public function scanString($str) {
		$arr = [];
		$n = preg_match_all("/[^a-z0-9\_\>\$]t\(\"([^\"]+)\"(\,\s*\"([a-z]+)\")?/i", $str, $matches);
		if (!$n) 
			return null;
		foreach ($matches[1] as $i => $string) {
			$lang = (empty($matches[3][$i]) ? "en" : $matches[3][$i]);
			$arr[] = (object) [
				"string" => $string,
				"lang" => $lang,
			];
		}
		return $arr;
	}
	
}