<?php
class Controller {

	public $args = [];
	public $connected;
	protected $Config, $Db, $Model, $User;

	public function __construct() {
		$this->Config = new Config();
		$this->Db = new Db();
		$this->Db->debug = $this->Config->getDebug();
		$this->name = $this->getName();
		if ($this->connect()) {
			$this->connected = true;
			$this->Model = $this->getModel();
			$this->User = $this->getUser();
		}
		else {
			$this->connected = false;
		}
	}

	public function callAction($action, $args = []) {
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
		if ($this->Config->getDebug())
			die(pr($this->Db->getErrors(), 1));
		return $this->serverBusy();
	}
	protected function serverBusy() {
		header("HTTP/1.1 503 Service unavailable");
		return $this->viewBare("503");
	}
	protected function notFound($vars = []) {
		header("HTTP/1.1 404 Not found");
		return $this->viewDefault("404", $vars);
	}
	protected function accessDenied() {
		header("HTTP/1.1 403 Forbidden");
		return $this->viewDefault("403");
	}

	protected function access($action, $args = []) {
		return true;
	}

	protected function getName() {
		$class = get_class($this);
		if ($class == "Controller")
			return "default";
		return strtolower(str_replace("_Controller", "", str_replace("_Core_Controller", "", $class)));
	}

	protected function getModel() {
		$cname = ucwords($this->name)."_Model";
		return newClass($cname, $this->Db);
	}

	protected function getUser() {
		$User = $this->getEntity("User");
		if (!empty($_SESSION['user_id']))
			$User->load($_SESSION['user_id']);
		return $User;
	}

	protected function getEntity($name, $id = null) {
		return newClass($name."_Entity", $this->Db, $id);
	}

	protected function view($name, $variables = []) {
		$View = new View($this->Db, $this->name, $name, $variables);
		try {
			return $View->render();
		}
		catch (Exception $e) {
			return $this->notFound(["console" => $e->getMessage()]);
		}
	}

	protected function viewDefault($name, $variables = []) {
		$View = new View($this->Db, "default", $name, $variables);
		try {
			return $View->render();
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
	}

	// Do not include html backbone
	protected function viewBare($name, $variables = []) {
		$View = new View(null, "default", $name, $variables);
		try {
			return $View->render();
		}
		catch (Exception $e) {
			die($e->getMessage());
			return $this->internalError();
		}
	}

	protected function connect() {
		$dbc = $this->Config->getDatabase();
		return $this->Db->connect($dbc['user'], $dbc['pass'], $dbc['db'], $dbc['host']);
	}

};