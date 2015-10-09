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
		define("REQUEST_URI", $request["uri"]);
		define("QUERY_STRING", $request["query"]);
		define("LANG", $request["lang"]);
		define("BASE_URL", $request["base"]);
		if (IS_CLI)
			define("BASE_PATH", DOC_ROOT."/");
		else
			define("BASE_PATH", substr($_SERVER["SCRIPT_NAME"], 0, strrpos($_SERVER["SCRIPT_NAME"], "/")+1));
		define("REQUEST_ALIAS", $request["alias"]);
		define("REQUEST_PATH", $request["path"]);
		define("IS_FRONT_PAGE", $request["controller"] == "page" && $request["action"] == "index");
		define("PUBLIC_URI", BASE_PATH.$this->Config->getPublicUri());
		define("PRIVATE_URI", BASE_URL.$this->Config->getPrivateUri());
		define("PUBLIC_PATH", $this->Config->getPublicPath());
		define("PRIVATE_PATH", $this->Config->getPrivatePath());
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
		
		// Remove leading slash if there is one
		$uri = strtolower($uri);
		if (strpos($uri, "/") === 0)
			$uri = substr($uri, 1);
		
		$request = [
			"uri" => $uri,
			"query" => null,
			"lang" => $this->Config->getDefaultLanguage(),
			"base" => substr($_SERVER["SCRIPT_NAME"], 0, strrpos($_SERVER["SCRIPT_NAME"], "/")+1),
		];
		$redir = [];

		// Language
		if ($this->Db->connected) {
			if ($this->Config->getLanguageDetection() == "path") {
				$lang = substr($uri, 0, 2);
				$language = $this->Db->getRow("
					SELECT * FROM `language` 
					WHERE 
						lang = :lang &&
						status = 1",
					[":lang" => $lang]);
				if ($language) {
					$uri = substr($uri, 3);
					$request["lang"] = $language->lang;
					$request["base"].= $language->lang."/";
				}
				else if (!IS_CLI) {
					$redir["uri"] = $this->Config->getDefaultLanguage()."/".$uri;
				}
			}
		}
			
		$request["alias"] = $request["path"] = $uri;

		// Query string
		$x = strpos($request["path"], "?");
		if ($x !== false) {
			$request["query"] = substr($request["path"], $x+1);
			$request["path"] = substr($request["path"], 0, $x);
		}
			
		if ($this->Db->connected) {
			// Alias
			if ($request["path"]) {
				$request["alias"] = $request["path"];
				$alias = $this->Db->getRow("SELECT * FROM `alias` WHERE status = 1 && alias = :alias", [":alias" => $request["path"]]);
				if ($alias) 
					$request["path"] = $alias->path;
			}

			// Redirects
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
			if (!array_key_exists("uri", $redir)) {
				$redirect = $this->Db->getRow("SELECT * FROM `redirect` WHERE status = 1 && (source = :alias || source = :path)", 
						[":alias" => $request["alias"], ":path" => $request["path"]]);
				if ($redirect) {
					$redir["uri"] = $redirect->target;
					$redir["code"] = $redirect->code;
				}
			}
			if (!empty($redir)) {
				$request["redirect"] = [
					"location" => 
						(!empty($redir["protocol"]) ? $redir["protocol"] : HTTP_PROTOCOL)."://".
						(!empty($redir["host"]) ? $redir["host"] : $_SERVER["HTTP_HOST"]).
						(!empty($redir["uri"]) ? "/".$redir["uri"] : $uri),
					"code" => (!empty($redir["code"]) ? $redir["code"] : null)
				];
			}
		}
		
		$params = explode("/", $request["path"]);
		
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