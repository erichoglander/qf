<?php
class Log_Controller_Core extends Controller {
	
	public function acl($action, $args = []) {
		return "logAdmin";
	}

	public function indexAction() {
		redirect("log/list");
	}

	public function viewAction($args = []) {
		if (empty($args[0]))
			return $this->notFound();
		$Log = $this->getEntity("Log", (int) $args[0]);
		if (!$Log->id())
			return $this->notFound();
		$this->viewData["Log"] = $Log;
		return $this->view("view");
	}

	public function deleteAction($args = []) {
		if (empty($args[0]))
			return $this->notFound();
		$Log = $this->getEntity("Log", $args[0]);
		if (!$Log->id())
			return $this->notFound();
		if ($this->Model->deleteLog($Log))
			setmsg(t("Log entry deleted"), "success");
		else
			$this->defaultError();
		redirect("log/list");
	}

	public function listAction() {
		$this->viewData["logs"] = $this->Model->getLogs();
		return $this->view("list");
	}

}