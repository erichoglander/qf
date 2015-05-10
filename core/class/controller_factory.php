<?php
class ControllerFactory {

	# uri: /controller/action/arg0/arg1/arg2/...
	public function executeUri($uri) {
		$uri = strtolower($uri);
		if (strpos($uri, "/") === 0)
			$uri = substr($uri, 1);
		// TODO: Url alias
		$params = explode("/", $uri);
		$n = count($params);
		$controller = (empty($params[0]) ? "page" : $params[0]);
		$action = ($n < 2 ? "index" : $params[1]);
		$args = array_slice($params, 2);
		return $this->executeControllerAction($controller, $action, $args);
	}

	public function executeControllerAction($controller, $action, $args = []) {
		$Controller = $this->getController($controller);
		if (!is_callable([$Controller, $action]) )
			return $Controller->callAction("notFound");
		return $Controller->callAction($action, $args);
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