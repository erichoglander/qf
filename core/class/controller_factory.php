<?php
class ControllerFactory_Core {

	protected $Config, $Db;


	public function __construct($Config, $Db) {
		$this->Config = &$Config;
		$this->Db = &$Db;
	}

	public function executeUri($uri) {
		$request = $this->parseUri($uri);
		if (!empty($request["redirect"])) {
			if ($request["redirect"]["code"] == "301")
				header("HTTP/1.1 301 Moved Permanently");
			else if ($request["redirect"]["code"] == "302")
				header("HTTP/1.1 302 Moved");
			else if ($request["redirect"]["code"] == "303")
				header("HTTP/1.1 302 See Other");
			else if ($request["redirect"]["code"] == "307")
				header("HTTP/1.1 302 Temporary Redirect");
			redirect($request["redirect"]["location"]);
		}
		define("REQUEST_ALIAS", $request["alias"]);
		define("REQUEST_PATH", $request["path"]);
		define("IS_FRONT_PAGE", $request["controller"] == "page" && $request["action"] == "index");
		return $this->executeControllerAction($request["controller"], $request["action"], $request["args"]);
	}

	public function executeControllerAction($controller, $action, $args = []) {
		$Controller = $this->getController($controller);
		if (!is_callable([$Controller, $action."Action"]))
			return $Controller->notFound();
		return $Controller->action($action, $args);
	}

	public function getController($controller, $init = true) {
		$class = newClass($controller."_Controller", $this->Config, $this->Db, $init);
		if (!$class)
			$class = new Controller($this->Config, $this->Db);
		return $class;
	}

	# uri: /controller/action/arg0/arg1/arg2/...
	public function parseUri($uri) {
		$request = [];
		$uri = strtolower($uri);
		if (strpos($uri, "/") === 0)
			$uri = substr($uri, 1);

		// Query string
		$x = strpos($uri, "?");
		if ($x !== false) {
			$request["query"] = substr($uri, $x+1);
			$uri = substr($uri, 0, $x);
		}
		else {
			$request["query"] = null;
		}

		if ($this->Db->connected) {
			// Alias
			$request["alias"] = $uri;
			$alias = $this->Db->getRow("SELECT * FROM `alias` WHERE status = 1 && alias = :alias", [":alias" => $uri]);
			if ($alias) 
				$uri = $alias->path;
			$request["path"] = $uri;

			// Redirects
			$redir = [];
			if ($this->Config->getHttps() && HTTP_PROTOCOL != "https")
				$redir["protocol"] = "https";
			$sub = $this->Config->getSubdomain();
			if ($sub) {
				if (strpos($_SERVER["HTTP_HOST"], "://".$sub.".") === false) {
					if (BASE_DOMAIN == $_SERVER["HTTP_HOST"])
						$redir["host"] = $sub.".".BASE_DOMAIN;
					else
						$redir["host"] = preg_replace("/^[^\.]+/", $sub, $_SERVER["HTTP_HOST"]);
				}
			}
			$redirect = $this->Db->getRow("SELECT * FROM `redirect` WHERE status = 1 && (source = :alias || source = :path)", 
					[":alias" => $request["alias"], ":path" => $request["path"]]);
			if ($redirect) {
				$redir["uri"] = $redirect->target;
				$redir["code"] = $redirect->code;
			}
			if (!empty($redir)) {
				$request["redirect"] = [
					"location" => 
						(!empty($redir["protocol"]) ? $redir["protocol"] : HTTP_PROTOCOL)."://".
						(!empty($redir["host"]) ? $redir["host"] : $_SERVER["HTTP_HOST"]).
						(!empty($redir["uri"]) ? $redir["uri"] : $_SERVER["REQUEST_URI"]),
					"code" => (!empty($redir["code"]) ? $redir["code"] : null)
				];
			}
		}
		else {
			$request["alias"] = $request["path"] = $uri;
		}
		
		$params = explode("/", $uri);
		
		// Controller 
		if (!empty($params[0])) {
			$controller = strtolower($params[0]);
			$arr = explode("-", $controller);
			$controller = null;
			foreach ($arr as $a)
				$controller.= ucwords($a);
			$request["controller"] = $controller;
		}
		else {
			$request["controller"] = "page";
		}
		
		// Action
		if (!empty($params[1])) {
			$action = strtolower($params[1]);
			$arr = explode("-", $action);
			foreach ($arr as $i => $a) {
				if ($i == 0)
					$action = $a;
				else
					$action.= ucwords($a);
			}
			$request["action"] = $action;
		}
		else {
			$request["action"] = "index";
		}

		// Summarize
		$request["args"] = array_slice($params, 2);
		return $request;
	}

};