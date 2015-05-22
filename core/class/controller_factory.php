<?php
class ControllerFactory_Core {

	# uri: /controller/action/arg0/arg1/arg2/...
	public function executeUri($uri) {
		$uri = strtolower($uri);
		if (strpos($uri, "/") === 0)
			$uri = substr($uri, 1);
		// TODO: Url alias
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
		$class = newClass($controller."_Controller");
		if (!$class)
			$class = new Controller();
		return $class;
	}

};