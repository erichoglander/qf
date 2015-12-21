<?php
/**
 * Contains theme class
 */

/**
 * Theme class
 *
 * A base to be extended by themes
 * 
 * @author Eric Höglander
 */
class Theme {
	
	/**
	 * Name of the theme
	 * @var string
	 */
	public $name;

	/**
	 * Css files
	 * @var array
	 */
	public $css = [];

	/**
	 * Js files
	 */
	public $js = [];


	/**
	 * Config object
	 * @var \Config_Core
	 */
	protected $Config;

	/**
	 * Database object
	 * @var \Db_Core
	 */
	protected $Db;

	/**
	 * Io object
	 * @var \Io_Core
	 */
	protected $Io;

	/**
	 * Cache object
	 * @var \Cache_Core
	 */
	protected $Cache;

	/**
	 * Variable object
	 * @var \Variable_Core
	 */
	protected $Variable;

	/**
	 * User entity
	 * @var \User_Entity_Core
	 */
	protected $User;


	/**
	 * Constructor
	 * @param \Config_Core      $Config
	 * @param \Db_Core          $Db
	 * @param \Io_Core          $Io
	 * @param \Cache_Core       $Cache
	 * @param \Variable_Core    $Variable
	 * @param \User_Entity_Core $User
	 */
	public function __construct($Config, $Db, $Io, $Cache, $Variable, $User) {
		if (!$this->name)
			throw new Exception("Missing theme name.");
		$this->Config = $Config;
		$this->Db = $Db;
		$this->Io = $Io;
		$this->Cache = $Cache;
		$this->Variable = $Variable;
		$this->User = $User;
		$this->loadFiles();
	}

	/**
	 * Renders a theme template
	 * @param  string $part
	 * @param  array  $vars
	 * @return string
	 */
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


	/**
	 * Load theme files
	 */
	protected function loadFiles() {
	}

	/**
	 * Called before rendering a theme template
	 * @param  string $part
	 * @param  array  &$vars
	 */
	protected function preRender($part, &$vars) {
	}

	/**
	 * Get file path of a theme template
	 * @param  string $name
	 * @return string
	 */
	protected function getTemplate($name) {
		return filePath("theme/".$this->name."/template/".$name.".php");
	}
	
	/**
	 * Returns an entity object
	 * @param  string $name
	 * @param  int    $id
	 * @return \Entity
	 */
	protected function getEntity($name, $id = null) {
		return newClass($name."_Entity", $this->Db, $id);
	}
	
	/**
	 * Returns a model object
	 * @param  string $name
	 * @return \Model
	 */
	protected function getModel($name) {
		$cname = ucwords($name)."_Model";
		return newClass($cname, $this->Config, $this->Db, $this->Io, $this->Cache, $this->Variable, $this->User);
	}
	
	/**
	 * Returns a form object
	 * @param  string $name
	 * @param  array  $vars
	 * @return \Form
	 */
	protected function getForm($name, $vars = []) {
		return newClass($name."_Form", $this->Db, $this->Io, $this->User, $vars);
	}

};