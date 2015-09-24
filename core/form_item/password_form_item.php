<?php
class Password_FormItem_Core extends FormItem {
	
	public $generator = false;
	public $generator_copy;
	
	public function preRenderInput(&$vars) {
		$vars["generator"] = $this->generator;
		$vars["generator_copy"] = $this->generator_copy;
	}
	
}