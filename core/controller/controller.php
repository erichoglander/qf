<?php
class Controller {

	public $args = [];
	public $connected;
	protected $action, $viewData = [];
	protected $Config, $Db, $Model, $User, $Io;

	public function __construct() {
		$this->Config = newClass("Config");
		$this->Db = newClass("Db");
		$this->Db->debug = $this->Config->getDebug();
		$this->Io = newClass("Io");
		$this->name = $this->getName();
		if ($this->connect()) {
			$this->connected = true;
			$this->User = $this->getUser();
			$this->Model = $this->getModel();
		}
		else {
			$this->connected = false;
		}
		$this->loadLibraries();
	}

	public function callAction($action, $args = []) {
		$action.= "Action";
		if (!$this->connected)
			return $this->databaseFail();
		if (!$this->access($action, $args))
			return $this->accessDenied();
		if (!method_exists($this, $action)) 
			return $this->notFound();
		return $this->$action($args);
	}

	public function access($action, $args = []) {
		return true;
	}

	public function internalError() {
		header("HTTP/1.1 500 Internal error");
		return $this->viewBare("500");
	}
	public function databaseFail() {
		if ($this->Config->getDebug())
			die(pr($this->Db->getErrors(), 1));
		return $this->serverBusy();
	}
	public function serverBusy() {
		header("HTTP/1.1 503 Service unavailable");
		return $this->viewBare("503");
	}
	public function notFound() {
		header("HTTP/1.1 404 Not found");
		return $this->viewDefault("404");
	}
	public function accessDenied() {
		header("HTTP/1.1 403 Forbidden");
		return $this->viewDefault("403");
	}


	protected function getName() {
		$class = get_class($this);
		if ($class == "Controller")
			return "default";
		return strtolower(str_replace("_Controller", "", str_replace("_Core", "", $class)));
	}

	protected function getModel() {
		$cname = ucwords($this->name)."_Model";
		return newClass($cname, $this->Config, $this->Db, $this->Io, $this->User);
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

	protected function getForm($name, $vars = []) {
		return newClass($name."_Form", $this->Db, $this->Io, $this->User, $vars);
	}

	protected function loadLibraries() {
		foreach ($this->Config->getLibraries() as $lib) {
			$Library = newClass($lib."_Library", $this->Db);
			if (!$Library)
				continue;
			foreach ($Library->getIncludes() as $incl) {
				$uri = "library/".classToDir($lib)."/".$incl;
				$path = filePath($uri);
				if ($path)
					require_once($path);
			}
		}
	}

	protected function view($name) {
		$View = newClass("View", $this->Db, $this->name, $name, $this->viewData);
		try {
			return $View->render();
		}
		catch (Exception $e) {
			$this->viewData["console"] = $e->getMessage();
			return $this->notFound();
		}
	}
	protected function viewDefault($name) {
		$View = newClass("View", $this->Db, "default", $name, $this->viewData);
		try {
			return $View->render();
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
	}
	protected function viewBare($name) {
		$View = newClass("View", null, "default", $name, $this->viewData);
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