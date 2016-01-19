<?php
/**
 * Contains the cache controller
 */
/**
 * Cache controller
 * 
 * Administration of caches
 */
class Cache_Controller_Core extends Controller {
	
	/**
	 * The access list
	 * @param  string $action
	 * @param  array  $args
	 * @return array
	 */
	public function acl($action, $args = []) {
		return ["cacheClear"];
	}

	/**
	 * Clear all caches or a type of cache
	 * @param array $args
	 */
	public function clearAction($args = []) {
		$part = (!empty($args[0]) ? $args[0] : null);
		if ($part == "data")
			$this->Cache->clearData();
		else if ($part == "images")
			$this->Cache->clearImageStyles();
		else
			$this->Cache->clear();
		setmsg(t("Cache cleared!"), "success");
		redirect();
	}

	/**
	 * Delete a database cache
	 * @param array $args
	 */
	public function deleteAction($args = []) {
		if (empty($args[0]))
			return $this->notFound();
		$this->Model->delete($args[0]);
		setmsg(t("Cache :name deleted!", "en", [":name" => $name]), "success");
		redirect("cache/list");
	}

	/**
	 * Cache list
	 * @return string
	 */
	public function listAction() {
		$this->viewData["caches"] = $this->Model->getCaches();
		return $this->view("list");
	}

}