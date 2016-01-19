<?php
/**
 * Contains the form controller
 */
/**
 * Form controller
 *
 * Contains asynchronous interactions with forms
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
		$this->viewData["dom"] = $this->Model->fileItem($json["structure"]);
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
		$this->viewData["dom"] = $this->Model->uploadFile($_SESSION["file_upload"][$args[0]]);
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
		$this->viewData["dom"] = $this->Model->removeFile($_SESSION["file_upload"][$args[0]], $File);
		return $this->json();
	}

}