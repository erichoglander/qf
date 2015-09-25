<?php
class Update_2 extends Update_Core {
	
	public function execute() {
		$qs = [
			"ALTER TABLE `l10n_string` CHANGE `sid` `sid` INT( 10 ) UNSIGNED NULL",
			"UPDATE `l10n_string` SET sid = NULL WHERE sid = 0",
			"ALTER TABLE `l10n_string` ADD FOREIGN KEY ( `sid` ) REFERENCES `l10n_string` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION";
		];
		foreach ($qs as $q) {
			if (!$this->Db->query($q))
				return false;
		}
		return true;
	}
	
}