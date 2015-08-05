<?php
class View_Core {
	
	protected $controller_name;
	protected $name;
	protected $User;
	protected $Html;


	public function __construct($Db, $User, $controller_name, $name, $variables = []) {
		$this->User = &$User;
		$this->controller_name = $controller_name;
		$this->name = $name;
		$this->variables = $variables;
		if ($Db) {
			$this->Html = newClass("Html", $Db, $this->User);
			$this->Html->title = ucwords($controller_name)." ".$name;
		}
	}

	public function render() {
		if (!empty($this->variables["html"])) {
			foreach ($this->variables["html"] as $key => $var)
				$this->Html->{$key} = $var;
			unset($this->variables["html"]);
		}
		if (IS_FRONT_PAGE)
			$this->Html->body_class[] = "front";
		$this->Html->body_class[] = cssClass("page-".$this->controller_name."-".$this->name);
		$this->Html->body_class[] = cssClass("controller-".$this->controller_name);
		$this->Html->body_class[] = cssClass("action-".$this->name);
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
		$path = filePath("view/".$this->controller_name."/".$this->name.".php");
		if ($path)
			return $path;
		else
			throw new Exception("Unable to find view ".$this->controller_name."/".$this->name);
	}

};