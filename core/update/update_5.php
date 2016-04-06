<?php
class Update_5_Core extends Update_Core {
  
  public function execute() {
    $qs = [
      "ALTER TABLE `login_attempt` CHANGE `user_id` `user_id` INT( 10 ) UNSIGNED NULL ;",
      "UPDATE `login_attempt` LEFT JOIN `user` ON `user`.id = user_id SET user_id = NULL WHERE `user`.id IS NULL",
      "ALTER TABLE `login_attempt` ADD INDEX ( `user_id` ) ;",
      "ALTER TABLE `login_attempt` ADD FOREIGN KEY ( `user_id` ) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE SET NULL ;",
    ];
    foreach ($qs as $q) {
      if (!$this->Db->query($q))
        return false;
    }
    return true;
  }
  
}