<?php
class FormItem {
	
	public $name;
	public $items;
	public $multiple, $parent_multiple;
	public $submit_data = true;

	protected $type;
	protected $label, $description, $icon;
	protected $dragable;
	protected $add_button = "Add item";
	protected $delete_button = "Delete item";
	protected $required, $focus;
	protected $attributes = [];
	protected $value;
	protected $options;
	protected $empty_option;
	protected $filter = "trim";
	protected $validation, $validation_error;
	protected $template;
	protected $submitted = false;
	protected $tree = true;

	protected $prefix, $suffix;
	protected $input_prefix, $input_suffix;
	protected $item_class;

	protected $structure = [];
	protected $item_structure = [];
	protected $parent_name;
	protected $error = [];

	protected $Db, $Io;


	public function __construct($Db, $Io, $structure) {
		$this->Db = &$Db;
		$this->Io = &$Io;
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
			if ($this->filter && $this->options === null)
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

	public function hasFileItem() {
		if (!empty($this->items)) {
			foreach ($this->items as $item) {
				if ($item->hasFileItem())
					return true;
			}
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
					if ($val !== null && (is_array($val) || !array_key_exists($val, $this->options()))) {
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
			"icon" => $this->icon,
			"error" => $this->getError(),
		];
		if (is_callable([$this, "preRender"]))
			$this->preRender($vars);
		return renderTemplate($path, $vars);
	}


	protected function loadStructure($structure) {
		if (is_callable([$this, "loadDefault"]))
			$this->loadDefault();
		$this->structure = $structure;
		if (!empty($structure["attributes"])) {
			foreach ($structure["attributes"] as $key => $val)
				$this->attributes[$key] = $val;
		}
		$properties = $structure;
		unset($properties["attributes"]);
		unset($properties["items"]);
		foreach ($properties as $key => $val)
			$this->{$key} = $val;
		if ($this->multiple) {
			$value = $this->value();
			if (empty($value))
				$value[] = null;
			$this->items = [];
			$st = $structure;
			$st["parent_name"] = $this->inputName();
			$st["multiple"] = false;
			$st["parent_multiple"] = true;
			unset($st["label"]);
			unset($st["description"]);
			unset($st["prefix"]);
			unset($st["suffix"]);
			foreach ($value as $i => $val) {
				if (!empty($structure["value"]) && array_key_exists($i, $structure["value"]))
					$st = $structure["value"][$i];
				else
					unset($st["value"]);
				unset($st["focus"]);
				$this->loadItem($i, $st);
			}
			unset($st["filter"]);
			unset($st["validation"]);
			unset($st["submitted"]);
			unset($st["value"]);
			$st["form_item_class"] = get_class($this);
			$this->item_structure = $st;
		}
		else {
			if (isset($structure["items"])) {
				foreach ($structure["items"] as $name => $item) {
					if (!empty($structure["value"]) && array_key_exists($structure["value"][$name]))
						$item["value"] = $structure["value"][$name];
					$item["parent_name"] = $this->inputName();
					$this->loadItem($name, $item);
				}
			}
		}
	}

	protected function loadItem($name, $item) {
		if (empty($item["type"]))
			throw new Exception("No type given for form item ".$name);
		$item["name"] = $name;
		$item["submitted"] = $this->submitted;
		$item["form_name"] = $this->form_name;
		$a = explode("_", $item["type"]);
		$cname = "";
		foreach ($a as $b)
			$cname.= ucwords($b);
		$cname.= "_FormItem";
		$class = newClass($cname, $this->Db, $this->Io, $item);
		if (!$class) 
			$class = new FormItem($this->Db, $this->Io, $item);
		$this->items[$name] = $class;
	}

	protected function filter($value, $filter) {
		return $this->Io->filter($value, $filter);
	}
	protected function validate($value, $validation) {
		if (!$this->Io->validate($value, $validation)) {
			if ($this->validation_error)
				$this->setError($this->validation_error);
			else
				$this->setError($this->Io->getError());
			return false;
		}
		return true;
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
			$options = ["" => $this->empty_option]+$options;
		return $options;
	}

	protected function isTextfield() {
		$arr = ["text", "textarea", "password", "tel", "url", "email", "search", "number"];
		return in_array($this->inputType(), $arr);
	}

	protected function inputType() {
		return $this->type;
	}
	protected function inputClass() {
		$class = cssClass("form-".$this->inputType());
		if ($this->isTextfield())
			$class.= " form-textfield";
		return $class;
	}
	protected function inputName() {
		if (!$this->tree)
			return $this->parent_name;
		return ($this->parent_name ? $this->parent_name."[".$this->name."]" : $this->name);
	}

	protected function itemClass() {
		$class = "form-item ".cssClass("form-type-".$this->type)." ".cssClass("form-name-".$this->name);
		if ($this->icon)
			$class.= " form-item-icon";
		if ($this->isTextfield())
			$class.= " form-item-textfield";
		if ($this->required)
			$class.= " form-item-required";
		if ($this->error)
			$class.= " form-item-error";
		return $class;
	}

	protected	function getAttributes() {
		$attr = [];
		$attr["type"] = $this->inputType();
		$attr["name"] = $this->inputName();
		foreach ($this->attributes as $key => $val)
			$attr[$key] = $val;
		if (empty($attr["class"]))
			$attr["class"] = $this->inputClass();
		else
			$attr["class"].= " ".$this->inputClass();
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
			if ($this->type != $this->inputType())
				$names[] = $prefix.$d.$this->inputType().$d.$this->template;
			$names[] = $prefix.$d.$this->template;
		}
		$names[] = $prefix.$d.$this->type;
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
			if ($this->type != $this->inputType())
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
		if (is_callable([$this, "preRenderInput"]))
			$this->preRenderInput($vars);
		return renderTemplate($path, $vars);
	}
	protected function renderAddButton() {
		if (!$this->multiple) 
			return null;
		$values = 
		$data = $this->item_structure;
		$json = htmlspecialchars(json_encode($data), ENT_QUOTES);
		return '<input type="button" class="form-button form-add-button btn" value="'.$this->add_button.'" onclick="formAddButton(this, '.$json.')">';
	}
	protected function renderDeleteButton() {
		if (!$this->parent_multiple)
			return null;
		return '<input type="button" class="form-button form-delete-button btn" value="'.$this->delete_button.'" onclick="formDeleteButton(this)">';
	}

};