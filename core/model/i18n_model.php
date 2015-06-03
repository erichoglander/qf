<?php
class i18n_Model_Core extends Model {
	
	public function getTranslationsNum() {
		return $this->Db->numRows("SELECT * FROM `translation` WHERE sid = 0");
	}

	public function getTranslations($q = []) {
		$sql = "SELECT * FROM `translation` WHERE sid = 0 ORDER BY updated DESC";
		if (array_key_exists("start", $q)) {
			$sql.= " LIMIT ".$q["start"];
			if (array_key_exists("stop", $q))
				$sql.= ", ".$q["stop"];
		}
		$sources = $this->Db->getRows($sql);
		foreach ($sources as $i => $source) {
			$sources[$i]->translations = $this->Db->getRows("
					SELECT * FROM `translation` 
					WHERE sid = :sid", 
					[":sid" => $source->id]);
		}
		return $sources;
	}
	
}