<?php
class FormItem {
	
	public $name;
	public $items;
	public $multiple, $parent_multiple;

	protected $type;
	protected $label, $description;
	protected $dragable;
	protected $add_button, $delete_button;
	protected $required, $focus;
	protected $attributes = [];
	protected $value;
	protected $options = [];
	protected $empty_option;
	protected $filter, $validation;
	protected $template;
	protected $submitted = false;

	protected $prefix, $suffix;
	protected $input_prefix, $input_suffix;
	protected $item_class;

	protected $error = [];
	protected $validation_error;

	public function __construct($structure) {
		$this->empty_option = t("- Choose -");
		$this->loadStructure($structure);
	}

	public function setError($msg) {
		$this->error = $msg;
	}
	public function getError() {
		return $this->error;
	}

	public function value($name) {
		
	}

	public function hasValue() {
		$value = $this->value();
		return false;
	}

	public function validated() {
		$value = $this->value();
		return true;
	}

	public function render() {
		$path = $this->templateItemPath();
		$vars = [
			"name" => $this->name,
			"label" => $this->label,
			"description" => $this->description,
			"prefix"=> $this->prefix,
			"suffix" => $this->suffix,
			"input_prefix" => $this->input_prefix,
			"input_suffix" => $this->input_suffix,
			"item_class" => $this->itemClass(),
			"options" => $this->options(),
			"containers" => $this->renderContainers(),
			"add_button" => $this->renderAddButton(),
			"delete_button" => $this->renderDeleteButton(),
			"error" => $this->getError(),
		];
		return renderTemplate($path, $vars);
	}


	protected function loadStructure($structure) {
		if (!empty($structure['attributes'])) {
			foreach ($structure['attributes'] as $key => $val)
				$this->attributes[$key] = $val;
		}
		$properties = $structure;
		unset($properties['attributes']);
		unset($properties['items']);
		foreach ($properties as $key => $val)
			$this->{$key} = $val;
		if ($this->multiple) {
			$value = $this->value();
			if (empty($value))
				$value[] = null;
			$this->items = [];
			$structure['multiple'] = false;
			$structure['parent_multiple'] = true;
			unset($structure['label']);
			foreach ($value as $i => $val) {
				$this->loadItem($i, $structure);
			}
		}
		else {
			if (isset($items)) {
				foreach ($items as $name => $item)
					$this->loadItem($name, $item);
			}
		}
	}

	protected function loadItem($name, $item) {
		if (empty($item['type']))
			throw new Exception("No type given for form item ".$name);
		$item['name'] = $name;
		$item['submitted'] = $this->submitted;
		$a = explode("_", $item['type']);
		$cname = "";
		foreach ($a as $b)
			$cname.= ucwords($b);
		$cname.= "_FormItem";
		$class = newClass($cname, $item);
		if (!$class) 
			$class = new FormItem($item);
		$this->items[$name] = $class;
	}

	protected function filter($value, $filter) {
		return $value; // TODO: filter
	}
	protected function validate($value, $validation) {
		return true; // TODO: validation
	}

	protected function emptyValue($val) {
		$is_arr = is_array($val);
		return ($is_arr && empty($val) || !$is_arr && strlen($val) === 0);
	}

	protected function options() {
		$options = $this->options;
		if ($this->empty_option)
			$options = array_merge(["" => $this->empty_option], $options);
		return $options;
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
	protected function attributes() {
		$attributes = $this->getAttributes();
		$attr = "";
		foreach ($attributes as $key => $val)
			$attr.= $key.'="'.$val.'" ';
		$attr = substr($attr, 0, -1);
		return $attr;
	}

	protected function templateItemPath() {
		return filePath("template/form/form_item.php");
	}
	protected function templateInputPath() {
		$prefix = "form_input";
		$d = "__";
		$names = [];
		if ($this->template) {
			$names[] = $prefix.$d.$this->type.$d.$this->template;
			if ($this->type != $this->inputType)
				$names[] = $prefix.$d.$this->inputType().$d.$this->template;
			$names[] = $prefix.$d.$this->template;
		}
		$names[] = $prefix.$d.$this->type;
		if ($this->type != $this->inputType())
			$names[] = $prefix.$d.$this->inputType();
		$names[] = $prefix;
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

	protected function renderInput($focus = false) {
		$path = $this->templateInputPath();
		$vars = [
			"attributes" => $this->attributes(),
			"name" => $this->name,
			"focus" => $focus,
			"value" => $this->value(),
		];
		return renderTemplate($path, $vars);
	}
	protected function renderAddButton() {
		if ($this->multiple)
			return '<input type="button" class="form-button" value="'.$this->add_button.'" onclick="formAddButton(this)">';
		return "";
	}
	protected function renderDeleteButton() {
		if ($this->parent_multiple)
			return '<input type="button" class="form-button" value="'.$this->delete_button.'" onclick="formDeleteButton(this)">';
		return "";
	}

};