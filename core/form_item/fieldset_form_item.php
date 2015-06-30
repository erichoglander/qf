<?php
class Fieldset_FormItem_Core extends FormItem {
	
	protected $collapsible = false;
	protected $collapsed = false;
	
	
	protected function preRender(&$vars) {
		if ($this->collapsible && !$this->multiple) {
			$vars["item_class"].= " form-collapsible";
			$vars["item_class"].= ($this->collapsed ? " collapsed" : " expanded");
		}
	}
	
}