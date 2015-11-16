<?php
class Html_Core {
	
	public $title, $title_suffix, $title_full;
	public $h1;
	public $meta;
	public $pre_css, $pre_js, $head_end;
	public $pre_page, $post_page;
	public $pre_content, $post_content;
	public $content;
	public $theme = "admin";

	public $css = [];
	public $js = [];
	public $breadcrumbs = [];
	public $body_class = [];
	public $menu = [];

	public $libraries = ["FontAwesome"];

	protected $Config, $Db, $Io, $Cache, $Variable, $User, $Theme;

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

	public function renderMenu($key, $menu) {
		if (empty($menu["links"]))
			return null;
		$html = '
			<div id="menu-'.$key.'" class="menu-wrapper">
				'.$this->renderMenuLinks($menu).'
			</div>';
		return $html;
	}
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


	protected function preProcessHtml() {
	}
	protected function preProcessPage() {
	}
	protected function preRenderHtml(&$vars) {
	}
	protected function preRenderPage(&$vars) {
	}

	protected function menus() {
		$menus = [];
		foreach ($this->menu as $key => $menu) 
			$menus[$key] = $this->renderMenu($key, $menu);
		return $menus;
	}

	protected function getTitle() {
		if ($this->title_full)
			return $this->title_full;
		return $this->title.$this->title_suffix;
	}

	protected function getTheme($theme) {
		$class = ucwords($theme)."_Theme";
		return newClass($class, $this->Config, $this->Db, $this->Io, $this->Cache, $this->Variable, $this->User);
	}

	protected function loadTheme() {
		if (!$this->Theme) {
			$this->Theme = $this->getTheme($this->theme);
			if (!$this->Theme)
				throw new Exception("Unable to load theme '".$this->theme."'");
		}
	}
	
	protected function getEntity($name, $id = null) {
		return newClass($name."_Entity", $this->Db, $id);
	}
	
	protected function getModel($name) {
		$cname = ucwords($name)."_Model";
		return newClass($cname, $this->Config, $this->Db, $this->Io, $this->Cache, $this->Variable, $this->User);
	}
	
	protected function getForm($name, $vars = []) {
		return newClass($name."_Form", $this->Db, $this->Io, $this->User, $vars);
	}

};