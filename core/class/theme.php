<?php
class Theme {
	
	public $name;
	public $css = Array();
	public $js = Array();

	protected $Db;

	public function __construct($Db) {
		$this->Db = &$Db;
	}

	public function getCss() {
		return $this->css;
	}
	public function getJs() {
		return $this->js;
	}

	public function render($part, $vars = Array()) {
		$template = $this->getTemplate($part);
		if (!$template)
			throw new Exception("Unable to find ".$this->name." template for ".$part);
		if ($part == "html") {
			$vars['css'] = array_merge($vars['css'], $this->css);
			$vars['js'] = array_merge($vars['js'], $this->js);
		}
		extract($vars);
		ob_start();
		include $template;
		return ob_get_clean();
	}

	public function getTemplate($name) {
		$cpath = DOC_ROOT."/core/theme/".$this->name."/template/".$name.".php";
		$epath = DOC_ROOT."/extend/theme/".$this->name."/template/".$name.".php";
		if (file_exists($epath))
			return $epath;
		else if (file_exists($cpath))
			return $cpath;
		else
			return null;
	}

};