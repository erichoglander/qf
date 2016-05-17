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
    else if ($action == "imagestyle")
      $acl[] = "fileImagestyle";
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
  
  /**
   * View a styled image on demand
   * @param  array $args
   * @return mixed
   */
  public function imagestyleAction($args = []) {
    if (empty($args))
      return $this->notFound();
    $style = array_shift($args);
    $uri = implode("/", $args);
    if (empty($uri))
      return $this->notFound();
    $File = $this->getEntity("File");
    if ($this->Io->validate($uri, "uint"))
      $File->load($uri);
    else
      $File->loadFromUri($uri);
    if (!$File->id())
      return $this->notFound();
    if ($File->get("dir") == "private" && !$this->Acl->access($this->User, "filePrivateUri", $File->get("uri")))
      return $this->accessDenied();
    $Imagestyle = newClass("Imagestyle", $File->path());
    if (!$Imagestyle->style($style, false))
      return $this->notFound();
    $Imagestyle->output();
  }
  
}