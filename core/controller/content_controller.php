<?php
/**
 * Contains the content controller
 */
/**
 * Content controller
 *
 * Administration of content
 * @author Eric Höglander
 */
class Content_Controller_Core extends Controller {
	
	/**
	 * The access list
	 * @param  string $action
	 * @param  array  $args
	 * @return array
	 */
	public function acl($action, $args = []) {
		$acl = ["contentAdmin"];
		if ($action == "view")
			$acl[] = "contentView";
		else if ($action == "edit" || $action == "list")
			$acl[] = "contentEdit";
		else if ($action == "delete" || $action == "list")
			$acl[] = "contentDelete";
		else if ($action == "config" || $action == "list")
			$acl[] = "contentConfig";
		return $acl;
	}

	/**
	 * Add content a piece of content
	 * @return string
	 */
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

	/**
	 * Configure a piece of content
	 * @param  array  $args
	 * @return string
	 */
	public function configAction($args = []) {
		if (empty($args[0]))
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

	/**
	 * Edit a piece of content
	 * @param  array   $args
	 * @return string
	 */
	public function editAction($args = []) {
		if (empty($args[0]))
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

	/**
	 * Delete a piece of content
	 * @param  array  $args
	 * @return string
	 */
	public function deleteAction($args = []) {
		if (empty($args[0]))
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

	/**
	 * Content list
	 * @return string
	 */
	public function listAction() {
		$this->viewData["access_config"] = $this->Acl->access($this->User, $this->acl("config"));
		$this->viewData["access_delete"] = $this->Acl->access($this->User, $this->acl("delete"));
		$this->viewData["access_edit"] = $this->Acl->access($this->User, $this->acl("edit"));
		$this->viewData["contents"] = $this->Model->getContents();
		return $this->view("list");
	}

}