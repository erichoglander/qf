<?php
class Form_Controller_Core extends Controller {
	
	public function acl($action, $args = []) {
		if ($action == "fileremove")
			return ["formFileRemove"];
		return null;
	}
	
	public function additemAction($args = []) {
		$json = getjson(true);
		if (empty($json) || empty($json["structure"]))
			return $this->jsone(t("No data"));
		$this->viewData["dom"] = $this->Model->fileItem($json["structure"]);
		return $this->json();
	}
	
	public function fileuploadAction($args = []) {
		if (empty($args[0]))
			return $this->jsone(t("Missing file token"), "missing_token");
		if (empty($_SESSION["file_upload"][$args[0]]))
			return $this->jsone(t("Missing file information"), "missing_file_info");
		$this->viewData["dom"] = $this->Model->uploadFile($_SESSION["file_upload"][$args[0]]);
		return $this->json();
	}
	
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