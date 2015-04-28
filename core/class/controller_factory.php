<?php
class ControllerFactory {

	protected $Db;

	public function __construct($Db) {
		$this->Db = &$Db;
	}

	# uri: /controller/action/arg0/arg1/arg2/...
	public function executeUri($uri) {
		if (strpos($uri, "/") === 0)
			$uri = substr($uri, 1);
		$params = explode("/", $uri);
		$n = count($params);
		if ($n < 2) {
			$controller = "page";
			$action = $params[0];
			if (!$action)
				$action = "index";
			$args = Array();
		}
		else {
			$controller = $params[0];
			$action = $params[1];
			$args = array_slice($params, 2);
		}
		return $this->executeControllerAction($controller, $action, $args);
	}

	public function executeControllerAction($controller, $action, $args = Array()) {
		$Controller = $this->getController($controller);
		if ($Controller) {
			if (is_callable(Array($Controller, $action))) {
				if (!$Controller->access($action, $args)) {
					$Controller = new Error_Controller($this->Db);
					$action = "accessDenied";
				}
			}
			else {
				$action = "defaultAction";
				if (!is_callable(Array($Controller, $action)))
					$Controller = null;
			}
		}
		if (!$Controller) {
			$Controller = new Error_Controller($this->Db);
			$action = "notFound";
		}
		return $Controller->$action($args);
	}

	public function getController($controller) {
		$class = ucwords($controller)."_Controller";
		$core_class = ucwords($controller)."_Core_Controller";
		if (class_exists($class))
			return new $class($this->Db);
		else if (class_exists($core_class))
			return new $core_class($this->Db);
		else
			return null;
	}

};