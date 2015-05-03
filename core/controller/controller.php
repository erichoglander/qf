<?php
class Controller {

	public $args = Array();
	protected $Config, $Db, $Html, $Model;

	public function __construct() {
		$this->Config = new Config();
		$this->Db = new Db();
		$this->Html = new Html($this->Db);
		$this->name = $this->getName();
		$this->real_name = $this->getRealName();
		if (!$this->connect()) {
			return $this->databaseFail();
		}
		$this->Model = $this->getModel();
	}

	public function __call($name, $arguments = Array()) {
		if (!method_exists($this, $name))
			return $this->notFound();
		if ($this->access($this, $name) === false)
			return $this->accessDenied();
	}
	
	protected function databaseFail() {
		return $this->serverBusy();
	}
	protected function serverBusy() {
		header("HTTP/1.1 503 Service unavailable");
		return $this->viewRender("503");
	}
	protected function notFound() {
		header("HTTP/1.1 404 Not found");
		return $this->viewRender("404");
	}
	protected function accessDenied() {
		header("HTTP/1.1 403 Forbidden");
		return $this->viewRender("403");
	}

	protected function access($action, $args = Array()) {
		return true;
	}

	protected function getName() {
		$class = get_class($this);
		if ($class == "Controller")
			return "default";
		return strtolower(str_replace("_Controller", "", str_replace("_Core_Controller", "", $class)));
	}

	protected function getRealName() {
		$class = get_class($this);
		if ($class == "Controller")
			return "default";
		return str_replace("_Controller", "", $class);
	}

	protected function getModel() {
		$emodel = ucwords($this->name)."_Model";
		$cmodel = $this->real_name."_Model";
		if (class_exists($emodel))
			return new $emodel($this->Db);
		else if (class_exists($cmodel))
			return new $cmodel($this->Db);
		else
			return null;
	}

	protected function viewPath($name) {
		$epath = DOC_ROOT."/extend/view/".$this->name."/".$name.".php";
		$cpath = DOC_ROOT."/core/view/".$this->name."/".$name.".php";
		if (file_exists($epath))
			return $epath;
		else if (file_exists($cpath))
			return $cpath;
		else
			return null;
	}

	protected function viewRender($name, $variables = Array()) {
		$path = $this->viewPath($name);
		if ($path) {
			extract($variables);
			ob_start();
			include $path;
			return ob_get_clean();
		}
		else {
			return null;
		}
	}

	protected function connect() {
		$dbc = $this->Config->getDatabase();
		return $this->Db->connect($dbc['user'], $dbc['pass'], $dbc['db'], $dbc['host']);
	}

};