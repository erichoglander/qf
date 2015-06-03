<?php
class i18n_Controller_Core extends Controller {
	
	public function acl($action) {
		return ["i18nAdmin"];
	}

	public function indexAction() {
		redirect("i18n/list");
	}

	public function scanAction() {
		
	}

	public function editAction($args = []) {

	}

	public function deleteAction($args = []) {

	}

	public function listAction($args = []) {
		$Pager = newClass("Pager");
		$Pager->setNum($this->Model->getTranslationsNum());
		$this->viewData["translations"] = $this->Model->getTranslations(["start" => $Pager->start(), "stop" => $Pager->ppp]);
		$this->viewData["pager"] = $Pager->render();
		return $this->view("list");
	}

}