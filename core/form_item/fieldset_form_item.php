<?php
class Fieldset_FormItem_Core extends FormItem {
	
	public $collapsible = false;
	public $collapsed = false;
	
	
	protected function loadStructure($structure) {
		parent::loadStructure($structure);
		$this->item_structure["collapsed"] = false;
	}
	
	protected function preRender(&$vars) {
		if ($this->collapsible && !$this->multiple) {
			$vars["item_class"].= " form-collapsible";
			$vars["item_class"].= ($this->collapsed ? " collapsed" : " expanded");
		}
	}
	
}