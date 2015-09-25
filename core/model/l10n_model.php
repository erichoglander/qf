<?php
class l10n_Model_Core extends Model {

	public function getActiveLanguages() {
		$rows = $this->Db->getRows("SELECT * FROM `language` WHERE status = 1");
		$languages = [];
		foreach ($rows as $row)
			$languages[$row->lang] = $row;
		return $languages;
	}

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

	public function deleteString($l10nString) {
		return $l10nString->deleteAll();
	}

	public function searchNum($q = null) {
		$vars = [];
		$sql = "SELECT * FROM `l10n_string` WHERE sid IS NULL";
		if ($q) {
			$sql.= " && string LIKE :q";
			$vars[":q"] = "%".$q."%";
		}
		return $this->Db->numRows($sql, $vars);
	}
	public function search($q = null, $start = 0, $stop = 30) {
		$vars = [];
		$sql = "SELECT id FROM `l10n_string` WHERE sid IS NULL";
		if ($q) {
			$sql.= " && string LIKE :q";
			$vars[":q"] = "%".$q."%";
		}
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
	public function scan($parts) {
		if (in_array("core", $parts))
			$arr[] = DOC_ROOT."/core";
		if (in_array("extend", $parts))
			$arr[] = DOC_ROOT."/extend";
		if (empty($arr))
			return null;
		return $this->scanFiles($arr);
	}
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