<?php
class FormItem {
	
	protected $name, $type;
	protected $label, $description;
	protected $multiple, $dragable;
	protected $required, $focus;
	protected $attributes;
	protected $filter, $validation;

	protected $prefix, $suffix;
	protected $inputPrefix, $inputSuffix;
	protected $itemClass,

	protected $error;

	public function __construct($name, $structure) {
		$this->name = $name;
	}

	public function setError($msg) {
		$this->error = $msg;
	}
	public function getError() {
		return $this->error;
	}


	protected function loadStructure($structure) {
		if (!empty($structure['attributes'])) {
			foreach ($structure['attributes'] as $key => $val)
				$this->attributes[$key] = $val;
			unset($structure['attributes']);
		}
		if (!empty($structure['items'])) {
			foreach ($structure['items'] as $name => $item)
				$this->loadItem($name, $item);
			unset($structure['items']);
		}
		foreach ($structure as $key => $val)
			$this->{$key} = $val;
	}

	protected function loadItem($name, $item) {
		if (empty($item['type']))
			throw new Exception("No type given for form item ".$name);
		$a = explode("_", $item['type']);
		$class = "";
		foreach ($a as $b)
			$class.= ucwords($b)."_FormItem";
		if (!class_exists($class))
			throw new Exception("Class not found for form type ".$item['type']);
		$this->items[$name] = new $class($name, $item);
	}

	protected function inputName() {
		return ($this->multiple ? $this->name."[]" : $this->name);
	}
	protected function inputType() {
		return $this->type;
	}
	protected function inputClass() {
		return "form-".$this->inputType();
	}

	protected function itemClass() {
		$class = "form-item form-type-".$this->type;
		if ($this->type != $this->inputType())
			$class.= " form-type-".$this->inputType();
		if ($this->required)
			$class.= " form-item-required";
		if ($this->error)
			$class.= " form-item-error";
		return $class;
	}

	protected	function getAttributes() {
		$attr = [];
		$attr['name'] = $this->inputName();
		$attr['type'] = $this->inputType();
		foreach ($this->attributes as $key => $val)
			$attr[$key] = $val;
		if (empty($attr['class']))
			$attr['class'] = $this->inputClass();
		else
			$attr['class'].= " ".$this->inputClass();
		return $attr;
	}
	protected function attributes() {
		$attr = "";
		foreach ($this->getAttributes() as $key => $val)
			$attr.= $key.'="'.$val.'" ';
		$attr = substr($attr, 0, -1);
		return $attr;
	}

	protected function templatePath() {
		$names = [
			"form_item__".$this->type,
			"form_item__".$this->inputType(),
			"form_item",
		];
		foreach ($names as $name) {}
			$path = DOC_ROOT."/extend/template/form/".$name.".php";
			if (file_exists($epath))
				return $path;
		}
		foreach ($names as $name) {}
			$path = DOC_ROOT."/core/template/form/".$name.".php";
			if (file_exists($epath))
				return $path;
		}
		return null;
	}

	protected function render() {
		$path = $this->templatePath();
		if (!$path)
			throw new Exception("Can't find template for form item ".$name);
		$label => $this->label;
		$description = $this->description;
		$items = $this->renderItems();
		$inputs = $this->renderInputs();
		include $path;
	}

	protected function renderInputs() {
		$inputs = [];
		return $inputs;
	}

	protected function renderItems() {
		$items = [];
		if (!empty($this->items)) {
			foreach ($this->items as $item) 
				$items[] = $item->render();
		}
		return $items;
	}

};