<?php
/**
 * Contains the form controller
 */
/**
 * Form controller
 *
 * Contains asynchronous interactions with forms
 * @author Eric Höglander
 */
class Form_Controller_Core extends Controller {
  
  /**
   * The access list
   * @param  string $action
   * @param  array  $args
   * @return mixed
   */
  public function acl($action, $args = []) {
    if ($action == "fileremove")
      return ["formFileRemove"];
    if ($action == "autocomplete")
      return ["formAutocomplete"];
    if ($action == "itemupload")
      return ["formItemUpload"];
    return null;
  }
  
  /**
   * Get a form item based on given structure
   *
   * Structure is sent as json in request body
   * @return string
   */
  public function additemAction() {
    $json = getjson(true);
    if (empty($json) || empty($json["structure"]))
      return $this->jsone(t("No data"));
    $FormItem = $this->Model->formItem($json["structure"]);
    $this->viewData["dom"] = JsonToHtml\htmlToJson($FormItem->render());
    if ($FormItem->add_callback)
      $this->viewData["callback"] = $FormItem->add_callback;
    return $this->json();
  }
  
  /**
   * Validate a form item
   * @return string
   */
  public function validateitemAction() {
    if (empty($_POST) || empty($_POST["__form_popup_structure"]))
      return $this->jsone(t("No data"));
    $structure = @json_decode($_POST["__form_popup_structure"], true);
    $structure["submitted"] = true;
    $FormItem = $this->Model->formItem($structure);
    $this->viewData["validated"] = $FormItem->validated();
    $this->viewData["dom"] = JsonToHtml\htmlToJson($FormItem->render());
    return $this->json();
  }
  
  /**
   * Upload a file
   *
   * A token is sent in $args so information about the upload
   * can be retrieved from the session 
   * @param  array $args
   * @return string
   */
  public function fileuploadAction($args = []) {
    if (empty($args[0]))
      return $this->jsone(t("Missing file token"), "missing_token");
    if (empty($_SESSION["file_upload"][$args[0]]))
      return $this->jsone(t("Missing file information"), "missing_file_info");
    $FormItem = $this->Model->uploadFile($_SESSION["file_upload"][$args[0]]);
    $this->viewData["dom"] = JsonToHtml\htmlToJson($FormItem->render());
    return $this->json();
  }
  
  /**
   * Remove a file from a form
   *
   * A token is sent in @args along with file ID so information 
   * about the upload and the file can be retrieved
   * @param  array $args
   * @return string
   */
  public function fileremoveAction($args = []) {
    if (empty($args[0]))
      return $this->jsone(t("Missing file token"), "missing_token");
    if (empty($_SESSION["file_upload"][$args[0]]))
      return $this->jsone(t("Missing file information"), "missing_file_info");
    if (empty($args[1]))
      return $this->jsone(t("Missing file ID"), "missing_id");
    $File = $this->getEntity("File", $args[1]);
    $FormItem = $this->Model->removeFile($_SESSION["file_upload"][$args[0]], $File);
    $this->viewData["dom"] = JsonToHtml\htmlToJson($FormItem->render());
    return $this->json();
  }
  
  /**
   * Upload a file from any form item type
   * @param  array $args
   * @return string
   */
  public function itemuploadAction($args = []) {
    if (empty($args[0]))
      return $this->jsone(t("An error occurred"));
    return $this->Model->itemUpload($args[0]);
  }
  
  /**
   * Fetch items for autocomplete
   * @param  array $args
   * @return string
   */
  public function autocompleteAction($args = []) {
    if (empty($args[0]))
      return $this->jsone("Missing entity type");
    $q = (!empty($args[1]) ? urldecode($args[1]) : "");
    try {
      $this->viewData["items"] = $this->Model->autocomplete($args[0], $q);
    }
    catch (Exception $e) {
      return $this->jsone($e->getMessage());
    }
    return $this->json();
  }

}