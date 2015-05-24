<?php
class Html_Core {
	
	public $title, $title_suffix, $title_full;
	public $h1;
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

	protected $Theme, $Db;

	public function __construct($Db) {
		$this->Config = newClass("Config");
		$this->Db = &$Db;
		$this->breadcrumbs[] = ["", t("Home")];
	}

	public function renderHtml() {
		$this->loadTheme();
		$this->preRenderHtml();
		$vars = [
			"css" => $this->css,
			"js" => $this->js,
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
		return $this->Theme->render("html", $vars);
	}

	public function renderPage() {
		$this->loadTheme();
		$this->preRenderPage();
		$vars = [
			"h1" => $this->h1,
			"pre_content" => $this->pre_content,
			"post_content" => $this->post_content,
			"content" => $this->content,
			"menus" => $this->menus(),
			"breadcrumbs" => $this->breadcrumbs,
			"msgs" => getmsgs(),
		];
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
					if (!empty($link["active"]))
						$class.= " active";
					if (strpos($url, "http") !== 0 && strpos($url, "#") !== 0)
						$url = "/".$url;
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


	protected function preRenderHtml() {
	}
	protected function preRenderPage() {
	}

	protected function menus() {
		$menus = [];
		foreach ($this->menu as $key => $menu) 
			$menus[$key] = $this->renderMenu($key, $menu);
		return $menus;
	}

	protected function getTitle() {
		if ($this->title_full)
			return $title_full;
		return $this->title.$this->title_suffix;
	}

	protected function getTheme($theme) {
		$class = ucwords($theme)."_Theme";
		return newClass($class, $this->Db);
	}

	protected function loadTheme() {
		if (!$this->Theme) {
			$this->Theme = $this->getTheme($this->theme);
			if (!$this->Theme)
				throw new Exception("Unable to load theme \"".$this->theme."\"");
		}
	}

};