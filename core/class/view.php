<?php
/**
 * Contains view class
 */

/**
 * View class
 * @author Eric Höglander
 */
class View_Core {
	
	/**
	 * Name of the controller
	 * @var string
	 */
	protected $controller_name;

	/**
	 * Name of the view
	 * @var string
	 */
	protected $name;

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
	 * Html object
	 * @var \Html_Core
	 */
	protected $Html;


	/**
	 * Constructor
	 * @param \Config_Core      $Config
	 * @param \Db_Core          $Db
	 * @param \Io_Core          $Io
	 * @param \Cache_Core       $Cache
	 * @param \Variable_Core    $Variable
	 * @param \User_Entity_Core $User
	 * @param string            $controller_name
	 * @param string            $name
	 * @param array             $variables
	 */
	public function __construct($Config, $Db, $Io, $Cache, $Variable, $User, $controller_name, $name, $variables = []) {
		$this->Config = $Config;
		$this->Db = $Db;
		$this->Io = $Io;
		$this->Cache = $Cache;
		$this->Variable = $Variable;
		$this->User = $User;
		$this->controller_name = $controller_name;
		$this->name = $name;
		$this->variables = $variables;
		if ($Db) {
			$this->Html = newClass("Html", $this->Config, $this->Db, $this->Io, $this->Cache, $this->Variable, $this->User);
			$this->Html->title = ucwords($controller_name)." ".$name;
		}
	}

	/**
	 * Renders the view
	 * @return string
	 */
	public function render() {
		if ($this->Html) {
			if (!empty($this->variables["html"])) {
				foreach ($this->variables["html"] as $key => $var)
					$this->Html->{$key} = $var;
				unset($this->variables["html"]);
			}
			if (IS_FRONT_PAGE)
				$this->Html->body_class[] = "front";
			$this->Html->body_class[] = cssClass("page-".$this->controller_name."-".$this->name);
			$this->Html->body_class[] = cssClass("controller-".$this->controller_name);
			$this->Html->body_class[] = cssClass("view-".$this->name);
		}
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


	/**
	 * Returns the filepath to the view
	 * @return string
	 */
	protected function path() {
		$path = filePath("view/".$this->controller_name."/".$this->name.".php");
		if ($path)
			return $path;
		else
			throw new Exception("Unable to find view ".$this->controller_name."/".$this->name);
	}

};