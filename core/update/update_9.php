<?php
class Update_9_Core extends Update_Core {
  
  public function execute() {
    $q = "ALTER TABLE `redirect` ADD `type` ENUM( 'normal', 'regexp' ) NOT NULL DEFAULT 'normal', ADD INDEX ( `type` ) ;";
    return $this->Db->query($q);
  }
  
}