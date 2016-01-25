<?php
class Update_2_Core extends Update_Core {
	
	public function execute() {
		// Create content table
		$sql = "
				CREATE TABLE IF NOT EXISTS `content` (
					`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					`sid` int(11) DEFAULT NULL,
					`lang` varchar(2) COLLATE utf8_swedish_ci DEFAULT NULL,
					`title` varchar(256) COLLATE utf8_swedish_ci NOT NULL,
					`data` longblob NOT NULL,
					`config` longblob NOT NULL,
					`status` tinyint(1) NOT NULL DEFAULT '1',
					`created` int(10) unsigned NOT NULL DEFAULT '0',
					`updated` int(10) unsigned NOT NULL DEFAULT '0',
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=1;";
		if ($this->Db->query($sql))
			return true;
		return false;
	}
	
}