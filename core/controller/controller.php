<?php
class Controller {

	public $args = Array();
	protected $Db;
	protected $Model;

	public function __construct($Db) {
		$this->Db = &$Db;
		$this->name = $this->getName();
		$this->real_name = $this->getRealName();
		$this->Model = $this->getModel();
	}

	public function access($action, $args = Array()) {
		return true;
	}

	public function viewRender($name, $variables = Array()) {
		$path = $this->viewPath($name);
		if ($path) {
			extract($variables);
			ob_start();
			include $path;
			return ob_get_clean();
		}
		else {
			return null;
		}
	}


	protected function getName() {
		return strtolower(str_replace("_Controller", "", str_replace("_Core_Controller", "", get_class($this))));
	}
	protected function getRealName() {
		return str_replace("_Controller", "", get_class($this));
	}

	protected function getModel() {
		$emodel = ucwords($this->name)."_Model";
		$cmodel = $this->real_name."_Model";
		if (class_exists($emodel))
			return new $emodel($this->Db);
		else if (class_exists($cmodel))
			return new $cmodel($this->Db);
		else
			return null;
	}

	protected function viewPath($name) {
		$epath = DOC_ROOT."/extend/view/".$this->name."_".$name.".php";
		$cpath = DOC_ROOT."/core/view/".$this->name."_".$name.".php";
		if (file_exists($epath))
			return $epath;
		else if (file_exists($cpath))
			return $cpath;
		else
			return null;
	}

};