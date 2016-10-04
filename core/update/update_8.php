<?php
class Update_8_Core extends Update_Core {
  
  public function execute() {
    $Cache = newClass("Cache", $this->Db);
    @$Cache->clearImageStyles();
    return true;
  }
  
}