<?php
class i18n_Controller_Core extends Controller {
	
	public function acl($action) {
		return ["i18nAdmin"];
	}

	public function indexAction() {
		redirect("i18n/list");
	}

	public function scanAction() {
		$Form = $this->getForm("TranslationScan");
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
		$translation = $this->Model->getTranslation($args[0]);
		$languages = $this->Model->getActiveLanguages();
		$Form = $this->getForm("TranslationEdit", ["translation" => $translation, "languages" => $languages]);
		if ($Form->isSubmitted()) {
			$values = $Form->values();
			foreach ($languages as $language) {
				if ($language->lang != $translation->lang) {
					if (isset($translation->translations[$language->lang])) 
						$translation->translations[$language->lang]->text = $values[$language->lang];
					else 
						$translation->translations[$language->lang] = (object) ["text" => $values[$language->lang]];
				}
			}
			if ($this->Model->saveTranslation($translation)) {
				setmsg(t("Translation saved"), "success");
				redirect("i18n/list");
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
		if ($this->Model->deleteTranslation($args[0]))
			setmsg(t("Translation deleted"), "success");
		else
			$this->defaultError();
		redirect("i18n/list");
	}

	public function listAction($args = []) {
		$q = (array_key_exists("i18n_search", $_SESSION) ? $_SESSION["i18n_search"] : null);
		$Form = $this->getForm("Search", ["q" => $q]);
		if ($Form->isSubmitted()) {
			$values = $Form->values();
			$_SESSION["i18n_search"] = $values["q"];
			refresh();
		}
		$Pager = newClass("Pager");
		$Pager->setNum($this->Model->searchNum($q));
		$this->viewData["translations"] = $this->Model->search($q, $Pager->start(), $Pager->ppp);
		$this->viewData["pager"] = $Pager->render();
		$this->viewData["search"] = $Form->render();
		return $this->view("list");
	}

}