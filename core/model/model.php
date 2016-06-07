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
   * Access control list
   * @var \Acl_Core
   */
  protected $Acl;
  
  
  /**
   * Constructor
   * @param \Config_Core      $Config
   * @param \Db_Core          $Db
   * @param \Io_Core          $Io
   * @param \Cache_Core       $Cache
   * @param \Variable_Core    $Variable
   * @param \User_Entity_Core $User
   * @param \Acl_Core         $Acl
   */
  public function __construct($Config, $Db, $Io, $Cache, $Variable, $User, $Acl) {
    $args = func_get_args();
    if (count($args) < 7)
      throw new Exception("Not enough parameters for Model class ".get_class($this));
    array_splice($args, 0, 7);
    $this->Config = $Config;
    $this->Db = $Db;
    $this->Io = $Io;
    $this->Cache = $Cache;
    $this->Variable = $Variable;
    $this->User = $User;
    $this->Acl = $Acl;
    if (is_callable([$this, "construct"])) 
      call_user_func_array([$this, "construct"], $args);
  }

  /**
   * Get form of given name
   * @see newClass()
   * @param  string $name
   * @param  array  $vars
   * @return \Form
   */
  public function getForm($name, $vars = []) {
    $cname = ucwords($name)."_Form";
    return $this->newClass($cname, $vars);
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
   * Get multiple entities of given name
   * @see getEntity
   * @param  string $name
   * @param  array  $arr Array of integers or objects/arrays with id as property/key
   * @return array
   */
  protected function getEntities($name, $arr = []) {
    if (empty($arr))
      return [];
    $items = [];
    foreach ($arr as $row) {
      if (is_numeric($row))
        $id = $row;
      else if (is_object($row) && property_exists($row, "id"))
        $id = $row->id;
      else if (is_array($row) && array_key_exists("id", $row))
        $id = $row["id"];
      else
        continue;
      $items[] = $this->getEntity($name, $id);
    }
    return $items;
  }
  
  /**
   * Get model of given name
   * @see newClass()
   * @param  string
   * @return \Model
   */
  protected function getModel($name) {
    $cname = ucwords($name)."_Model";
    return $this->newClass($cname, $name);
  }

  /**
   * Send e-mail message of given name
   * @see    newClass()
   * @see    \Mail_Core
   * @see    \MailMessage_Core
   * @param  string $name
   * @param  string $to
   * @param  array  $vars
   * @return bool
   */
  protected function sendMail($name, $to, $vars = []) {
    $Mail = $this->newClass($name."_Mail");
    if (!$Mail)
      throw new Exception("Can't find email message ".$name);
    return $Mail->send($to, $vars);
  }
  
  /**
   * Get model extended object of given name
   * @param  string $name
   * @return \Model
   */
  protected function newClass($name) {
    $args = func_get_args();
    array_splice($args, 0, 1);
    $params = [
      $name,
      $this->Config, 
      $this->Db,
      $this->Io,
      $this->Cache,
      $this->Variable,
      $this->User,
      $this->Acl,
    ];
    $params = array_merge($params, $args);
    return call_user_func_array("newClass", $params);
  }
  
};