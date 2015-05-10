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
	protected $name;
	protected $items;
	protected $errors = [];
	protected $prefix, $suffix;
	protected $vars = [];
	protected $Db;


	public function __construct(&$Db, $vars = []) {
		$this->Db = $Db;
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
				if ($item->multiple)
					$i++;
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

	public function values() {
		$values = [];
		foreach ($this->items as $name => $item)
			$values[$name] = $item->value($name);
		return $values;
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
		];
		return renderTemplate($path, $vars);
	}

	public function validated() {
		if (!empty($this->errors))
			return false;
		if (!$this->verifyToken()) {
			$this->setError(t("Form token expired, please try to submit the form again."));
			return false;
		}
		foreach ($this->items as $name => $item) {
			if (!$item->validated($name))
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

	public function loadStructure() {
		$args = func_get_args();
		if (empty($args))
			$structure = $this->structure();
		else
			$structure = call_user_func_array([$this, "structure"], func_get_args());
		if (empty($structure['name']))
			throw new Exception("No name given for form");
		if (!empty($structure['attributes'])) {
			foreach ($structure['attributes'] as $key => $val)
				$this->attributes[$key] = $val;
			unset($structure['attributes']);
		}
		$items = $structure['items'];
		unset($structure['items']);
		foreach ($structure as $key => $val)
			$this->{$key} = $val;
		foreach ($items as $name => $item)
			$this->loadItem($name, $item);
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

	protected function loadItem($name, $item) {
		if (empty($item['type']))
			throw new Exception("No type given for form item ".$name);
		$item['name'] = $name;
		$item['submitted'] = $this->submitted(false);
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

	protected function verifyToken() {
		return $_POST['form_token'] === $this->token();
	}

	protected function token() {
		if (!isset($_SESSION['form_token']))
			$_SESSION['form_token'] = substr(hash("sha512", rand(1,1000).microtime(true)."qfformtoken"), 4, 32);
		return $_SESSION['form_token'];
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
			return $epath;
		if (file_exists($cpath))
			return $cpath;
		return null;
	}

	protected function renderItems() {
		$items = [];
		foreach ($this->items as $name => $item)
			$items[] = $item->render($name);
		return $items;
	}

};