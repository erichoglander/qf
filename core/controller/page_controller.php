<?php
/**
 * Contains the page controller
 */
/**
 * Page controller
 *
 * Used for custom pages, such as the front page
 * @author Eric Höglander
 */
class Page_Controller_Core extends Controller {

  /**
   * The front page
   * @return string
   */
  public function indexAction() {
    return $this->view("index");
  }

};