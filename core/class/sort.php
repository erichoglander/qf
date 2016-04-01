<?php
/**
 * Contains the sort class
 */

/**
 * Sort class
 * @author Eric Höglander
 */
class Sort_Core {
  
  /**
   * The possible sortings, ex. ["created", "title"]
   * @var array
   */
  public $sorts = [];
  
  /**
   * Current sort, ex. "created", or "title"
   * @var string
  */
  public $sort;
  
  /**
   * Current order, "asc" or "desc"
   * @var string
   */
  public $order;
  
  /**
   * Constructor
   * @param array $sorts
   * @param string $sort
   * @param string $order
   */
  public function __construct($sorts, $sort = null, $order = "asc") {
    $this->sorts = $sorts;
    if ($sort) {
      $this->sort = $sort;
      $this->order = $order;
    }
    $this->get();
  }
  
  /**
   * Get sorting and order from $_GET
   */
  public function get() {
    $sort = (array_key_exists("sort", $_GET) ? $_GET["sort"] : null);
    $order = (array_key_exists("order", $_GET) ? $_GET["order"] : null);
    if ($sort && in_array($sort, $this->sorts)) {
      $this->sort = $sort;
      if ($order)
        $this->order = ($order == "desc" ? "desc" : "asc");
    }
  }
  
  /**
   * Creates an url for sorting, uses REQUEST_URI as a base
   * @param string $str
   * @param string $def
   * @return string
   */
  public function url($str, $def = "asc") {
    $url = REQUEST_URI;
    $regex_sort = "/([\?|\&])sort\=[^\&]+/i";
    $regex_order = "/([\?|\&])order\=[a-z0-9\-\_]+/i";
    if ($str == $this->sort)
      $order = ($this->order == "desc" ? "asc" : "desc");
    else
      $order = $def;
    if (preg_match($regex_sort, $url)) 
      $url = preg_replace($regex_sort, "$1sort=".$str, $url);
    else 
      $url.= (strpos($url, "?") ? "&" : "?")."sort=".$str;
    if (preg_match($regex_order, $url))
      $url = preg_replace($regex_order, "$1order=".$order, $url);
    else
      $url.= "&order=".$order;
    return BASE_PATH.$url;
  }
  
  /**
   * Combines $sort and $order to easily fit into an SQL statement
   * @return string
   */
  public function sql() {
    if (!$this->sort)
      return null;
    return $this->sort." ".$this->order;
  }
  
}