<?php
/**
 * Contains the library class
 */

/**
 * Library class
 *
 * A base to be extended by libraries
 * 
 * @author Eric Höglander
 */
class Library {

  /**
   * Name of the library
   * @var string
   */
  public $name;

  /**
   * Css files
   * @var array
   */
  public $css = [];

  /**
   * Js files
   * @var array
   */
  public $js = [];

  /**
   * Files to included
   * @var array
   */
  public $includes = [];

  /**
   * Database object
   * @var \Db_Core
   */
  protected $Db;


  /**
   * Constructor
   * @param \Db_Core $Db
   */
  public function __construct($Db) {
    $this->Db = $Db;
    $this->name = $this->parseName();
    $this->loadFiles();
  }
  
  /**
   * Load library files
   */
  public function loadFiles() {
  }

  /**
   * Get libraries css files
   * @return array
   */
  public function getCss() {
    $arr = [];
    foreach ($this->css as $css)
      $arr[] = fileUrl("library/".$this->name."/".$css);
    return $arr;
  }

  /**
   * Get library js files
   * @return array
   */
  public function getJs() {
    $arr = [];
    foreach ($this->js as $js)
      $arr[] = fileUrl("library/".$this->name."/".$js);
    return $arr;
  }

  /**
   * Get library includes
   * @return array
   */
  public function getIncludes() {
    return $this->includes;
  }


  /**
   * Returns the name of the library, based on the class name
   * @return string
   */
  protected function parseName() {
    $class = get_class($this);
    $x = strpos($class, "_");
    if ($x)
      $class = substr($class, 0, $x);
    return classToDir($class);
  }
  
};
