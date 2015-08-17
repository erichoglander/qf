<?php
class Library {

	public $name;
	public $css = [];
	public $js = [];
	public $includes = [];

	protected $Db;


	public function __construct(&$Db) {
		$this->Db = $Db;
		$this->name = $this->parseName();
	}

	public function getCss() {
		$arr = [];
		foreach ($this->css as $css)
			$arr[] = fileUrl("library/".$this->name."/".$css);
		return $arr;
	}

	public function getJs() {
		$arr = [];
		foreach ($this->js as $js)
			$arr[] = fileUrl("library/".$this->name."/".$js);
		return $arr;
	}

	public function getIncludes() {
		return $this->includes;
	}


	protected function parseName() {
		$class = get_class($this);
		$x = strpos($class, "_");
		if ($x)
			$class = substr($class, 0, $x);
		return classToDir($class);
	}
	
};
