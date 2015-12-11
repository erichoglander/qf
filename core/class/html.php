<?php
/**
 * Contains the Html class
 */
/**
 * The Html class
 * @author Eric Höglander
 */
class Html_Core {
	
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
	 * Config object
	 * @var \Config_Core
	 */
	protected $Config;

	/**
	 * Database object
	 * @var \Db_Core
	 */
	protected $Db;

	/**
	 * Io object
	 * @var \Io_Core
	 */
	protected $Io;

	/**
	 * Cache object
	 * @var \Cache_Core
	 */
	protected $Cache;

	/**
	 * Variable object
	 * @var \Variable_Core
	 */
	protected $Variable;

	/**
	 * User entity
	 * @var \User_Entity_Core
	 */
	protected $User;

	/**
	 * Theme object
	 * @var \Theme
	 */
	protected $Theme;


	/**
	 * Constructor
	 * @param \Config_Core      $Config
	 * @param \Db_Core          $Db
	 * @param \Io_Core          $Io
	 * @param \Cache_Core       $Cache
	 * @param \Variable_Core    $Variable
	 * @param \User_Entity_Core $User
	 */
	public function __construct($Config, $Db, $Io, $Cache, $Variable, $User) {
		$this->Config = $Config;
		$this->Db = $Db;
		$this->Io = $Io;
		$this->Cache = $Cache;
		$this->Variable = $Variable;
		$this->User = $User;
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
		$this->preRenderHtml($vars);
		clearmsgs();
		return $this->Theme->render("page", $vars);
	}

	/**
	 * Renders a menu
	 * @param  string $key
	 * @param  array  $menu
	 * @return string
	 */
	public function renderMenu($key, $menu) {
		if (empty($menu["links"]))
			return null;
		$html = '
			<div id="menu-'.$key.'" class="menu-wrapper">
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
				$class = "menu-link";
				$title = "";
				if (!empty($link["faicon"]))
					$title.= FontAwesome\Icon($link["faicon"]);
				if (!empty($link["title"]))
					$title.= xss(t($link["title"]));
				$html.= '
					<li class="menu-item menu-item-'.$key.'">';
				if (array_key_exists("href", $link)) {
					$url = $link["href"];
					$x = strpos($url, "?");
					if ($x)
						$path = substr($url, 0, $x);
					else
						$path = $url;
					if (!array_key_exists("active", $link) && $path == REQUEST_PATH)
						$link["active"] = true;
					if (!empty($link["active"]))
						$class.= " active";
					if (strpos($url, "http") !== 0 && strpos($url, "#") !== 0)
						$url = url($url, !empty($link["return"]));
					$html.= '
						<a href="'.$url.'" class="'.$class.'">'.$title.'</a>';
				}
				else {
					$html.= '
						<span class="'.$class.'">'.$title.'</span>';
				}
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
		foreach ($this->menu as $key => $menu) 
			$menus[$key] = $this->renderMenu($key, $menu);
		return $menus;
	}

	/**
	 * Get the full page title
	 * @return string
	 */
	protected function getTitle() {
		if ($this->title_full)
			return $this->title_full;
		return $this->title.$this->title_suffix;
	}

	/**
	 * Create the theme object
	 * @param  string $theme
	 * @return \Theme
	 */
	protected function getTheme($theme) {
		$class = ucwords($theme)."_Theme";
		return newClass($class, $this->Config, $this->Db, $this->Io, $this->Cache, $this->Variable, $this->User);
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
	
	/**
	 * Get an entity
	 * @param  string $name
	 * @param  int    $id
	 * @return \Entity
	 */
	protected function getEntity($name, $id = null) {
		return newClass($name."_Entity", $this->Db, $id);
	}
	
	/**
	 * Get a model
	 * @param  string $name
	 * @return \Model
	 */
	protected function getModel($name) {
		$cname = ucwords($name)."_Model";
		return newClass($cname, $this->Config, $this->Db, $this->Io, $this->Cache, $this->Variable, $this->User);
	}
	
	/**
	 * Get a form
	 * @param  string $name
	 * @param  array  $vars
	 * @return \Form
	 */
	protected function getForm($name, $vars = []) {
		return newClass($name."_Form", $this->Db, $this->Io, $this->User, $vars);
	}

};