<?php
class Update_6_Core extends Update_Core {
  
  public function execute() {
    $qs = [
      "ALTER TABLE `log` CHANGE `user_id` `user_id` INT( 10 ) UNSIGNED NULL ;",
      "UPDATE `log` SET user_id = NULL WHERE user_id = 0",
      "ALTER TABLE `log` ADD INDEX ( `user_id` ) ;",
      "ALTER TABLE `log` ADD INDEX ( `category` ) ;",
      "ALTER TABLE `log` ADD FOREIGN KEY ( `user_id` ) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE SET NULL ;",
    ];
    foreach ($qs as $q) {
      if (!$this->Db->query($q))
        return false;
    }
    return true;
  }
  
}