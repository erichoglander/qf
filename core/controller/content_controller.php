<?php
class Content_Controller_Core extends Controller {
	
	public function acl($action) {
		$acl = ["contentAdmin"];
		if ($action == "view")
			$acl[] = "contentView";
		else if ($action == "edit")
			$acl[] = "contentEdit";
		else if ($action == "delete")
			$acl[] = "contentDelete";
		else if ($action == "config")
			$acl[] = "contentConfig";
		return $acl;
	}

	public function addAction() {
		$Form = $this->getForm("ContentConfig");
		if ($Form->isSubmitted()) {
			$Content = $this->Model->addContent($Form->values());
			if ($Content) {
				setmsg(t("Content saved"), "success");
				redirect("content/edit/".$Content->id());
			}
			else {
				$this->defaultError();
			}
		}
		$this->viewData["form"] = $Form->render();
		return $this->view("add");
	}

	public function configAction($args = []) {
		if (count($args) < 1)
			return $this->notFound();
		$Content = $this->getEntity("Content", $args[0]);
		if (!$Content->id())
			return $this->notFound();
		$Form = $this->getForm("ContentConfig", [
			"Content" => $Content, 
		]);
		if ($Form->isSubmitted()) {
			if ($this->Model->configContent($Content, $Form->values())) {
				setmsg(t("Content saved"), "success");
				redirect("content/list");
			}
			else {
				$this->defaultError();
			}
		}
		$this->viewData["form"] = $Form->render();
		return $this->view("config");
	}

	public function editAction($args = []) {
		if (count($args) < 1)
			return $this->notFound();
		$Content = $this->getEntity("Content", $args[0]);
		if (!$Content->id())
			return $this->notFound();
		$Form = $this->getForm("ContentEdit", [
			"Content" => $Content, 
			"config" => $this->Acl->access($this->User, ["contentAdmin", "contentConfig"]),
		]);
		if ($Form->isSubmitted()) {
			if ($this->Model->editContent($Content, $Form->values())) {
				setmsg(t("Content saved"), "success");
				redirect("content/list");
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
		$Content = $this->getEntity("Content", $args[0]);
		if (!$Content->id())
			return $this->notFound();
		$Form = $this->getForm("Confirm", [
			"text" => t("Are you sure you want to delete :title?", "en", [":title" => $Content->get("title")]),
		]);
		if ($Form->isSubmitted()) {
			if ($this->Model->deleteContent($Content)) {
				setmsg(t("Content deleted"), "success");
				redirect("content/list");
			}
			else {
				$this->defaultError();
			}
		}
		$this->viewData["form"] = $Form->render();
		return $this->view("edit");
	}

	public function listAction() {
		$this->viewData["contents"] = $this->Model->getContents();
		return $this->view("list");
	}

}