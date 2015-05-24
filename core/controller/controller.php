<?php
class Controller {

	public $args = [];
	protected $action, $viewData = [];
	protected $Config, $Db, $Model, $User, $Io;

	public function __construct($Config, $Db, $init = true) {
		$this->Config = &$Config;
		$this->Db = &$Db;
		$this->Io = newClass("Io");
		$this->name = $this->getName();
		if ($this->Db->connected) {
			$this->Acl = newClass("Acl", $this->Db);
			$this->User = $this->getUser();
			$this->Model = $this->getModel();
			if ($init) {
				$this->defaultViewData();
				$this->loadLibraries();
				$this->loadMenus();
			}
		}
		else {
			return $this->databaseFail();
		}
	}

	public function action($action, $args = []) {
		if (!$this->Acl->access($this->User, $this->acl($action, $args), $args))
			return $this->accessDenied();
		$action.= "Action";
		if (!method_exists($this, $action)) 
			return $this->notFound();
		return $this->$action($args);
	}

	public function acl($action, $args = []) {
		return null;
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
		if (!$this->User->id()) 
			redirect("user/login?redir=".REQUEST_ALIAS);
		header("HTTP/1.1 403 Forbidden");
		return $this->viewDefault("403");
	}
	public function defaultError() {
		setmsg(t("An error occurred"), "error");
	}


	protected function uriAccess($uri) {
		$CF = newClass("ControllerFactory", $this->Config, $this->Db);
		$request = $CF->parseUri($uri);
		$Controller = $CF->getController($request["controller"], false);
		$acl = $Controller->acl($request["action"], $request["args"]);
		return $this->Acl->access($this->User, $acl, $request["args"]);
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
			$this->viewData["html"]["css"] = array_merge($this->viewData["html"]["css"], $Library->getCss());
			$this->viewData["html"]["js"] = array_merge($this->viewData["html"]["js"], $Library->getJs());
		}
	}

	protected function loadMenus() {
		foreach ($this->Config->getMenus() as $key => $menu) {
			if (!empty($menu["acl"]) && !$this->Acl->access($this->User, $menu["acl"]))
				continue;
			$this->viewData["html"]["menu"][$key] = $this->menuAccess($menu);
			if (!empty($menu["body_class"]))
				$this->viewData["html"]["body_class"][] = cssClass($menu["body_class"]);
		}
	}

	protected function menuAccess($menu) {
		if (array_key_exists("href", $menu)) {
			if (!$this->uriAccess($menu["href"]))
				unset($menu["href"]);
		}
		if (!empty($menu["links"])) {
			foreach ($menu["links"] as $key => $link) {
				$re = $this->menuAccess($link);
				if (!$re)
					unset($menu["links"][$key]);
				else
					$menu["links"][$key] = $re;
			}
		}
		if (!array_key_exists("href", $menu) && empty($menu["links"]))
			return [];
		return $menu;
	}

	protected function defaultViewData() {
		$this->viewData["html"] = [
			"menu" => [],
			"body_class" => [],
			"css" => [],
			"js" => [],
		];
	}

	protected function view($name) {
		$View = newClass("View", $this->Db, $this->User, $this->name, $name, $this->viewData);
		try {
			return $View->render();
		}
		catch (Exception $e) {
			$this->viewData["console"] = $e->getMessage();
			return $this->notFound();
		}
	}
	protected function viewDefault($name) {
		$View = newClass("View", $this->Db, $this->User, "default", $name, $this->viewData);
		try {
			return $View->render();
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
	}
	protected function viewBare($name) {
		$View = newClass("View", null, null, "default", $name, $this->viewData);
		try {
			return $View->render();
		}
		catch (Exception $e) {
			die($e->getMessage());
			return $this->internalError();
		}
	}

};