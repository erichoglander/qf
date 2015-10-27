<?php
class Theme {
	
	public $name;
	public $css = [];
	public $js = [];

	protected $Config, $Db, $Io, $Cache, $Variable, $User;

	public function __construct($Config, $Db, $Io, $Cache, $Variable, $User) {
		$this->Config = $Config;
		$this->Db = $Db;
		$this->Io = $Io;
		$this->Cache = $Cache;
		$this->Variable = $Variable;
		$this->User = $User;
		$this->loadFiles();
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


	protected function loadFiles() {
	}
	protected function preRender($part, &$vars) {
	}

	protected function getTemplate($name) {
		return filePath("theme/".$this->name."/template/".$name.".php");
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