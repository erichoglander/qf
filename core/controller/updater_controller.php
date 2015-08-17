<?php
class Updater_Controller_Core extends Controller {
	
	public function acl() {
		return ["Update"];
	}

	public function updateAction() {
		$updates = $this->Model->getUpdates();
		$n = count($updates);
		if ($n) {
			$handle = fopen("php://stdin", "r");
			print t("There are :n new updates. Continue? (y/n)", "en", [":n" => $n])." ";
			$line = trim(fgets($handle));
			if ($line == "y") {
				foreach ($updates as $update) {
					if ($this->Model->runUpdate($update))
						print t("Performed update :update", "en", [":update" => $update]).PHP_EOL;
					else
						return t("Update :update failed", "en", [":update" => $update]);
				}
			}
			else {
				return t("Aborting");
			}
		}
		$tn = $this->Model->updateTranslations();
		if ($tn)
			return t(":n translations added", "en", [":n" => $tn]);
		if ($n || $tn)
			return t("Update complete");
		else
			return t("No new updates");
	}

}