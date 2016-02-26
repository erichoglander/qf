<?php
/**
 * Contains the pager class
 */

/**
 * Pager class
 *
 * Renders a pager and keeps track of current page
 * 
 * @author Eric Höglander
 */
class Pager_Core {

  /**
   * Current page
   * @var int
   */
  public $page = 1;

  /**
   * Posts Per Page or Items per page
   * @var int
   */
  public $ppp = 30;

  /**
   * How many pages to show before and after the current page
   * @var int
   */
  public $span = 7;

  /**
   * What param to fetch the current page from
   * @var string
   */
  public $get = "page";
  
  /**
   * Number of items
   * @var int
   */
  protected $num = 1;

  /**
   * URL to serve as base for the pager links
   * @var string
   */
  protected $url;


  /**
   * Constructor
   */
  public function __construct() {
    $this->setUrl(BASE_PATH.REQUEST_URI);
  }

  /**
   * Get the url for a certain page
   * @param  int
   * @return string
   */
  public function url($x) {
    $regex = "/([\&|\?])".$this->get."\=[0-9]+/";
    $url = $this->url;
    if (preg_match($regex, $url))
      $url = preg_replace($regex, "$1".$this->get."=".$x, $url);
    else if ($x)
      $url.= (strpos($url, "?") === false ? "?" : "&").$this->get."=".$x;
    return $url;
  }

  /**
   * Renders a link for a page
   * @param  int    $x     The page number
   * @param  string $html  The html to fit inside the link tag
   * @param  string $class Any extra classes for the link tag
   * @return string
   */
  public function tag($x, $html, $class = null) {
    return '<a href="'.$this->url($x).'" class="page'.($class ? " ".$class : "").'">'.$html.'</a>';
  }

  /**
   * Get the start offset for the current page
   * @return int
   */
  public function start() {
    return ($this->page-1)*$this->ppp;
  }
  
  /**
   * Setter for $url
   * @param string $url
   */
  public function setUrl($url) {
    $this->url = $url;
  }

  /**
   * Setter for $num
   * @param int $num
   */
  public function setNum($num) {
    $this->num = $num;
    $this->pages = (int) max(1, ceil($this->num/$this->ppp));
    $this->page = (empty($_GET[$this->get]) ? 0 : abs((int) $_GET[$this->get]));
    if ($this->page < 1)
      $this->page = 1;
    else if ($this->page > $this->pages)
      $this->page = $this->pages;
  }
  
  /**
   * Render the complete pager
   * @return string
   */
  public function render() {
    if ($this->pages < 2)
      return "";
    $min = max(1, $this->page-$this->span);
    $max = min($this->pages, $this->page+$this->span);
    $html = '<div class="pager">';
    if ($this->page != 1) {
      $html.= $this->tag(1, FontAwesome\Icon("angle-double-left"), "page-first");
      $html.= $this->tag($this->page-1, FontAwesome\Icon("angle-left"), "page-previous");
    }
    for ($i=$min; $i<=$max; $i++) {
      if ($i == $this->page)
        $html.= '<span class="page page-current">'.$i.'</span>';
      else
        $html.= $this->tag($i, $i, "page-".$i);
    }
    if ($this->page != $this->pages) {
      $html.= $this->tag($this->page+1, FontAwesome\Icon("angle-right"), "page-next");
      $html.= $this->tag($this->pages, FontAwesome\Icon("angle-double-right"), "page-last");
    }
    $html.= '</div>';
    return $html;
  }

}