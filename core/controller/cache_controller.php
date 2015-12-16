<?php
class Cache_Controller_Core extends Controller {
	
	public function acl($action) {
		return ["cacheClear"];
	}

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

	public function deleteAction($args = []) {
		if (empty($args[0]))
			return $this->notFound();
		$this->Model->delete($args[0]);
		setmsg(t("Cache :name deleted!", "en", [":name" => $name]), "success");
		redirect("cache/list");
	}

	public function listAction() {
		$this->viewData["caches"] = $this->Model->getCaches();
		return $this->view("list");
	}

}