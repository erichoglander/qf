<?php
class i18n_Model_Core extends Model {

	public function getActiveLanguages() {
		$rows = $this->Db->getRows("SELECT * FROM `language` WHERE status = 1");
		$languages = [];
		foreach ($rows as $row)
			$languages[$row->lang] = $row;
		return $languages;
	}

	public function addTranslation($text, $lang, $sid = 0) {
		$data = [
			"lang" => $lang,
			"text" => $text,
			"sid" => $sid,
			"created" => REQUEST_TIME,
			"updated" => REQUEST_TIME,
		];
		return $this->Db->insert("translation", $data);
	}

	public function saveTranslation($translation) {
		if (empty($translation->id))
			return false;
		if (!$this->Db->update("translation", ["updated" => REQUEST_TIME], ["id" => $translation->id]))
			return false;
		foreach ($translation->translations as $lang => $t) {
			if (empty($t->id)) {
				$this->addTranslation($t->text, $lang, $translation->id);
			}
			else {
				$this->Db->update("translation", 
						[	"text" => $t->text,
							"updated" => REQUEST_TIME],
						["id" => $t->id]);
			}
		}
		return ture;
	}

	public function deleteTranslation($id) {
		return $this->Db->query("DELETE FROM `translation` WHERE sid = :id || id = :id", [":id" => $id]);
	}
	
	public function getTranslationsNum() {
		return $this->Db->numRows("SELECT * FROM `translation` WHERE sid = 0");
	}

	public function getTranslation($id) {
		$source = $this->Db->getRow("
				SELECT * FROM `translation` 
				WHERE id = :id",
				[":id" => $id]);
		if (!$source)
			return null;
		$rows = $this->Db->getRows("
				SELECT * FROM `translation` 
				WHERE sid = :sid", 
				[":sid" => $source->id]);
		$source->translations = [];
		foreach ($rows as $row)
			$source->translations[$row->lang] = $row;
		return $source;
	}

	public function searchNum($q = null) {
		$vars = [];
		$sql = "SELECT * FROM `translation` WHERE sid = 0";
		if ($q) {
			$sql.= " && text LIKE :q";
			$vars[":q"] = "%".$q."%";
		}
		return $this->Db->numRows($sql, $vars);
	}
	public function search($q = null, $start = 0, $stop = 30) {
		$vars = [];
		$sql = "SELECT * FROM `translation` WHERE sid = 0";
		if ($q) {
			$sql.= " && text LIKE :q";
			$vars[":q"] = "%".$q."%";
		}
		$sql.= " ORDER BY (updated-created) ASC";
		$sql.= " LIMIT ".$start.", ".$stop;
		$sources = $this->Db->getRows($sql, $vars);
		foreach ($sources as $i => $source) {
			$rows = $this->Db->getRows("
					SELECT * FROM `translation` 
					WHERE sid = :sid", 
					[":sid" => $source->id]);
			$sources[$i]->translations = [];
			foreach ($rows as $row)
				$sources[$i]->translations[$row->lang] = $row;
		}
		return $sources;
	}

	public function scanAdd($parts) {
		$arr = $this->scan($parts);
		foreach ($arr as $translation) {
			$row = $this->Db->getRow("
					SELECT * FROM `translation`
					WHERE 
						lang = :lang && 
						text = :text",
					[	":lang" => $translation->lang,
						":text" => $translation->text]);
			if (!$row) {
				$this->addTranslation($translation->text, $translation->lang);
				$n++;
			}
		}
		return $n;
	}
	public function scanInfo($parts) {
		$arr = $this->scan($parts);
		$info = ["total" => count($arr), "new" => 0];
		foreach ($arr as $translation) {
			$row = $this->Db->getRow("
					SELECT * FROM `translation`
					WHERE 
						lang = :lang && 
						text = :text",
					[	":lang" => $translation->lang,
						":text" => $translation->text]);
			if (!$row)
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
		foreach ($matches[1] as $i => $text) {
			$lang = (empty($matches[3][$i]) ? "en" : $matches[3][$i]);
			$arr[] = (object) [
				"text" => $text,
				"lang" => $lang,
			];
		}
		return $arr;
	}
	
}