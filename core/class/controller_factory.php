<?php
/**
 * Contains the ControllerFactory class
 */

/**
 * Controller Factory, where the request begins
 * 
 * Parses the uri, selects the controller, 
 * and executes the action
 * @author Eric Höglander
 */
class ControllerFactory_Core {

	/**
	 * Config object
	 * @var \Config_Core
	 */
	protected $Config;

	/**
	 * Database object
	 * @var \Db_Core
	 */
	protected $Db;

	/**
	 * Constructor
	 * @param \Config_Core $Config
	 * @param \Db_Core $Db
	 */
	public function __construct($Config, $Db) {
		$this->Config = $Config;
		$this->Db = $Db;
	}

	/**
	 * Takes in an uri, sets some constants, and executes the action
	 * @param  string $uri
	 * @return string
	 */
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
		/**
		 * The requested uri without leading slash
		 * 
		 * Ex: controller/action/arg1
		 * @var string
		 */
		define("REQUEST_URI", $request["uri"]);

		/**
		 * The query string without leading question mark
		 * 
		 * Ex: param1=foo&param2=bar
		 * @var string
		 */
		define("QUERY_STRING", $request["query"]);

		/**
		 * The code for the selected language
		 * @var string
		 */
		define("LANG", $request["lang"]);

		/**
		 * The path before the uri
		 * 
		 * If the web is directly under the domain, it will contain "/"
		 * If there is a language prefix it will might "/en/" or "/sv/"
		 * Ex: /
		 * @var string
		 */
		define("BASE_URL", $request["base"]);

		if (IS_CLI)
			$base_path = DOC_ROOT."/";
		else
			$base_path = substr($_SERVER["SCRIPT_NAME"], 0, strrpos($_SERVER["SCRIPT_NAME"], "/")+1);
		/**
		 * The path before files
		 * 
		 * It's usually the same as BASE_URL, but does
		 * not contain prefixes, so it can be used locate files
		 * @var string
		 */
		define("BASE_PATH", $base_path);

		/**
		 * The alias of the current page. Contains the same value
		 * as REQUEST_PATH if there is not alias
		 * 
		 * Ex: blog/my-first-blog-post
		 * @var string
		 */
		define("REQUEST_ALIAS", $request["alias"]);

		/**
		 * The system path of the current page. Does not contain query string.
		 * 
		 * Ex: blog/view/13
		 * @var string
		 */
		define("REQUEST_PATH", $request["path"]);

		/**
		 * Whether or not the current page is the front page.
		 * @var bool
		 */
		define("IS_FRONT_PAGE", $request["controller"] == "page" && $request["action"] == "index");

		/**
		 * Uri of public files. Used in urls
		 * 
		 * Ex: files
		 * @var string
		 */
		define("PUBLIC_URI", BASE_PATH.$this->Config->getPublicUri());

		/**
		 * Uri of private files. Used in urls
		 * 
		 * Ex: file/private
		 * @var string
		 */
		define("PRIVATE_URI", BASE_URL.$this->Config->getPrivateUri());

		/**
		 * Full path of public files.
		 * 
		 * Ex: /usr/share/nginx/mysite/web/files
		 * @var string
		 */
		define("PUBLIC_PATH", $this->Config->getPublicPath());

		/**
		 * Full path of private files.
		 * 
		 * Ex: /usr/share/nginx/mysite/private
		 * @var string
		 */
		define("PRIVATE_PATH", $this->Config->getPrivatePath());
		
		return $this->executeControllerAction($request["controller"], $request["action"], $request["args"]);
	}

	/**
	 * Execute the action of the controller
	 * @param  string $controller
	 * @param  string $action
	 * @param  array $args
	 * @return string
	 */
	public function executeControllerAction($controller, $action, $args = []) {
		$Controller = $this->getController($controller);
		if (!is_callable([$Controller, $action."Action"]))
			return $Controller->notFound();
		return $Controller->action($action, $args);
	}

	/**
	 * Creates the controller of the given name
	 * @param  string $controller
	 * @param  boolean $init
	 * @return \Controller
	 */
	public function getController($controller, $init = true) {
		$class = newClass($controller."_Controller", $this->Config, $this->Db, $init);
		if (!$class)
			$class = new Controller($this->Config, $this->Db);
		return $class;
	}

	/**
	 * Parses the uri into an array we can use to determine our next move
	 * @param  string $uri
	 * @return array Keys: uri, query, lang, base, path, alias, args, redirect
	 */
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
			
		$request["path"] = $uri;

		// Query string
		$x = strpos($request["path"], "?");
		if ($x !== false) {
			$request["query"] = substr($request["path"], $x+1);
			$request["path"] = substr($request["path"], 0, $x);
		}
		
		$request["alias"] = $request["path"];
			
		if ($this->Db->connected) {
			// Alias
			if ($request["path"]) {
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
				$Redirect = newClass("Redirect_Entity", $this->Db);
				$Redirect->loadBySource($uri);
				if (!$Redirect->id()) {
					$Redirect->loadBySource($request["path"]);
					if (!$Redirect->id())
						$Redirect->loadBySource($request["alias"]);
				}
				if ($Redirect->id()) {
					$redir["uri"] = $Redirect->uri();
					$redir["code"] = $Redirect->get("code");
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