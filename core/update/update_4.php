<?php
class Update_4_Core extends Update_Core {
	
	public function execute() {
		$qs = [
			"ALTER TABLE `alias` ADD `lang` VARCHAR( 2 ) NULL AFTER `updated`, ADD INDEX (`lang`);",
			"ALTER TABLE `redirect` ADD `lang` VARCHAR( 2 ) NULL AFTER `updated`, ADD INDEX (`lang`);",
		];
		foreach ($qs as $q) {
			if (!$this->Db->query($q))
				return false;
		}
		return true;
	}
	
}