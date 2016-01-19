<?php
/**
 * Contains the alias controller
 */
/**
 * Alias controller
 *
 * Administration of url-aliases
 * @author Eric Höglander
 */
class Alias_Controller_Core extends Controller {

	/**
	 * The access list 
	 * @param string  $action
	 * @param array   $args
	 * @return array
	 */
	public function acl($action, $args = []) {
		return ["aliasAdmin"];
	}

	/**
	 * Redirect to the alias list
	 */
	public function indexAction() {
		redirect("alias/list");
	}

	/**
	 * Add an alias
	 * @return string
	 */
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

	/**
	 * Edit an alias
	 * @param  array  $args
	 * @return string
	 */
	public function editAction($args = []) {
		if (empty($args[0]))
			return $this->notFound();
		$Alias = $this->getEntity("Alias", $args[0]);
		if (!$Alias->id())
			return $this->notFound();
		$Form = $this->getForm("AliasEdit", ["Alias" => $Alias]);
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

	/**
	 * Delete and alias
	 * @param  array  $args
	 * @return string
	 */
	public function deleteAction($args = []) {
		if (empty($args[0]))
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

	/**
	 * Alias list
	 * @return string
	 */
	public function listAction() {
		$values = (array_key_exists("alias_list_search", $_SESSION) ? $_SESSION["alias_list_search"] : []);
		$Form = $this->getForm("Search", ["q" => (!empty($values["q"]) ? $values["q"] : null)]);
		if ($Form->isSubmitted()) {
			$_SESSION["alias_list_search"] = $Form->values();
			refresh();
		}
		$Pager = newClass("Pager");
		$Pager->setNum($this->Model->listSearchNum($values));
		$this->viewData["aliases"] = $this->Model->listSearch($values, $Pager->start(), $Pager->ppp);
		$this->viewData["pager"] = $Pager->render();
		$this->viewData["search"] = $Form->render();
		return $this->view("list");
	}

};