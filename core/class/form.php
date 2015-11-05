<?php
/*
	Form class

	Goal is to handle the following:
		ajax
		nested items
		multiple items with an add/delete buttons


	Usage:

		class UserLogin_Form extends Form {

			public function structure() {
				// Return array of structure here
			}

		};
			

*/
class Form {

	protected $attributes = [
		"method" => "POST",
		"action" => "",
		"class" => "form"
	];
	protected $vars;
	protected $name;
	protected $items;
	protected $ajax = false;
	protected $errors = [];
	protected $prefix, $suffix;
	protected $Db, $Io, $User;


	public function __construct($Db, $Io, $User, $vars = []) {
		$this->Db = $Db;
		$this->Io = $Io;
		$this->User = $User;
		$this->setVars($vars);
	}

	public function render() {
		$path = $this->templatePath();
		$vars = [
			"items" => $this->renderItems(),
			"errors" => $this->getErrors(),
			"attributes" => $this->attributes(),
			"token" => $this->token(),
			"name" => $this->name,
			"prefix" => $this->prefix,
			"suffix" => $this->suffix,
			"ajax" => $this->ajax,
		];
		return renderTemplate($path, $vars);
	}

	public function get($name, $def = null) {
		if (!array_key_exists($name, $this->vars))
			return $def;
		return $this->vars[$name];
	}
	public function set($name, $value) {
		$this->vars[$name] = $value;
		$this->loadStructure();
	}
	public function setVars($vars) {
		$this->vars = $vars;
		$this->loadStructure();
	}

	public function values() {
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
		return $value;
	}

	public function isValidated() {
		if (!empty($this->errors))
			return false;
		if (!$this->verifyToken()) {
			$this->setError(t("Form token expired, please try to submit the form again."));
			return false;
		}
		foreach ($this->items as $name => $item) {
			if (!$item->validated())
				return false;
		}
		if (!$this->validate($this->values()))
			return false;
		return true;
	}

	public function validate() {
		return true;
	}

	public function isSubmitted($validate = true) {
		return isset($_POST["form_".$this->name]) && (!$validate || $this->isValidated());
	}

	public function setError($msg, $name = null) {
		if ($name) {
			$arr = explode("[", str_replace("]", "", $name));
			$n = count($arr);
			$item = $this;
			for ($i=0; $i<$n; $i++) {
				if (!isset($item->items[$arr[$i]]))
					return;
				$item = $item->items[$arr[$i]];
			}
			$item->setError($msg, $name);
		}
		else {
			$this->errors[] = $msg;
		}
	}
	public function getErrors() {
		return $this->errors;
	}


	protected function structure() {
		return [
			"name" => "default_form",
			"attributes" => [
				"method" => "POST",
				"action" => "",
				"class" => "form",
			],
		];
	}

	protected function loadStructure() {
		$args = func_get_args();
		if (empty($args))
			$structure = $this->structure();
		else
			$structure = call_user_func_array([$this, "structure"], func_get_args());
		if (empty($structure["name"]))
			throw new Exception("No name given for form");
		if (!empty($structure["attributes"])) {
			foreach ($structure["attributes"] as $key => $val)
				$this->attributes[$key] = $val;
			unset($structure["attributes"]);
		}
		$items = $structure["items"];
		unset($structure["items"]);
		foreach ($structure as $key => $val)
			$this->{$key} = $val;
		foreach ($items as $name => $item)
			$this->loadItem($name, $item);
		if ($this->hasFileItem())
			$this->attributes["enctype"] = "multipart/form-data";
	}

	protected function loadItem($name, $item) {
		if (empty($item["type"]))
			throw new Exception("No type given for form item ".$name);
		$item["name"] = $name;
		$item["submitted"] = $this->isSubmitted(false);
		$item["form_name"] = $this->name;
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

	protected function defaultActions($submit = null, $cancel = null) {
		if (!$submit)
			$submit = t("Save");
		if (!$cancel)
			$cancel = t("Cancel");
		return [
			"type" => "actions",
			"items" => [
				"submit" => [
					"type" => "submit",
					"value" => $submit,
				],
				"cancel" => [
					"type" => "button",
					"value" => $cancel,
					"attributes" => [
						"onclick" => "window.history.go(-1)",
					],
				],
			],
		];
	}

	protected function hasFileItem() {
		if (!empty($this->items)) {
			foreach ($this->items as $item) {
				if ($item->hasFileItem())
					return true;
			}
		}
		return false;
	}

	protected function verifyToken() {
		return $_POST["form_token"] === $this->token();
	}

	protected function token() {
		if (!isset($_SESSION["form_token"]))
			$_SESSION["form_token"] = substr(hash("sha512", rand(1,1000).microtime(true)."qfformtoken"), 4, 32);
		return $_SESSION["form_token"];
	}

	protected	function getAttributes() {
		$attr = [];
		foreach ($this->attributes as $key => $val)
			$attr[$key] = $val;
		$class = cssClass("form-".$this->name);
		$attr["name"] = $this->name;
		if (empty($attr["class"]))
			$attr["class"] = $class;
		else
			$attr["class"].= " ".$class;
		if ($this->ajax)
			$attr["onsubmit"] = "return formAjaxSubmit(this);";
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

	protected function templatePath() {
		return filePath("template/form/form.php");
	}

	protected function renderItems() {
		$items = [];
		foreach ($this->items as $name => $item)
			$items[] = $item->render($name);
		return $items;
	}

};