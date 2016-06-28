<?php
class Update_7_Core extends Update_Core {
  
  public function execute() {
    $q = "ALTER TABLE `user` ADD `lang` VARCHAR( 2 ) NULL AFTER `email` ;";
    return $this->Db->query($q);
  }
  
}