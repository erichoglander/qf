<?php
class User_Core_Model extends Model {
	
	public function getEditForm($User) {
		$Form = newClass("UserEdit_Form", $this->Db);
		return $Form;
	}
	
};