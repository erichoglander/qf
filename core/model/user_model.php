<?php
class User_Core_Model extends Model {
	
	public function getEditPage() {
		$vars = [];
		$vars['User'] = $this->getEntity("User");
		$vars['Form'] = $this->getEditForm($vars['User']);
		return $vars;
	}
	public function getEditForm($User) {
		$Form = $this->getForm("UserEdit");
		$Form->loadStructure($User);
		if ($Form->submitted(true)) {
			print "Submitted & validated";
			$Form->onSubmit();
		}
		return $Form;
	}
	
};