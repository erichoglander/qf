<?php
/**
 * Contains the base model
 */
/**
 * Base model
 *
 * The model is to serve as the heavy lifter.
 * The matching model is created automatically in the controller.
 *
 * @author Eric Höglander
 */
class Model {

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
	 * @param \Config_Core $Config
	 * @param \Db_Core          $Db
	 * @param \Io_Core          $Io
	 * @param \Cache_Core       $Cache
	 * @param \Variable_Core    $Variable
	 * @param \User_Entity_Core $User
	 */
	public function __construct($Config, $Db, $Io, $Cache, $Variable, $User) {
		$this->Config = $Config;
		$this->Db = $Db;
		$this->Io = $Io;
		$this->Cache = $Cache;
		$this->Variable = $Variable;
		$this->User = $User;
	}

	
	/**
	 * Get entity of given name
	 * @see newClass()
	 * @param  string $name
	 * @param  int    $id
	 * @return \Entity
	 */
	protected function getEntity($name, $id = null) {
		return newClass($name."_Entity", $this->Db, $id);
	}
	
	/**
	 * Get model of given name
	 * @see newClass()
	 * @param  string
	 * @return \Model
	 */
	protected function getModel($name) {
		$cname = ucwords($name)."_Model";
		return newClass($cname, $this->Config, $this->Db, $this->Io, $this->Cache, $this->Variable, $this->User);
	}

	/**
	 * Get form of given name
	 * @see newClass()
	 * @param  string $name
	 * @param  array  $vars
	 * @return \Form
	 */
	protected function getForm($name, $vars = []) {
		return newClass($name."_Form", $this->Db, $this->Io, $this->User, $vars);
	}

	/**
	 * Send e-mail message of given name
	 * @see    \Mail_Core
	 * @see    \MailMessage_Core
	 * @param  string $name
	 * @param  string $to
	 * @param  array  $vars
	 * @return bool
	 */
	protected function sendMail($name, $to, $vars = []) {
		$Mail = newClass($name."_Mail", $this->Db);
		if (!$Mail)
			throw new Exception("Can't find email message ".$name);
		return $Mail->send($to, $vars);
	}
	
};