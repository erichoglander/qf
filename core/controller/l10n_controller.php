<?php
/**
 * Contains the l10n controller
 */
/**
 * Localization controller
 *
 * Administration of translations
 * @author Eric Höglander
 */
class l10n_Controller_Core extends Controller {

  /**
   * The access list
   * @param  string $action
   * @param  array  $args
   * @return array
   */
  public function acl($action, $args = []) {
    $acl = ["l10nAdmin"];
    if (in_array($action, ["edit", "list"]))
      $acl[] = "l10nEdit";
    if (in_array($action, ["delete", "list"]))
      $acl[] = "l10nDelete";
    if ($action == "import")
      $acl[] = "l10nImport";
    if ($action == "export")
      $acl[] = "l10nExport";
    if ($action == "scan")
      $acl[] = "l10nScan";
    return $acl;
  }

  /**
   * Redirect to the string translation list
   */
  public function indexAction() {
    redirect("l10n/list");
  }

  /**
   * Import translations from json
   * @see    \l10nStringImport_Form_Core
   * @see    \l10n_Model_Core::import()
   * @return string
   */
  public function importAction() {
    $Form = $this->getForm("l10nStringImport");
    if ($Form->isSubmitted()) {
      try {
        $n = $this->Model->import($Form->values());
        setmsg(t(":n translations imported", "en", [":n" => $n]), "success");
        refresh();
      }
      catch (Exception $e) {
        setmsg($e->getMessage(), "error");
      }
    }
    $this->viewData["form"] = $Form->render();
    return $this->view("import");
  }

  /**
   * Export translations as json
   * @see    \l10nStringExport_Form_Core
   * @see    \l10n_Model_Core::export()
   * @return string
   */
  public function exportAction() {
    $languages = $this->Model->getActiveLanguages();
    $Form = $this->getForm("l10nStringExport", ["languages" => $languages]);
    if ($Form->isSubmitted()) {
      $values = $Form->values();
      $json = $this->Model->export($values);
      header_remove("Content-Type");
      header("Content-Type: application/octet-stream");
      if ($values["format"] == "xml")
        header("Content-Disposition: filename=l10n_strings.xml");
      else
        header("Content-Disposition: filename=l10n_strings.json");
      print $json;
      exit;
    }
    $this->viewData["form"] = $Form->render();
    return $this->view("export");
  }

  /**
   * Scan code for translation requests
   * @see    \l10nStringScan_Form_Core
   * @see    \l10n_Model_Core::scanAdd()
   * @return string
   */
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
                [  ":total" => $re["total"],
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

  /**
   * Edit a string translation
   * @param  array $args
   * @return string
   */
  public function editAction($args = []) {
    if (empty($args[0]))
      return $this->notFound();
    $l10nString = $this->getEntity("l10nString", $args[0]);
    if (!$l10nString->id())
      return $this->notFound();
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

  /**
   * Delete a string translation
   * @param  array $args
   * @return string
   */
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

  /**
   * List string translations
   * @return string
   */
  public function listAction() {
    $q = (array_key_exists("l10n_search", $_SESSION) ? $_SESSION["l10n_search"] : null);
    $Form = $this->getForm("Search", ["q" => $q]);
    if ($Form->isSubmitted()) {
      $values = $Form->values();
      $_SESSION["l10n_search"] = $values["q"];
      redirect("l10n/list");
    }
    $Pager = newClass("Pager");
    $Pager->setNum($this->Model->searchNum($q));
    $this->viewData["l10n_strings"] = $this->Model->search($q, $Pager->start(), $Pager->ppp);
    $this->viewData["pager"] = $Pager->render();
    $this->viewData["search"] = $Form->render();
    return $this->view("list");
  }

}