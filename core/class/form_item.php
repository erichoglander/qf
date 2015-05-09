<?php
class FormItem {
	
	public $name;

	protected $type;
	protected $label, $description;
	protected $multiple, $dragable;
	protected $add_button, $delete_button;
	protected $required, $focus;
	protected $attributes;
	protected $filter, $validation;
	protected $contains = "inputs"; // 

	protected $prefix, $suffix;
	protected $inputPrefix, $inputSuffix;
	protected $itemClass;

	protected $error = [];
	protected $items = [];

	public function __construct($structure) {
		$this->loadStructure($structure);
	}

	public function setError($msg, $i = 0, $j = 0) {
		$this->error[$i][$j] = $msg;
	}
	public function getError($i = 0, $j = 0) {
		return (isset($this->error[$i][$j]) ? $this->error[$i][$j] : null);
	}

	public function value() {
		return null; // TODO: value
	}

	public function render($name) {
		$path = $this->templateItemPath();
		$label = $this->label;
		$description = $this->description;
		$containers = $this->renderContainers($name);
		$prefix = $this->prefix;
		$suffix = $this->suffix;
		$inputPrefix = $this->inputPrefix;
		$inputSuffix = $this->inputSuffix;
		$error = $this->getError();
		include $path;
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
		$item['parent_name'] = $this->name;
		$this->items[$name] = new $class($item);
	}

	protected function numRows() {
		return ($this->multiple ? max(count($this->values()), 1) : 1);
	}

	protected function inputType() {
		return $this->type;
	}
	protected function inputClass() {
		return cssClass("form-".$this->inputType());
	}

	protected function itemClass() {
		$class = "form-item ".cssClass("form-type-".$this->type);
		if ($this->type != $this->inputType())
			$class.= " ".cssClass("form-type-".$this->inputType());
		if ($this->required)
			$class.= " form-item-required";
		if ($this->error)
			$class.= " form-item-error";
		return $class;
	}

	protected	function getAttributes() {
		$attr = [];
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

	protected function templateItemPath() {
		$epath = DOC_ROOT."/extend/template/form/form_item.php";
		$cpath = DOC_ROOT."/core/template/form/form_item.php";
		if (file_exists($epath))
			return $path;
		if (file_exists($cpath))
			return $path;
		return null;
	}
	protected function templateInputPath() {
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

	protected function renderContainers($name) {
		$containers = [];
		if ($this->multiple) {
			$n = $this->numRows();
			if ($this->contains == "inputs") {
				for ($i=0; $i<$n; $i++)
					$containers[0][] = $this->renderInput($name."[".$i."]");
			}
			else if ($this->contains == "items") {
				$n = $this->numRows();
				for ($i=0; $i<$n; $i++) {
					foreach ($this->items as $item)
						$containers[] = $item->render($name."[".$i."]".$item->name);
				}
			}
		}
		else {
			$containers[0][] = $this->renderInput($name);
		}
		return $containers;
	}
	protected function renderInput($name) {
		$path = $this->ftemplateInputPath();
		if (!$path)
			throw new Exception("Can't find input template for form item ".$name);
		$attributes = $this->getAttributes();
		$value = $this->value();
		$attributes['name'] = $name;
		if ($this->multiple) {
			$attributes['value'] = (isset($value[$row]) ? $value[$row] : null);
		}
		else {
			$attributes['value'] = $value;
		}
		$attributes = $this->attributes($attributes);

	}

};