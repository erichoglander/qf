<?php
/**
 * Contains the Html class
 */
/**
 * The Html class
 * @author Eric Höglander
 */
class Html_Core extends Model {
  
  /**
   * The page title excluding suffix
   * @see getTitle
   * @var string
   */
  public $title;

  /**
   * The page title suffix
   * @see getTitle
   * @var string
   */
  public $title_suffix;

  /**
   * If present, sets the full page title
   * @see getTitle
   * @var string
   */
  public $title_full;

  /**
   * The main heading
   * @var string
   */
  public $h1;

  /**
   * Any meta tags to include in html head
   * @var string
   */
  public $meta;

  /**
   * Html to include before the css files
   * @var string
   */
  public $pre_css;

  /**
   * Html to include before the js files
   * @var string
   */
  public $pre_js;

  /**
   * Html to include before the end of head
   * @var string
   */
  public $head_end;

  /**
   * Html to include before the page container
   * @var string
   */
  public $pre_page;

  /**
   * Html to include after the page container
   * @var string
   */
  public $post_page;

  /**
   * Html to include before the content container
   * @var string
   */
  public $pre_content;

  /**
   * Html to include after the content container
   * @var string
   */
  public $post_content;

  /**
   * The page content
   * @var string
   */
  public $content;

  /**
   * The name of the theme to render the page with
   * @var string
   */
  public $theme = "admin";

  /**
   * Css files to include on page
   * @var array
   */
  public $css = [];

  /**
   * Js files to include on page
   * @var array
   */
  public $js = [];

  /**
   * Breadcrumbs of the current page
   * @var array
   */
  public $breadcrumbs = [];

  /**
   * Css classes for the body
   * @var array
   */
  public $body_class = [];

  /**
   * Renderable menus
   * @var array
   */
  public $menu = [];

  /**
   * Theme object
   * @var \Theme
   */
  protected $Theme;


  /**
   * Constructor
   */
  public function construct() {
    $this->breadcrumbs[] = (IS_FRONT_PAGE ? t("Home") : ["", t("Home")]);
    if (!$this->title_suffix)
      $this->title_suffix = " | ".$this->Config->getSiteName();
    $this->pre_js = '
      <script>
        var REQUEST_URI = "'.REQUEST_URI.'";
        var REQUEST_PATH = "'.REQUEST_PATH.'";
        var REQUEST_ALIAS = "'.REQUEST_ALIAS.'";
        var QUERY_STRING = "'.QUERY_STRING.'";
        var IS_FRONT_PAGE = '.(IS_FRONT_PAGE ? 'true' : 'false').';
        var BASE_DOMAIN = "'.BASE_DOMAIN.'";
        var BASE_URL = "'.BASE_URL.'";
        var BASE_PATH = "'.BASE_PATH.'";
        var SITE_URL = "'.SITE_URL.'";
        var REQUEST_TIME = '.REQUEST_TIME.';
        var LANG = "'.LANG.'";
      </script>';
  }

  /**
   * Renders the html part of the theme, which includes the page part
   * @see    renderPage
   * @return string
   */
  public function renderHtml() {
    $this->loadTheme();
    $this->preProcessHtml();
    $vars = [
      "css" => $this->css,
      "js" => $this->js,
      "meta" => $this->meta,
      "pre_css" => $this->pre_css,
      "pre_js" => $this->pre_js,
      "head_end" => $this->head_end,
      "title" => $this->getTitle(),
      "body_class" => $this->body_class,
      "pre_page" => $this->pre_page,
      "post_page" => $this->post_page,
      "page" => $this->renderPage(),
      "menu" => $this->menus(),
      "breadcrumbs" => $this->breadcrumbs,
    ];
    $this->preRenderHtml($vars);
    return $this->Theme->render("html", $vars);
  }

  /**
   * Renders the page part of the theme, which includes the content
   * @return string
   */
  public function renderPage() {
    $this->loadTheme();
    $this->preProcessPage();
    $vars = [
      "h1" => $this->h1,
      "pre_content" => $this->pre_content,
      "post_content" => $this->post_content,
      "content" => $this->content,
      "menus" => $this->menus(),
      "breadcrumbs" => $this->breadcrumbs,
      "msgs" => getmsgs(),
    ];
    $this->preRenderPage($vars);
    clearmsgs();
    return $this->Theme->render("page", $vars);
  }

  /**
   * Renders a menu
   * @param  string $key
   * @param  array  $menu
   * @param  array  $options
   * @return string
   */
  public function renderMenu($key, $menu, $options = []) {
    $menu = $this->prepareMenu($menu, $options);
    if (empty($menu["links"]))
      return null;
    $html = '
      <div id="menu-'.cssClass($key).'" class="menu-wrapper">
        '.$this->renderMenuLinks($menu).'
      </div>';
    return $html;
  }

