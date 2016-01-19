<?php
/**
 * Contains the file controller
 */
/**
 * File controller
 *
 * Contains non-direct interaction with managed files
 * @author Eric Höglander
 */
class File_Controller_Core extends Controller {
	
	/**
	 * The access list
	 * @param  string $action
	 * @param  array  $args
	 * @return array
	 */
	public function acl($action, $args = []) {
		$acl = [];
		if ($action == "private")
			$acl[] = "filePrivate";
		return $acl;
	}
	
	/**
	 * Access a private file
	 * @param  array $args
	 * @return mixed
	 */
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