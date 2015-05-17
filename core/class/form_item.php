<?php
class FormItem {
	
	public $name;
	public $items;
	public $multiple, $parent_multiple;
	public $submit_data = true;

	protected $type;
	protected $label, $description;
	protected $dragable;
	protected $add_button = "Add item";
	protected $delete_button = "Delete item";
	protected $required, $focus;
	protected $attributes = [];
	protected $value;
	protected $options;
	protected $empty_option;
	protected $filter = "trim";
	protected $validation;
	protected $template;
	protected $submitted = false;
	protected $tree = true;

	protected $prefix, $suffix;
	protected $input_prefix, $input_suffix;
	protected $item_class;

	protected $structure = [];
	protected $parent_name;
	protected $error = [];
	protected $validation_error;

	protected $Io;


	public function __construct(&$Io, $structure) {
		$this->Io = $Io;
		$this->empty_option = t("- Choose -");
		$this->loadStructure($structure);
	}

	public function setError($msg) {
		$this->error = $msg;
	}
	public function getError() {
		return $this->error;
	}

	public function value() {
		if (!$this->submitted)
			return $this->value;
		if ($this->items !== null) {
			$value = [];
			foreach ($this->items as $item)
				if ($item->submit_data)
					$value[$item->name] = $item->value();
		}
		else {
			$value = $this->itemValue();
			if ($this->filter)
				$value = $this->filter($value, $this->filter);
		}
		return $value;
	}

	public function hasValue() {
		if ($this->items === null) {
			return !$this->emptyValue($this->value());
		}
		else {
			foreach ($this->items as $item) 
				if ($item->hasValue())
					return true;
		}
		return false;
	}

	public function validated($req = true) {
		if (!$this->submitted)
			return false;
		if ($this->items !== null) {
			if (!$this->hasValue() && !$this->required)
				$req = false;
			foreach ($this->items as $item)
				if (!$item->validated($req))
					return false;
		}
		else {
			$value = $this->value();
			if ($req && $this->required && !$this->hasValue()) {
				$this->setError(t("Field is required"));
				return false;
			}
			if ($this->validation && !$this->validate($value, $this->validation))
				return false;
			if ($this->options !== null) {
				if (!is_array($value))
					$value = [$value];
				foreach ($value as $val) {
					if (is_array($val) || !array_key_exists($val, $this->options())) {
						$this->setError(t("Invalid option"));
						return false;
					}
				}
			}
		}
		return true;
	}

	public function render() {
		$path = $this->templateItemPath();
		$vars = [
			"name" => $this->name,
			"input_name" => $this->inputName(),
			"label" => $this->label,
			"description" => $this->description,
			"prefix"=> $this->prefix,
			"suffix" => $this->suffix,
			"input_prefix" => $this->input_prefix,
			"input_suffix" => $this->input_suffix,
			"item_class" => $this->itemClass(),
			"items" => $this->renderItems(),
			"input" => $this->renderInput(),
			"add_button" => $this->renderAddButton(),
			"delete_button" => $this->renderDeleteButton(),
			"error" => $this->getError(),
		];
		return renderTemplate($path, $vars);
	}


	protected function loadStructure($structure) {
		$this->structure = $structure;
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
			$st = $structure;
			$st['parent_name'] = $this->inputName();
			$st['multiple'] = false;
			$st['parent_multiple'] = true;
			unset($st['label']);
			unset($st['description']);
			unset($st['prefix']);
			unset($st['suffix']);
			foreach ($value as $i => $val) {
				if (!empty($structure['value']) && array_key_exists($structure['value'][$i]))
					$st = $structure['value'][$i];
				else
					unset($st['value']);
				unset($st['focus']);
				$this->loadItem($i, $st);
			}
		}
		else {
			if (isset($structure['items'])) {
				foreach ($structure['items'] as $name => $item) {
					if (!empty($structure['value']) && array_key_exists($structure['value'][$name]))
						$item['value'] = $structure['value'][$name];
					$item['parent_name'] = $this->inputName();
					$this->loadItem($name, $item);
				}
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
		$class = newClass($cname, $this->Io, $item);
		if (!$class) 
			$class = new FormItem($this->Io, $item);
		$this->items[$name] = $class;
	}

	protected function filter($value, $filter) {
		return $this->Io->filter($value, $filter);
	}
	protected function validate($value, $validation) {
		return $this->Io->validate($value, $validation);
	}

	protected function emptyValue($val) {
		$is_arr = is_array($val);
		return ($is_arr && empty($val) || !$is_arr && strlen($val) === 0);
	}

	protected function postValue() {
		$data = $_POST;
		if ($this->parent_name) {
			$arr = explode("[", str_replace("]", "", $this->parent_name));
			foreach ($arr as $f)
				$data = $data[$f];
		}
		return (isset($data[$this->name]) ? $data[$this->name] : null);
	}

	protected function itemValue() {
		return $this->postValue();
	}

	protected function options() {
		if ($this->options === null)
			return null;
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
	protected function inputName() {
		if (!$this->tree)
			return $this->parent_name;
		return ($this->parent_name ? $this->parent_name."[".$this->name."]" : $this->name);
	}

	protected function itemClass() {
		$class = "form-item ".cssClass("form-type-".$this->type)." ".cssClass("form-name-".$this->name);
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
		$attr['name'] = $this->inputName();
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
		$prefix = "form_item";
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

	protected function renderItems() {
		if ($this->items === null)
			return null;
		$items = [];
		foreach ($this->items as $item)
			$items[$item->name] = $item->render();
		return $items;
	}
	protected function renderInput() {
		if ($this->items !== null)
			return null;
		$path = $this->templateInputPath();
		$vars = [
			"attributes" => $this->attributes(),
			"name" => $this->inputName(),
			"focus" => $this->focus,
			"options" => $this->options(),
			"value" => $this->value(),
		];
		return renderTemplate($path, $vars);
	}
	protected function renderAddButton() {
		if (!$this->multiple) 
			return null;
		$data = $this->structure;
		unset($data['filter']);
		unset($data['validation']);
		unset($data['submitted']);
		$json = htmlspecialchars(json_encode($data), ENT_QUOTES);
		return '<input type="button" class="form-button" value="'.$this->add_button.'" onclick="formAddButton(this, '.$json.')">';
	}
	protected function renderDeleteButton() {
		if (!$this->parent_multiple)
			return null;
		return '<input type="button" class="form-button" value="'.$this->delete_button.'" onclick="formDeleteButton(this)">';
	}

};