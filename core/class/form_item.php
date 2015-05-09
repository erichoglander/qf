<?php
class FormItem {
	
	public $name;

	protected $type;
	protected $label, $description;
	protected $multiple, $dragable;
	protected $add_button, $delete_button;
	protected $required, $focus;
	protected $attributes;
	protected $value;
	protected $items = [];
	protected $options = [];
	protected $empty_option = "- Choose -";
	protected $filter, $validation;
	protected $template;
	protected $submitted = false;
	protected $contains = "inputs";

	protected $prefix, $suffix;
	protected $input_prefix, $input_suffix;
	protected $item_class;

	protected $error = [];
	protected $validation_error;

	public function __construct($structure) {
		$this->loadStructure($structure);
	}

	public function setError($msg, $i = 0) {
		$this->error[$i] = $msg;
	}
	public function getError($i = 0) {
		return (isset($this->error[$i]) ? $this->error[$i] : null);
	}

	public function value($name) {
		if (!$this->submitted)
			return $this->value;
		$name_arr = explode("[", str_replace("]", "", $name)); 
		$data = $_POST;
		foreach ($name_arr as $f) {
			if (!array_key_exists($f, $data))
				return null;
			$data = $data[$f];
		}
		if ($this->contains == "inputs") {
			return $this->filter($data, $this->filter);
		}
		else if ($this->contains == "items") {
			$values = [];
			if ($this->multiple) {
				$n = count($data);
				for ($i=0; $i<$n; $i++) {
					foreach ($this->items as $key => $item)
						$values[$key] = $item->value($name."[".$i."]".$key); 
				}
			}
			else {
				foreach ($this->items as $key => $item)
					$values[$key] = $item->value($name.$key); 
			}
			return $values;
		}
	}

	public function validated($name) {
		$value = $this->value($name);
		if ($this->contains == "inputs") {
			if ($this->multiple) {
				$n = count($value);
				for ($i=0; $i<$n; $i++) {
					$val = $value[$i];
					$is_arr = is_array($val);
					if ($this->required && ($is_arr && empty($val) || !$is_arr && strlen($val) === 0)) {
						$this->setError(t("Field is required"));
						return false;
					}
					if (!empty($this->options) && ($is_arr || !array_key_exists($val, $this->options()))) {
						$this->setError(t("Invalid option"));
						return false;
					}
					if ($this->validation && !$this->validate($val, $this->validation)) {
						$this->setError($this->validation_error);
						return false;
					}
				}
			}
			else {
				$is_arr = is_array($value);
				if ($this->required && ($is_arr && empty($value) || !$is_arr && strlen($value) === 0)) {
					$this->setError(t("Field is required"));
					return false;
				}
				if (!empty($this->options) && ($is_arr || !array_key_exists($value, $this->options()))) {
					$this->setError(t("Invalid option"));
					return false;
				}
				if ($this->validation && !$this->validate($value, $this->validation)) {
					$this->setError($this->validation_error);
					return false;
				}
			}
		}
		else if ($this->containers == "items") {
			if ($this->multiple) {
				$n = count($value);
				for ($i=0; $i<$n; $i++) {
					foreach ($this->items as $item) {
						if (!$this->validated($name."[".$i."]".$item->name))
							return false;
					}
				}
			}
			else {
				foreach ($this->items as $item) {
					if (!$this->validated($name.$item->name))
						return false;
				}
			}
		}
		return true;
	}

	public function render($name) {
		$path = $this->templateItemPath();
		$label = $this->label;
		$description = $this->description;
		$containers = $this->renderContainers($name);
		$prefix = $this->prefix;
		$suffix = $this->suffix;
		$input_prefix = $this->input_prefix;
		$input_suffix = $this->input_suffix;
		$options = $this->options();
		$add_button = $this->renderAddButton();
		$delete_button = $this->renderDeleteButton();
		$error = $this->getError();
		ob_start();
		include $path;
		return ob_get_clean();
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
		$class = "FormItem";
		foreach ($a as $b)
			$class.= ucwords($b);
		if (!class_exists($class))
			$class = "FormItem";
		$item['name'] = $name;
		$item['submitted'] = $this->submitted;
		$this->items[$name] = new $class($item);
	}

	protected function filter($value, $filter) {
		return $value; // TODO: filter
	}
	protected function validate($value, $validation) {
		return true; // TODO: validation
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

	protected	function getAttributes($name = null) {
		$attr = [];
		$attr['type'] = $this->inputType();
		foreach ($this->attributes as $key => $val)
			$attr[$key] = $val;
		if ($name)
			$attr['name'] = $name;
		if (empty($attr['class']))
			$attr['class'] = $this->inputClass();
		else
			$attr['class'].= " ".$this->inputClass();
		return $attr;
	}
	protected function attributes($name = null) {
		$attributes = $this->getAttributes($name);
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
		if ($this->type != $this->inputType)
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

	protected function renderContainers($name) {
		$containers = [];
		if ($this->multiple) {
			$n = max(count($this->value($name)), 1);
			if ($this->contains == "inputs") {
				for ($i=0; $i<$n; $i++) {
					$containers[0][] = $this->renderInput($name."[".$i."]", $this->value($name."[".$i."]"));
				}
			}
			else if ($this->contains == "items") {
				for ($i=0; $i<$n; $i++) {
					foreach ($this->items as $item)
						$containers[] = $item->render($name."[".$i."]".$item->name);
				}
			}
		}
		else {
			$containers[0][] = $this->renderInput($name, $this->value($name));
		}
		return $containers;
	}
	protected function renderInput($name, $value) {
		$path = $this->templateInputPath();
		$vars = [
			"attributes" => $this->attributes($name),
			"value" => $value,
		];
		return renderTemplate($path, $vars);
	}
	protected function renderAddButton() {
		return '<input type="button" class="form-button" value="'.$this->add_button.'" onclick="formAddButton(this)">';
	}
	protected function renderDeleteButton() {
		return '<input type="button" class="form-button" value="'.$this->delete_button.'" onclick="formDeleteButton(this)">';
	}

};