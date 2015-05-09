<?php
class FormItem {
	
	protected $name, $full_name;
	protected $type;
	protected $label, $description;
	protected $multiple, $dragable;
	protected $required, $focus;
	protected $attributes;
	protected $filter, $validation;

	protected $prefix, $suffix;
	protected $inputPrefix, $inputSuffix;
	protected $itemClass;

	protected $error = [];

	public function __construct($structure) {
		$this->loadStructure($structure);
	}

	public function setError($msg, $n = 0) {
		$this->error[$n] = $msg;
	}
	public function getError($n = 0) {
		return (isset($this->error[$n]) ? $this->error[$n] : null);
	}

	public function value() {
		return null; // TODO: value
	}


	protected function loadStructure($structure) {
		if (!empty($structure['attributes'])) {
			foreach ($structure['attributes'] as $key => $val)
				$this->attributes[$key] = $val;
			unset($structure['attributes']);
		}
		if (!empty($structure['items'])) {
			foreach ($structure['items'] as $name => $item)
				$this->loadItem($name, $item, $this->data[$this->name]);
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
		$item['name'] = $name;
		$item['full_name'] = $this->inputName().$name;
		$this->items[$name] = new $class($item);
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
	protected function attributes($attributes = null) {
		if (!$attributes)
			$attributes = $this->getAttributes();
		$attr = "";
		foreach ($attributes as $key => $val)
			$attr.= $key.'="'.$val.'" ';
		$attr = substr($attr, 0, -1);
		return $attr;
	}

	protected function formItemPath() {
		$epath = DOC_ROOT."/extend/template/form/form_item.php";
		$cpath = DOC_ROOT."/core/template/form/form_item.php";
		if (file_exists($epath))
			return $path;
		if (file_exists($cpath))
			return $path;
		return null;
	}
	protected function formInputPath() {
		$names = [
			"form_input__".$this->type,
			"form_input__".$this->inputType(),
			"form_input",
		];
		foreach ($names as $name) {
			$path = DOC_ROOT."/extend/template/form/".$name.".php";
			if (file_exists($path))
				return $path;
		}
		foreach ($names as $name) {
			$path = DOC_ROOT."/core/template/form/".$name.".php";
			if (file_exists($path))
				return $path;
		}
		return null;
	}

	protected function render() {
		$path = $this->formItemPath();
		$label = $this->label;
		$description = $this->description;
		$items = $this->renderItems();
		$inputs = $this->renderInputs();
		$prefix = $this->prefix;
		$suffix = $this->suffix;
		$inputPrefix = $this->inputPrefix;
		$inputSuffix = $this->inputSuffix;
		$error = $this->getError();
		include $path;
	}
	protected function renderInputs() {
		$inputs = [];
		if ($this->multiple) {
			$n = max(count($this->values()), 1);
			for ($i=0; $i<$n; $i++) {
				$inputs[] = $this->renderInput($i);
			}
		}
		else {
			$inputs[] = $this->renderInput(0);
		}
		return $inputs;
	}
	protected function renderInput($n) {
		$path = $this->formInputPath();
		if (!$path)
			throw new Exception("Can't find input template for form item ".$name);
		$attributes = $this->getAttributes();
		$value = $this->value();
		if ($this->multiple) {
			$attributes['value'] = (isset($value[$n]) ? $value[$n] : null);
		}
		else {
			$attributes['value'] = $value;
		}
		$attributes = $this->attributes($attributes);

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