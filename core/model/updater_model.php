<?php
/**
 * Contains the updater model
 */
/**
 * Updater model
 * @author Eric Höglander
 */
class Updater_Model_Core extends Model {
	
	/**
	 * Get path to updates
	 * @return string
	 */
	public function updatesPath() {
		return DOC_ROOT."/core/update";
	}

	/**
	 * Run a specific update
	 * @param  int  $value
	 * @return bool
	 */
	public function runUpdate($value) {
		$path = $this->updatesPath()."/update_".$value.".php";
		if (!file_exists($path))
			return false;
		require_once($path);
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
	
	/**
	 * Get all pending updates
	 * @return array
	 */
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

	/**
	 * Update any missing translations from file
	 * @return int|bool Number of translations added. False on failure.
	 */
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