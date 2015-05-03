<?php
class Html_Core {
	
	public $title, $title_suffix, $title_full;
	public $h1;
	public $pre_css, $pre_js, $head_end;
	public $pre_page, $post_page;
	public $pre_content, $post_content;
	public $content;
	public $theme = "admin";

	public $css = Array();
	public $js = Array();
	public $body_class = Array();

	protected $Theme, $Db;

	public function __construct($Db) {
		$this->Db = &$Db;
	}

	public function __call($name, $arguments = Array()) {
		die($name);
		if (method_exists($this, $name) && strpos($name, "render") === 0) {
			$this->loadTheme();
		}
	}

	public function renderHtml() {
			$this->loadTheme();
		$vars = Array(
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
		);
		return $this->Theme->render("html", $vars);
	}

	public function renderPage() {
		$vars = Array(
			"h1" => $this->h1,
			"pre_content" => $this->pre_content,
			"post_content" => $this->post_content,
			"content" => $this->content,
		);
		return $this->Theme->render("page", $vars);
	}


	protected function getTitle() {
		if ($this->title_full)
			return $title_full;
		return $this->title.$this->title_suffix;
	}

	protected function getTheme($theme) {
		$class = ucwords($theme)."_Theme";
		$core_class = ucwords($theme)."_Core_Theme";
		if (class_exists($class))
			return new $class($this->Db);
		else if (class_exists($core_class))
			return new $core_class($this->Db);
		else
			return null;
	}

	protected function loadTheme() {
		if (!$this->Theme) {
			$this->Theme = $this->getTheme($this->theme);
			if (!$this->Theme)
				throw new Exception("Unable to load theme \"".$this->theme."\"");
		}
	}

};