<?php
class l10n_Model_Core extends Model {

	public function getActiveLanguages() {
		$rows = $this->Db->getRows("SELECT * FROM `language` WHERE status = 1");
		$languages = [];
		foreach ($rows as $row)
			$languages[$row->lang] = $row;
		return $languages;
	}

	public function export($values) {
		$l10n_strings = [];
		$sql = "SELECT * FROM `l10n_string` WHERE sid = 0";
		$rows = $this->Db->getRows($sql);
		if (!empty($rows)) {
			$sql = "SELECT * FROM `l10n_string` WHERE sid = :id";
			if (!empty($values["input_type"])) 
				$sql.= " && input_type IN ('".implode("','", $values["input_type"])."')";
			if (!empty($values["language"]))
				$sql.= " && lang IN ('".implode("','", $values["language"])."')";
			foreach ($rows as $row) {
				$row->translations = $this->Db->getRows($sql, [":id" => $row->id]);
				if (!empty($row->translations))
					$l10n_strings[] = $row;
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
			if (!isset($l10nString->translations[$lang])) {
				$l10nString->translations[$lang] = $this->getEntity("l10nString");
				$l10nString->translations[$lang]->set("lang", $lang);
				$l10nString->translations[$lang]->set("input_type", "manual");
				$l10nString->translations[$lang]->set("string", $string);
				if (!$l10nString->translations->save())
					return false;
			}
			else {
				if ($l10nString->translations[$lang]->get("string") != $string) {
					$l10nString->translations[$lang]->set("input_type", "manual");
					$l10nString->translations[$lang]->set("string", $string);
					if (!$l10nString->translations[$lang]->save())
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
		$sql = "SELECT * FROM `l10n_string` WHERE sid = 0";
		if ($q) {
			$sql.= " && string LIKE :q";
			$vars[":q"] = "%".$q."%";
		}
		return $this->Db->numRows($sql, $vars);
	}
	public function search($q = null, $start = 0, $stop = 30) {
		$vars = [];
		$sql = "SELECT id FROM `l10n_string` WHERE sid = 0";
		if ($q) {
			$sql.= " && string LIKE :q";
			$vars[":q"] = "%".$q."%";
		}
		$sql.= " ORDER BY (updated-created) ASC";
		$sql.= " LIMIT ".$start.", ".$stop;
		$sources = $this->Db->getRows($sql, $vars);
		$l10n_strings = [];
		foreach ($sources as $source) {
			$l10nString = $this->getEntity("l10nString", $source->id);
			$l10nString->loadAll();
			$l10n_strings[] = $l10nString;
		}
		return $l10n_strings;
	}

	public function scanAdd($parts) {
		$arr = $this->scan($parts);
		foreach ($arr as $l10n_string) {
			$l10nString = $this->getEntity("l10nString");
			if (!$l10nString->loadFromString($l10n_string->string, $l10n_string->lang)) {
				$l10nString->set("lang", $l10n_string->lang);
				$l10nString->set("string", $l10n_string->string);
				$l10nString->set("input_type", "code");
				$l10nString->set("sid", 0);
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