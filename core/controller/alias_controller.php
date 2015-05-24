<?php
class Alias_Controller_Core extends Controller {

	public function acl($action, $args = []) {
		return "aliasAdmin";
	}

	public function indexAction() {
		redirect("alias/list");
	}

	public function addAction() {
		$Form = $this->getForm("AliasEdit");
		if ($Form->isSubmitted()) {
			if ($this->Model->addAlias($Form->values())) {
				setmsg(t("Alias added"), "success");
				redirect("alias/list");
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
		$Alias = $this->getEntity("Alias", $args[0]);
		if (!$Alias->id())
			return $this->notFound();
		$Form = $this->getForm("AliasEdit", [
			"id" => $Alias->id(),
			"path" => $Alias->get("path"),
			"alias" => $Alias->get("alias"),
		]);
		if ($Form->isSubmitted()) {
			if ($this->Model->editAlias($Alias, $Form->values())) {
				setmsg(t("Alias saved"), "success");
				redirect("alias/list");
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
		$Alias = $this->getEntity("Alias", $args[0]);
		if (!$Alias->id())
			return $this->notFound();
		$Form = $this->getForm("Confirm", [
			"text" => t("Are you sure you want to delete the alias :alias?", "en", [":alias" => $Alias->get("alias")]),
		]);
		if ($Form->isSubmitted()) {
			if ($this->Model->deleteAlias($Alias)) {
				setmsg(t("Alias deleted"), "success");
				redirect("alias/list");
			}
			else {
				$this->defaultError();
			}
		}
		$this->viewData["form"] = $Form->render();
		return $this->view("delete");
	}

	public function listAction() {
		$this->viewData["aliases"] = $this->Model->getAliases();
		return $this->view("list");
	}

};