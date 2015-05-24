<?php
class ControllerFactory_Core {

	protected $Config, $Db;


	public function __construct($Config, $Db) {
		$this->Config = &$Config;
		$this->Db = &$Db;
	}

	# uri: /controller/action/arg0/arg1/arg2/...
	public function executeUri($uri) {
		$request = $this->parseUri($uri);
		define("REQUEST_ALIAS", $request["alias"]);
		define("REQUEST_PATH", $request["path"]);
		return $this->executeControllerAction($request["controller"], $request["action"], $request["args"]);
	}

	public function executeControllerAction($controller, $action, $args = []) {
		$Controller = $this->getController($controller);
		if (!is_callable([$Controller, $action."Action"]) )
			return $Controller->notFound();
		return $Controller->action($action, $args);
	}

	public function getController($controller, $init = true) {
		$class = newClass($controller."_Controller", $this->Config, $this->Db, $init);
		if (!$class)
			$class = new Controller($this->Config, $this->Db);
		return $class;
	}

	public function parseUri($uri) {
		$request = [];
		$uri = strtolower($uri);
		if (strpos($uri, "/") === 0)
			$uri = substr($uri, 1);
		$request["alias"] = $uri;
		$row = $this->Db->getRow("SELECT * FROM `alias` WHERE status = 1 && alias = :alias", [":alias" => $uri]);
		if ($row) 
			$uri = $row->path;
		$request["path"] = $uri;
		$params = explode("/", $uri);
		$request["controller"] = (empty($params[0]) ? "page" : strtolower($params[0]));
		$request["action"] = (empty($params[1]) ? "index" : str_replace("-", "_", strtolower($params[1])));
		$request["args"] = array_slice($params, 2);
		return $request;
	}

};