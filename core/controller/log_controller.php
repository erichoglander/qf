<?php
/**
 * Contains the log controller
 */
/**
 * Log controller
 * @author Eric Höglander
 */
class Log_Controller_Core extends Controller {
  
  /**
   * The access list
   * @param  string $action
   * @param  array  $args
   * @return array
   */
  public function acl($action, $args = []) {
    return ["logAdmin"];
  }

  /**
   * Redirect to the log list
   */
  public function indexAction() {
    redirect("log/list");
  }

  /**
   * View a log entry
   * @param  array  $args
   * @return string
   */
  public function viewAction($args = []) {
    if (empty($args[0]))
      return $this->notFound();
    $Log = $this->getEntity("Log", (int) $args[0]);
    if (!$Log->id())
      return $this->notFound();
    $this->viewData["Log"] = $Log;
    return $this->view("view");
  }

  /**
   * Delete a log entry
   * @param  array  $args
   * @return string
   */
  public function deleteAction($args = []) {
    if (empty($args[0]))
      return $this->notFound();
    $Log = $this->getEntity("Log", $args[0]);
    if (!$Log->id())
      return $this->notFound();
    if ($this->Model->deleteLog($Log))
      setmsg(t("Log entry deleted"), "success");
    else
      $this->defaultError();
    redirect("log/list");
  }

  /**
   * Log list
   * @return string
   */
  public function listAction() {
    $values = (array_key_exists("log_list_search", $_SESSION) ? $_SESSION["log_list_search"] : []);
    $Form = $this->getForm("Search", ["q" => (!empty($values["q"]) ? $values["q"] : null)]);
    if ($Form->isSubmitted()) {
      $_SESSION["log_list_search"] = $Form->values();
      refresh();
    }
    $Pager = newClass("Pager");
    $Pager->ppp = 50;
    $Pager->setNum($this->Model->listSearchNum($values));
    $this->viewData["logs"] = $this->Model->listSearch($values, $Pager->start(), $Pager->ppp);
    $this->viewData["pager"] = $Pager->render();
    $this->viewData["search"] = $Form->render();
    return $this->view("list");
  }

}