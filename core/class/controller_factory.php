<?php
class ControllerFactory {

	public function __construct() {
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
		return $Controller->$action($args);
	}

	public function getController($controller) {
		$class = ucwords($controller)."_Controller";
		$core_class = ucwords($controller)."_Core_Controller";
		if (class_exists($class))
			return new $class();
		else if (class_exists($core_class))
			return new $core_class();
		else
			return new Controller();
	}

};