  /**
   * Renders menu links
   * @param  array  $menu
   * @param  int    $depth
   * @return string
   */
  public function renderMenuLinks($menu, $depth = 1) {
    $html = '';
    if (!empty($menu["links"])) {
      $html.= '
        <ul class="menu menu-depth-'.$depth.'">';
      foreach ($menu["links"] as $key => $link) {
        $item_class = ["menu-item", "menu-item-".$key];
        $link_class = ["menu-link"];
        $title = "";
        $attr = "";
        if (!empty($link["class"]))
          $item_class[] = " ".$link["class"];
        if (!empty($link["faicon"]))
          $title.= FontAwesome\Icon($link["faicon"]);
        if (!empty($link["title"]))
          $title.= xss(t($link["title"]));
        if (!empty($link["attributes"])) {
          foreach ($link["attributes"] as $a => $v)
            $attr.= ' '.$a.'="'.$v.'"';
        }
        $inner = null;
        if (array_key_exists("href", $link)) {
          if (!empty($link["active"])) {
            $link_class[] = " active";
            $item_class[] = " active";
          }
          if (!empty($link["active_trail"])) {
            $item_class[] = " active-trail";
          }
          $inner = '
            <a href="'.$link["href"].'" class="'.implode(" ", $link_class).'">'.$title.'</a>';
        }
        else {
          $inner = '
            <span class="'.implode(" ", $link_class).'">'.$title.'</span>';
        }
        $html.= '
          <li class="'.implode(" ", $item_class).'"'.$attr.'>'.$inner;
        $html.= $this->renderMenuLinks($link, $depth+1);
        $html.= '
          </li>';
      }
      $html.= '
        </ul>';
    }
    return $html;  
  }
  
  /**
   * Prepare menu for rendering
   * @param  array $menu
   * @param  array $options
   * @return array
   */
  public function prepareMenu($menu, $options = [], $depth = 0) {
    $min = 0;
    $max = 99;
    if (!empty($options["depth"])) {
      $min = $options["depth"][0];
      if (!empty($options["depth"][1]))
        $max = $options["depth"][1]+$min-1;
    }
    if ($depth == 0) {
      $menu["active_depth"] = $this->activateMenu($menu);
      if (!empty($options["view"])) {
        $vmin = $options["view"][0];
        $vmax = (!empty($options["view"][1]) ? $options["view"][1]+$min-1 : 99);
        $in_view = false;
        foreach ($menu["active_depth"] as $d) {
          if ($d >= $vmin && $d <= $vmax) {
            $in_view = true;
            break;
          }
        }
        if (!$in_view)
          return null;
      }
    }
    if (!empty($menu["links"])) {
      foreach ($menu["links"] as $key => $link) {
        if ($depth == $max)
          unset($menu["links"][$key]["links"]);
        if (array_key_exists("href", $link)) {
          $url = $link["href"];
          if (strpos($url, "http") !== 0 && strpos($url, "/") !== 0) {
            $u = explode("#", $url);
            $url = url($u[0], !empty($link["return"]));
            if (isset($u[1]))
              $url.= "#".$u[1];
          }
          $menu["links"][$key]["href"] = $url;
        }
        if ($depth < $min) {
          if (!empty($menu["links"][$key]["active"]) || !empty($menu["links"][$key]["active_trail"]))
            return $this->prepareMenu($menu["links"][$key], $options, $depth+1);
        }
        else {
          $menu["links"][$key] = $this->prepareMenu($menu["links"][$key], $options, $depth+1);
        }
      }
    }
    if ($depth < $min)
      return null;
    return $menu;
  }
  
  /**
   * Finds active trail in menu
   */
  public function activateMenu(&$menu, $depth = 0) {
    // Set active trail
    $active = [];
    if (!empty($menu["links"])) {
      foreach ($menu["links"] as $key => $link) {
        if (array_key_exists("href", $link)) {
          $path = preg_replace("/(\?.*)?(\#.*)?$/", "", $link["href"]);
          if (!array_key_exists("active", $link) && ($path == REQUEST_PATH || $path == REQUEST_URI)) {
            $menu["links"][$key]["active"] = true;
            $active[] = $depth;
          }
        }
        if (!empty($link["links"])) {
          $d = $this->activateMenu($menu["links"][$key], $depth+1);
          if (!empty($d)) {
            $menu["links"][$key]["active_trail"] = true;
            $active = array_merge($active, $d);
          }
        }
      }
    }
    return $active;
  }

  /**
   * Get the full page title
   * @return string
   */
  public function getTitle() {
    if ($this->title_full)
      return $this->title_full;
    return $this->title.$this->title_suffix;
  }

  /**
   * Called before the view is rendered
   */
  public function preView() {
  }
  

  /**
   * Called before the html part is rendered and before the vars are set
   */
  protected function preProcessHtml() {
  }

  /**
   * Called before the page part is rendered and before the vars are set
   */
  protected function preProcessPage() {
  }

  /**
   * Called before the html part is rendered but after the vars are set
   * @param  array &$vars
   */
  protected function preRenderHtml(&$vars) {
  }

  /**
   * Called before the page part is rendered but after the vars are set
   * @param  array &$vars
   */
  protected function preRenderPage(&$vars) {
  }

  /**
   * Renders the menus
   * @see    $menu
   * @return array
   */
  protected function menus() {
    $menus = [];
    foreach ($this->menu as $key => $menu) {
      if (empty($menu["render"])) {
        $menus[$key] = $this->renderMenu($key, $menu);
      }
      else {
        foreach ($menu["render"] as $k => $render) {
          $delta = $key."_".$k;
          $menus[$delta] = $this->renderMenu($delta, $menu, $render);
        }
      }
    }
    return $menus;
  }

  /**
   * Create the theme object
   * @param  string $theme
   * @return \Theme
   */
  protected function getTheme($theme) {
    $class = ucwords($theme)."_Theme";
    return $this->newClass($class);
  }

  /**
   * Load the theme
   */
  protected function loadTheme() {
    if (!$this->Theme) {
      $this->Theme = $this->getTheme($this->theme);
      if (!$this->Theme)
        throw new Exception("Unable to load theme '".$this->theme."'");
    }
  }

};