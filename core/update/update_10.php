<?php
class Update_10_Core extends Update_Core {
  
  public function execute() {
    $qs = [
      "ALTER TABLE `l10n_string` DROP FOREIGN KEY `l10n_string_ibfk_1` ;",
      "ALTER TABLE `l10n_string` ADD CONSTRAINT `l10n_string_ibfk_1` FOREIGN KEY ( `sid` ) REFERENCES `l10n_string` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;",
    ];
    return $this->dbQueries($qs);
  }
  
}