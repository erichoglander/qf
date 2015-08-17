<?php
class Redirect_Controller_Core extends Controller {

	public function acl($action, $args = []) {
		return "redirectAdmin";
	}

	public function indexAction() {
		redirect("redirect/list");
	}

	public function addAction() {
		$Form = $this->getForm("RedirectEdit");
		if ($Form->isSubmitted()) {
			if ($this->Model->addRedirect($Form->values())) {
				setmsg(t("Redirect added"), "success");
				redirect("redirect/list");
			}
			else {
				$this->defaultError();
			}
		}
		$this->viewData["form"] = $Form->render();
		return $this->view("add");
	}

	public function editAction($args = []) {
		if (count($args) < 1)
			return $this->notFound();
		$Redirect = $this->getEntity("Redirect", $args[0]);
		if (!$Redirect->id())
			return $this->notFound();
		$Form = $this->getForm("RedirectEdit", ["Redirect" => $Redirect]);
		if ($Form->isSubmitted()) {
			if ($this->Model->editRedirect($Redirect, $Form->values())) {
				setmsg(t("Redirect saved"), "success");
				redirect("redirect/list");
			}
			else {
				$this->defaultError();
			}
		}
		$this->viewData["form"] = $Form->render();
		return $this->view("edit");
	}

	public function deleteAction($args = []) {
		if (count($args) < 1)
			return $this->notFound();
		$Redirect = $this->getEntity("Redirect", $args[0]);
		if (!$Redirect->id())
			return $this->notFound();
		$Form = $this->getForm("Confirm", [
			"text" => t("Are you sure you want to delete the redirect :redirect?", "en", 
					[":redirect" => $Redirect->get("source")." -> ".$Redirect->get("target")]),
		]);
		if ($Form->isSubmitted()) {
			if ($this->Model->deleteRedirect($Redirect)) {
				setmsg(t("Redirect deleted"), "success");
				redirect("redirect/list");
			}
			else {
				$this->defaultError();
			}
		}
		$this->viewData["form"] = $Form->render();
		return $this->view("delete");
	}

	public function listAction() {
		$this->viewData["redirects"] = $this->Model->getRedirectes();
		return $this->view("list");
	}

};