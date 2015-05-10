<?php
class User_Core_Model extends Model {
	
	public function getEditForm($User) {
		$Form = $this->getForm("UserEdit");
		$Form->loadStructure($User);
		return $Form;
	}
	
};