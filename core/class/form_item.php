<?php
class FormItem {
	
	public $name;
	public $items;
	public $multiple, $parent_multiple;
	public $submit_data = true;
	public $tree = true;

	public $type;
	public $label, $item_label, $description, $icon;
	public $sortable;
	public $add_button = "Add item";
	public $delete_button = "Remove";
	public $required, $focus;
	public $attributes = [];
	public $value;
	public $options;
	public $empty_option;
	public $filter = "trim";
	public $validation, $validation_error;
	public $template;
	public $submitted = false;

	public $prefix, $suffix;
	public $input_prefix, $input_suffix;
	public $item_class;

	protected $structure = [];
	protected $item_structure = [];
	protected $parent_name;
	protected $error = [];

	protected $Db, $Io;


	public function __construct($Db, $Io, $structure) {
		$this->Db = $Db;
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
			foreach ($this->items as $item) {
				if ($item->submit_data) {
					$val = $item->value();
					if (is_array($val) && !$item->tree) {
						foreach ($val as $k => $v)
							$value[$k] = $v;
					}
					else {
						$value[$item->name] = $val;
					}
				}
			}
		}
		else {
			$value = $this->itemValue();
			if ($this->filter && $this->options === null && !$this->emptyValue($value))
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
			if ($this->validation && !$this->emptyValue($value) && !$this->validate($value, $this->validation))
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
			"sortable" => $this->sortable,
			"error" => $this->getError(),
		];
		if (is_callable([$this, "preRender"]))
			$this->preRender($vars);
		return renderTemplate($path, $vars);
	}


	protected function loadStructure($structure) {
		if (is_callable([$this, "loadDefault"]))
			$this->loadDefault();
		if (is_callable([$this, "preStructure"]))
			$this->preStructure($structure);
		$this->structure = $structure;
		if (!empty($structure["attributes"])) {
			foreach ($structure["attributes"] as $key => $val)
				$this->attributes[$key] = $val;
		}
		$properties = $structure;
		unset($properties["attributes"]);
		unset($properties["items"]);
		if (!empty($properties["multiple"]))
			unset($properties["sortable"]);
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
			$item_label = (!empty($st["item_label"]) ? $st["item_label"] : null);
			unset($st["label"]);
			unset($st["description"]);
			unset($st["prefix"]);
			unset($st["suffix"]);
			foreach ($value as $i => $val) {
				if (!empty($structure["value"]) && array_key_exists($i, $structure["value"]))
					$st["value"] = $structure["value"][$i];
				else
					unset($st["value"]);
				if (!empty($structure["labels"]) && array_key_exists($i, $structure["labels"]))
					$st["label"] = $structure["labels"][$i];
				else
					$st["label"] = $item_label;
				unset($st["focus"]);
				$this->loadItem($i, $st);
			}
			unset($st["filter"]);
			unset($st["validation"]);
			unset($st["submitted"]);
			unset($st["value"]);
			$st["label"] = $item_label;
			$st["form_item_class"] = get_class($this);
			$this->item_structure = $st;
		}
		else {
			if (isset($structure["items"])) {
				$this->items = [];
				foreach ($structure["items"] as $name => $item) {
					if (!empty($structure["value"]) && array_key_exists($name, $structure["value"]))
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
			foreach ($arr as $f) {
				if (!array_key_exists($f, $data))
					$data = [];
				else
					$data = $data[$f];
			}
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
		if ($this->empty_option !== null)
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
		$type = $this->type;
		if ($this->multiple)
			$type = "container";
		$class = "form-item ".cssClass("form-type-".$type)." ".cssClass("form-name-".$this->name);
		if ($this->icon)
			$class.= " form-item-icon";
		if ($this->isTextfield())
			$class.= " form-item-textfield";
		if ($this->required)
			$class.= " form-item-required";
		if ($this->error)
			$class.= " form-item-error";
		if ($this->sortable)
			$class.= " form-item-sortable";
		if (!empty($this->item_class))
			$class.= " ".$this->item_class;
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
			"empty_option" => $this->empty_option,
			"options" => $this->options(),
			"value" => $this->value(),
		];
		if (is_callable([$this, "preRenderInput"]))
			$this->preRenderInput($vars);
		return renderTemplate($path, $vars);
	}
	protected function renderAddButton() {
		if (!$this->multiple || !$this->add_button) 
			return null;
		$values = 
		$data = $this->item_structure;
		$last_item = count($this->items)-1;
		$json = htmlspecialchars(json_encode($data), ENT_QUOTES);
		return '<input type="button" class="form-button form-add-button btn" value="'.$this->add_button.'" last_item="'.$last_item.'" onclick="formAddButton(this, '.$json.')">';
	}
	protected function renderDeleteButton() {
		if (!$this->parent_multiple || !$this->delete_button)
			return null;
		return '<input type="button" class="form-button form-delete-button btn btn-danger" value="'.$this->delete_button.'" onclick="formDeleteButton(this)">';
	}

};