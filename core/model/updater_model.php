<?php
class Updater_Model_Core extends Model {
	
	public function updatesPath() {
		return DOC_ROOT."/core/updates";
	}

	public function runUpdate($value) {
		$cname = "Update_".$value;
		if (!class_exists($cname))
			return false;
		$Update = newClass($cname, $this->Db);
		if (!$Update->execute())
			return false;
		$last = $this->Variable->get("core_update", 0);
		$this->Variable->set("core_update", max($value, $last));
		return true;
	}
	
	public function getUpdates() {
		$updates = [];
		$last = $this->Variable->get("core_update", 0);
		$files = glob($this->updatesPath()."/update_*.php");
		foreach ($files as $file) {
			$info = pathinfo($file);
			$value = (int) substr($info["filename"], 7);
			if ($value > $last)
				$updates[] = $value;
		}
		sort($updates, SORT_NUMERIC);
		return $updates;
	}

	public function updateTranslations() {
		$path = DOC_ROOT."/core/update/l10n_strings.json";
		if (!file_exists($path))
			return false;
		$json = @json_decode(file_get_contents($path));
		if (!$json)
			return false;
		$l10nModel = $this->getModel("l10n");
		$n = $l10nModel->import($json);
		return $n;
	}

}