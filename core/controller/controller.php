<?php
class Controller {

	public $args = Array();
	public $connected;
	protected $Config, $Db, $Model;

	public function __construct() {
		$this->Config = new Config();
		$this->Db = new Db();
		$this->name = $this->getName();
		$this->real_name = $this->getRealName();
		if ($this->connect()) {
			$this->connected = true;
			$this->Model = $this->getModel();
		}
		else {
			$this->connected = false;
		}
	}


	public function callAction($action, $args = Array()) {
		if (!$this->connected)
			return $this->databaseFail();
		if (!$this->access($action, $args))
			return $this->accessDenied();
		if (!method_exists($this, $action)) 
			return $this->notFound();
		return $this->$action($args);
	}

	
	protected function internalError() {
		header("HTTP/1.1 500 Internal error");
		return $this->viewBare("500");
	}
	protected function databaseFail() {
		return $this->serverBusy();
	}
	protected function serverBusy() {
		header("HTTP/1.1 503 Service unavailable");
		return $this->viewBare("503");
	}
	protected function notFound($vars = Array()) {
		header("HTTP/1.1 404 Not found");
		return $this->view("404", $vars);
	}
	protected function accessDenied() {
		header("HTTP/1.1 403 Forbidden");
		return $this->view("403");
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

	// Do not include html backbone
	protected function viewBare($name, $variables = Array()) {
		$View = new View(null, $this->name, $name, $variables);
		try {
			return $View->render();
		}
		catch (Exception $e) {
			// TODO: log exception
			return $this->internalError();
		}
	}

	protected function view($name, $variables = Array()) {
		$View = new View($this->Db, $this->name, $name, $variables);
		try {
			return $View->render();
		}
		catch (Exception $e) {
			return $this->notFound(Array("console" => $e->getMessage()));
		}
	}

	protected function connect() {
		$dbc = $this->Config->getDatabase();
		return $this->Db->connect($dbc['user'], $dbc['pass'], $dbc['db'], $dbc['host']);
	}

};