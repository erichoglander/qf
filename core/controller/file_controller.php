<?php
class File_Controller_Core extends Controller {
	
	public function acl($action, $args = []) {
		$acl = [];
		if ($action == "private")
			$acl[] = "filePrivate";
		return $acl;
	}
	
	public function privateAction($args = []) {
		$uri = implode("/", $args);
		if (empty($uri))
			return $this->notFound();
		$File = $this->Model->getPrivateFileFromUri($uri);
		if (!$File)
			return $this->notFound();
		if (!$this->Acl->access($this->User, "filePrivateUri", $uri))
			return $this->accessDenied();
		$File->prompt();
	}
	
}