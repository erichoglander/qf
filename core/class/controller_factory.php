<?php
class ControllerFactory_Core {

	protected $Config, $Db;


	public function __construct() {
		$this->Config = newClass("Config");
		$this->Db = newClass("Db");
		$this->Db->debug = $this->Config->getDebug();
		if (!$this->connect()) {
			$Controller = new Controller($this->Config, $this->Db);
			return $Controller->databaseFail();
		}
	}

	# uri: /controller/action/arg0/arg1/arg2/...
	public function executeUri($uri) {
		$uri = strtolower($uri);
		if (strpos($uri, "/") === 0)
			$uri = substr($uri, 1);
		define("REQUEST_ALIAS", $uri);
		$row = $this->Db->getRow("SELECT * FROM `alias` WHERE status = 1 && alias = :alias", [":alias" => $uri]);
		if ($row) 
			$uri = $row->path;
		define("REQUEST_PATH", $uri);
		$params = explode("/", $uri);
		$controller = (empty($params[0]) ? "page" : strtolower($params[0]));
		$action = (empty($params[1]) ? "index" : str_replace("-", "_", strtolower($params[1])));
		$args = array_slice($params, 2);
		return $this->executeControllerAction($controller, $action, $args);
	}

	public function executeControllerAction($controller, $action, $args = []) {
		$Controller = $this->getController($controller);
		if (!is_callable([$Controller, $action."Action"]) )
			return $Controller->notFound();
		return $Controller->action($action, $args);
	}

	public function getController($controller) {
		$class = newClass($controller."_Controller", $this->Config, $this->Db);
		if (!$class)
			$class = new Controller($this->Config, $this->Db);
		return $class;
	}


	protected function connect() {
		$dbc = $this->Config->getDatabase();
		return $this->Db->connect($dbc['user'], $dbc['pass'], $dbc['db'], $dbc['host']);
	}

};