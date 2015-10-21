<?php
class Theme {
	
	public $name;
	public $css = [];
	public $js = [];

	protected $Config, $Db, $User;

	public function __construct($Config, $Db, $User) {
		$this->Config = &$Config;
		$this->Db = &$Db;
		$this->User = &$User;
	}

	public function render($part, $vars = []) {
		$template = $this->getTemplate($part);
		if (!$template)
			throw new Exception("Unable to find ".$part." template for ".$this->name." theme");
		$this->preRender($part, $vars);
		if ($part == "html") {
			foreach ($this->css as $css) {
				$url = fileUrl("theme/".$this->name."/css/".$css);
				if ($url)
					$vars["css"][] = $url;
			}
			foreach ($this->js as $js) {
				$url = fileUrl("theme/".$this->name."/js/".$js);
				if ($url)
					$vars["js"][] = $url;
			}
		}
		extract($vars);
		ob_start();
		include $template;
		return ob_get_clean();
	}


	protected function preRender($part, &$vars) {
	}

	protected function getTemplate($name) {
		return filePath("theme/".$this->name."/template/".$name.".php");
	}

};