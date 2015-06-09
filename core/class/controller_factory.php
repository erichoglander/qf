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
			if ($request["redirect"]->code == "301")
				header("HTTP/1.1 301 Moved Permanently");
			else if ($request["redirect"]->code == "302")
				header("HTTP/1.1 302 Moved");
			else if ($request["redirect"]->code == "303")
				header("HTTP/1.1 302 See Other");
			else if ($request["redirect"]->code == "307")
				header("HTTP/1.1 302 Temporary Redirect");
			redirect($request["redirect"]);
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
		$arr = explode("-", $controller);
		$controller = null;
		foreach ($arr as $a)
			$controller.= ucwords($a);
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
			if ($redirect) 
				$redir["uri"] = $redirect->target;
			if (!empty($redir)) {
				$request["redirect"] = 
					(!empty($redir["protocol"]) ? $redir["protocol"] : HTTP_PROTOCOL)."://".
					(!empty($redir["host"]) ? $redir["host"] : $_SERVER["HTTP_HOST"]).
					(!empty($redir["uri"]) ? $redir["uri"] : $_SERVER["REQUEST_URI"]);
			}
		}

		// Summarize
		$params = explode("/", $uri);
		$request["controller"] = (empty($params[0]) ? "page" : strtolower($params[0]));
		$request["action"] = (empty($params[1]) ? "index" : str_replace("-", "_", strtolower($params[1])));
		$request["args"] = array_slice($params, 2);
		return $request;
	}

};