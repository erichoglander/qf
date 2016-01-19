<?php
/**
 * Contains the redirect controller
 */
/**
 * Redirect controller
 *
 * Administration of http redirects
 */
class Redirect_Controller_Core extends Controller {

	/**
	 * The access list
	 * @param  string $action
	 * @param  array  $args
	 * @return array
	 */
	public function acl($action, $args = []) {
		return ["redirectAdmin"];
	}

	/**
	 * Redirects to the redirect list
	 */
	public function indexAction() {
		redirect("redirect/list");
	}

	/**
	 * Add a redirect
	 * @return string
	 */
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

	/**
	 * Edit a redirect
	 * @param  array  $args
	 * @return string
	 */
	public function editAction($args = []) {
		if (empty($args[0]))
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

	/**
	 * Delete a redirect
	 * @param  array  $args
	 * @return string
	 */
	public function deleteAction($args = []) {
		if (empty($args[0]))
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

	/**
	 * Redirect list
	 * @return string
	 */
	public function listAction() {
		$values = (array_key_exists("redirect_list_search", $_SESSION) ? $_SESSION["redirect_list_search"] : []);
		$Form = $this->getForm("Search", ["q" => (!empty($values["q"]) ? $values["q"] : null)]);
		if ($Form->isSubmitted()) {
			$_SESSION["redirect_list_search"] = $Form->values();
			refresh();
		}
		$Pager = newClass("Pager");
		$Pager->setNum($this->Model->listSearchNum($values));
		$this->viewData["redirects"] = $this->Model->listSearch($values, $Pager->start(), $Pager->ppp);
		$this->viewData["pager"] = $Pager->render();
		$this->viewData["search"] = $Form->render();
		return $this->view("list");
	}

};