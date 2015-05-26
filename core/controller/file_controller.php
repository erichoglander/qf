<?php
class File_Controller_Core extends Controller {
	
	public function uploadAction($args = []) {
		if (empty($args[0]))
			return $this->jsone(t("Missing file token"), "missing_token");
		if (empty($_SESSION["file_upload"][$args[0]]))
			return $this->jsone(t("Missing file information"), "missing_file_info");
		$this->viewData = $this->Model->uploadFile($_SESSION["file_upload"]);
		return $this->json();
	}

}