<?php
class i18n_Core {

	public $lang = "sv";

	protected $Db;


	public function __construct($Db) {
		$this->Db = &$Db;
	}
	
	public function translateString($str, $lang, $vars = []) {

		# Check if the source language is different from the target language
		if ($lang != $this->lang) {
			$row = $this->Db->getRow("
				SELECT id, sid FROM `translation` 
				WHERE 
					text = :str && 
					lang = :lang",
				[":str" => $str, ":lang" => $lang]);
			if (!$row) {
				$this->addTranslationString($str, $lang);
			}
			else {
				if ($row->sid) {
					$t = $this->Db->getRow("
						SELECT text FROM `translation` 
						WHERE 
							lang = :lang && 
							(sid = :id || sid = :sid)",
						[	":lang" => $this->lang, 
							":id" => $row->id, 
							":sid" => $row->sid]);
				}
				else {
					$t = $this->Db->getRow("
						SELECT text FROM `translation` 
						WHERE 
							lang = :lang && 
							sid = :id",
						[":lang" => $this->lang, ":id" => $row->id]);
				}
				if ($t)
					$str = $t->text;
			}
		}

		# Replace variables
		if (!empty($vars))
			$str = str_replace(array_keys($vars), array_values($vars), $str);

		return $str;
	}

	public function addTranslationString($text, $lang, $sid = 0) {
		return $this->Db->insert("translation", [
			"sid" => $sid,
			"text" => $text,
			"lang" => $lang,
			"created" => REQUEST_TIME,
			"updated" => REQUEST_TIME,
		]);
	}

	public function findTranslationsInFiles($arr) {

		if (empty($arr))
			return [];

		$t = [];
		foreach ($arr as $file) {
			if (is_dir($file)) {
				$files = glob($file."/*");
				$t = array_merge($t, $this->findTranslationsInFiles($files));
			}
			else if (substr($file, -4) == ".php") {
				$str = @file_get_contents($file);
				if ($str) {
					$sources = $this->findTranslations($str);
					if (!empty($sources))
						$t = array_merge($t, $sources);
				}
			}
		}

		return array_unique($t, SORT_REGULAR);
	}

	public function findTranslations($str) {

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

	public function importTranslations($data, $overwrite = false) {

		if (!is_array($data))
			return false;

		foreach ($data as $d) {

			if (empty($d->source) || empty($d->targets) || empty($d->source->text) || empty($d->source->lang))
				continue;

			$source = $this->Db->getRow("
					SELECT id FROM `translation` 
					WHERE 
						lang = :lang && 
						text = :text && 
						sid = 0",
						[	":lang" => $d->source->lang, 
							":text" => $d->source->text]);
			if ($source)
				$sid = $source->id;
			else {
				$sid = $this->Db->insert("translation", [
					"lang" => $d->source->lang,
					"text" => $d->source->text,
					"created" => REQUEST_TIME,
					"updated" => REQUEST_TIME,
					"sid" => 0,
				]);
			}

			foreach ($d->targets as $target) {
				if ($source) {
					$row = $this->Db->getRow("
							SELECT id FROM `translation` 
							WHERE 
								lang = :lang && 
								sid = :sid",
								[	":lang" => $target->lang, 
									":sid" => $sid]);
					if ($row) {
						if ($overwrite)
							$this->Db->update("translation", 
								[	"text" => $target->text, 
									"updated" => REQUEST_TIME], 
								[	"id" => $row->id]);
						continue;
					}
				}
				$this->Db->insert("translation", [
					"lang" => $target->lang,
					"text" => $target->text,
					"created" => REQUEST_TIME,
					"updated" => REQUEST_TIME,
					"sid" => $sid,
				]);
			}

		}

		return true;
	}


}