<?php
class l10n_Controller_Core extends Controller {
	
	public function acl($action) {
		return ["l10nAdmin"];
	}

	public function indexAction() {
		redirect("l10n/list");
	}

	public function exportAction() {
		$Form = $this->getForm("l10nStringExport");
		if ($Form->isSubmitted()) {
			
		}
		$this->viewData["form"] = $Form->render();
		return $this->view("export");
	}

	public function scanAction() {
		$Form = $this->getForm("l10nStringScan");
		if ($Form->isSubmitted()) {
			$values = $Form->values();
			if (!empty($values["parts"])) {
				if ($values["action"] == "add") {
					$re = $this->Model->scanAdd($values["parts"]);
					if ($re !== null) {
						setmsg(t("Added :n new strings", "en", [":n" => $re]), "success");
						refresh();
					}
				}
				else if ($values["action"] == "info") {
					$re = $this->Model->scanInfo($values["parts"]);
					if ($re !== null) {
						setmsg(t(":total strings found and :new new", "en", 
								[	":total" => $re["total"],
									":new" => $re["new"]]), "success");
					}
				}
				if ($re === null) {
					$this->defaultError();
				}
			}
		}
		$this->viewData["form"] = $Form->render();
		return $this->view("scan");
	}

	public function editAction($args = []) {
		if (empty($args[0]))
			return $this->notFound();
		$l10nString = $this->getEntity("l10nString", $args[0]);
		if (!$l10nString->id())
			return $this->notFound();
		$l10nString->loadAll();
		$languages = $this->Model->getActiveLanguages();
		$Form = $this->getForm("l10nStringEdit", ["l10nString" => $l10nString, "languages" => $languages]);
		if ($Form->isSubmitted()) {
			$values = $Form->values();
			unset($values["source"]);
			if ($this->Model->editString($l10nString, $values)) {
				setmsg(t("Translation saved"), "success");
				redirect("l10n/list");
			}
			else {
				$this->defaultError();
			}
		}
		$this->viewData["form"] = $Form->render();
		return $this->view("edit");
	}

	public function deleteAction($args = []) {
		if (empty($args[0]))
			return $this->notFound();
		$l10nString = $this->getEntity("l10nString", $args[0]);
		if (!$l10nString->id())
			return $this->notFound();
		if ($this->Model->deleteString($l10nString))
			setmsg(t("Translation deleted"), "success");
		else
			$this->defaultError();
		redirect("l10n/list");
	}

	public function listAction($args = []) {
		$q = (array_key_exists("l10n_search", $_SESSION) ? $_SESSION["l10n_search"] : null);
		$Form = $this->getForm("Search", ["q" => $q]);
		if ($Form->isSubmitted()) {
			$values = $Form->values();
			$_SESSION["l10n_search"] = $values["q"];
			refresh();
		}
		$Pager = newClass("Pager");
		$Pager->setNum($this->Model->searchNum($q));
		$this->viewData["l10n_strings"] = $this->Model->search($q, $Pager->start(), $Pager->ppp);
		$this->viewData["pager"] = $Pager->render();
		$this->viewData["search"] = $Form->render();
		return $this->view("list");
	}

}