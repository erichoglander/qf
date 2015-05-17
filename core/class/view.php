<?php
class View {
	
	protected $controller_name;
	protected $name;
	protected $Html;

	public function __construct($Db, $controller_name, $name, $variables = []) {
		$this->controller_name = $controller_name;
		$this->name = $name;
		$this->variables = $variables;
		if ($Db) {
			$this->Html = new Html($Db);
			$this->Html->title = ucwords($controller_name)." ".$name;
			$this->Html->body_class[] = cssClass("page-".$controller_name."-".$name);
			$this->Html->body_class[] = cssClass("controller-".$controller_name);
			$this->Html->body_class[] = cssClass("action-".$name);
		}
	}

	public function render() {
		$path = $this->path();
		extract($this->variables);
		ob_start();
		include $path;
		$output = ob_get_clean();
		if ($this->Html) {
			$this->Html->content = $output;
			$output = $this->Html->renderHtml();
		}
		return $output;
	}

	protected function path() {
		$epath = DOC_ROOT."/extend/view/".$this->controller_name."/".$this->name.".php";
		$cpath = DOC_ROOT."/core/view/".$this->controller_name."/".$this->name.".php";
		if (file_exists($epath))
			return $epath;
		else if (file_exists($cpath))
			return $cpath;
		else
			throw new Exception("Unable to find view ".$this->controller_name."/".$this->name);
	}

};