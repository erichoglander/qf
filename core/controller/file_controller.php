<?php
class File_Controller_Core extends Controller {
	
	public function acl($action, $args = []) {
		if ($action == "remove")
			return ["fileRemove"];
	}
	
	public function uploadAction($args = []) {
		if (empty($args[0]))
			return $this->jsone(t("Missing file token"), "missing_token");
		if (empty($_SESSION["file_upload"][$args[0]]))
			return $this->jsone(t("Missing file information"), "missing_file_info");
		$this->viewData["dom"] = $this->Model->uploadFile($_SESSION["file_upload"][$args[0]]);
		return $this->json();
	}
	
	public function removeAction($args = []) {
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