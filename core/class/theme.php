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
			foreach ($this->css as $css)
				$vars["css"][] = fileUrl("theme/".$this->name."/css/".$css);
			foreach ($this->js as $js)
				$vars["js"][] = fileUrl("theme/".$this->name."/js/".$js);
		}
		return renderTemplate($template, $vars);
	}


	protected function preRender($part, &$vars) {
	}

	protected function getTemplate($name) {
		return filePath("theme/".$this->name."/template/".$name.".php");
	}

};