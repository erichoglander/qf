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
class Form_Core {
	
	protected $attributes = [
		"name" => "default_form",
		"method" => "POST",
		"action" => "",
		"class" => "form"
	];
	protected $items;
	protected $errors = [];
	protected $prefix, $suffix;
	protected $Db;


	public function __construct(&$Db) {
		$this->Db = $Db;
		$this->loadStructure();
	}

	public function setError($msg) {
		$this->errors[] = $msg;
	}
	public function getErrors() {
		return $this->errors;
	}

	public function values() {
		$values = [];
		foreach ($this->items as $name => $item)
			$values[$name] = $item->value();
		return $values;
	}

	public function render() {
		$path = $this->templatePath();
		$items = $this->renderItems();
		$errors = $this->getErrors();
		$attributes = $this->attributes();
		$token = $this->token();
		$name = $this->name;
		ob_start();
		include $path;
		return ob_get_clean();
	}

	public function validated() {
		if (!empty($this->errors))
			return false;
		foreach ($this->items as $item) {
			if (!$this->validate())
				return false;
		}
		return true;
	}

	public function submitted($validate = true) {
		if (!isset($_POST['form_'.$this->name]))
			return false;
		if ($validate && !$this->validated())
			return false;
		return true;
	}

	public function onSubmit() {
		// Any code that runs on form submition		
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

	protected function verifyToken() {
		return $_POST['form_token'] === $this->token();
	}

	protected function token() {
		if (!isset($_SESSION['form_token']))
			$_SESSION['form_token'] = hash("sha512", rand(1,1000).microtime(true)."qfformtoken");
		return $_SESSION['form_token'];
	}

	protected function loadStructure() {
		$structure = $this->structure();
		if (!empty($structure['attributes'])) {
			foreach ($structure['attributes'] as $key => $val)
				$this->attributes[$key] = $val;
		}
		foreach ($structure['items'] as $name => $item) 
			$this->loadItem($name, $item);
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

	protected	function getAttributes() {
		$attr = [];
		foreach ($this->attributes as $key => $val)
			$attr[$key] = $val;
		$class = cssClass("form-".$this->name);
		if (empty($attr['class']))
			$attr['class'] = $class;
		else
			$attr['class'].= " ".$class;
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
		$epath = DOC_ROOT."/extend/template/form/form.php";
		$cpath = DOC_ROOT."/core/template/form/form.php";
		if (file_exists($epath))
			return $path;
		if (file_exists($cpath))
			return $path;
		return null;
	}

	protected function renderItems() {
		$items = [];
		foreach ($this->items as $name => $item)
			$items[] = $item->render($name);
		return $items;
	}

};