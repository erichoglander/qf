<?php
/**
 * Contains the base controller class
 */
/**
 * Base controller
 *
 * The controller is to server as the leader for a request
 * After the controller factory parses the request and contructs
 * the proper controller object, this is where the request comes.
 * The controller should do any additional parsing of the request,
 * then deligate the heavy lifting to the model, and finally,
 * send that data to the view for rendering and output.
 * The model may be skipped if no processing needs to be done.
 * The view may be skipped if the data doesn't need rendering.
 *
 * @author Eric Höglander
 */
class Controller {

  /**
   * Data passed on to the view
   * @var array
   */
  protected $viewData = [];
  
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
   * Matching model
   * @var \Model
   */
  protected $Model;
  
  /**
   * User entity
   * @var \User_Entity_Core
   */
  protected $User;
  
  /**
   * Io object
   * @var \Io_Core
   */
  protected $Io;
  
  /**
   * Acl object
   * @var \Acl_Core
   */
  protected $Acl;
  
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
   * Constructor
   * @param \Config_Core $Config
   * @param \Db_Core     $Db
   * @param bool         $init
   */
  public function __construct($Config, $Db, $init = true) {
    $this->Config = $Config;
    $this->Db = $Db;
    $this->Io = newClass("Io");
    $this->name = $this->getName();
    if ($this->Db->connected) {
      $this->Acl = newClass("Acl", $this->Db);
      $this->Cache = newClass("Cache", $this->Db);
      $this->Variable = newClass("Variable", $this->Db);
      $this->User = $this->getUser();
      $this->Model = $this->getModel($this->name, true);
      if ($init) {
        $this->automaticCron();
        $this->defaultViewData();
        $this->loadLibraries();
        if (!IS_CLI)
          $this->loadMenus();
      }
    }
  }

  /**
   * Try to execute the given action
   * @param  string $action
   * @param  array  $args
   * @return string
   */
  public function action($action, $args = []) {
    if (!$this->Db->connected)
      return $this->databaseFail();
    if (!$this->Acl->access($this->User, $this->acl($action, $args), $args))
      return $this->accessDenied();
    $action.= "Action";
    if (!method_exists($this, $action)) 
      return $this->notFound();
    return $this->$action($args);
  }
  
  /**
   * Execute an uri
   * Typically used when wanting to reuse an action from a different controller
   * Note that the request constants are already defined and cannot be redefined
   * @param  string $uri
   * @return string
   */
  public function executeUri($uri) {
    $CF = newClass("ControllerFactory", $this->Config, $this->Db);
    $request = $CF->parseUri($uri);
    return $CF->executeControllerAction($request["controller"], $request["action"], $request["args"]);
  }
  
  /**
   * Get the access control list for an action of the current controller
   * @param  string $action
   * @param  array  $args
   * @return mixed  Array, string, or null
   */
  public function acl($action, $args = []) {
    return null;
  }

  /**
   * 500 Internal error
   * @return string
   */
  public function internalError() {
    if (IS_CLI)
      return "500 Error";
    setHeaderFromCode(500);
    return $this->viewBare("500");
  }
  
  /**
   * Database connection error
   * @return string
   */
  public function databaseFail() {
    if (IS_CLI)
      return "Database connection failed";
    if ($this->Config->getDebug())
      die(pr($this->Db->getErrors(), 1));
    return $this->serverBusy();
  }
  
  /**
   * 503 Server busy error 
   * @return string
   */
  public function serverBusy() {
    if (IS_CLI)
      return "503 Service unavailable";
    setHeaderFromCode(503);
    return $this->viewBare("503");
  }
  
  /**
   * 404 Not found error
   * @return string
   */
  public function notFound() {
    if (IS_CLI)
      return "404 Not found";
    setHeaderFromCode(404);
    return $this->viewDefault("404");
  }
  
  /**
   * 403 Access denied error
   *
   * Redirects user to login page if not logged in
   * @return string
   */
  public function accessDenied() {
    if (IS_CLI)
      return "403 Access denied";
    if (!$this->User->id()) 
      redirect("user/login?redir=".REQUEST_ALIAS);
    setHeaderFromCode(403);
    header("HTTP/1.1 403 Forbidden");
    return $this->viewDefault("403");
  }
  
  /**
   * Set a default generic error message
   */
  public function defaultError() {
    setmsg(t("An error occurred"), "error");
  }
  
  /**
   * Redirects current page to it's alias
   * @param bool $all_languages If true, checks aliases for other languages if none exists for current language
   * @param int  $code Http code for the redirect
   */
  public function redirectToAlias($all_languages = true, $code = 301) {
    if (IS_FRONT_PAGE || REQUEST_PATH != REQUEST_ALIAS)
      return;
    $alias = $this->Db->getRow("
        SELECT * FROM `alias` 
        WHERE 
          status = 1 &&
          path = :path &&
          (lang IS NULL || lang = :lang)", 
        [ ":path" => REQUEST_PATH,
          ":lang" => LANG]);
    if ($alias) {
      setHeaderFromCode($code);
      redirect($alias->alias, false);
    }
    
    // Try for an alias in a different language
    if ($all_languages) {
      $alias = $this->Db->getRow("
          SELECT * FROM `alias` 
          WHERE 
            status = 1 &&
            path = :path", 
          [ ":path" => REQUEST_PATH]);
      if ($alias) {
        setHeaderFromCode($code);
        header("Location: ".langBaseUrl($alias->lang).$alias->alias);
      }
    }
  }

  
  /**
   * Check if current user has access to given uri
   * @param  string $uri
   * @return bool
   */
  protected function uriAccess($uri) {
    $uri = preg_replace("/\#.*$/", "", $uri);
    $CF = newClass("ControllerFactory", $this->Config, $this->Db);
    $request = $CF->parseUri($uri);
    $Controller = $CF->getController($request["controller"], false);
    $acl = $Controller->acl($request["action"], $request["args"]);
    return $this->Acl->access($this->User, $acl, $request["args"]);
  }

  /**
   * Get name of current controller (lower case)
   * @return string
   */
  protected function getName() {
    $class = get_class($this);
    if ($class == "Controller")
      return "default";
    return strtolower(preg_replace("/([a-z])([A-Z])/", "$1_$2", str_replace("_Controller", "", str_replace("_Core", "", $class))));
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

  /**
   * Get model of given name
   * @see    newClass()
   * @param  string $name
   * @param  bool   $fallback
   * @return \Model
   */
  protected function getModel($name, $fallback = false) {
    $arr = explode("_", $name);
    $cname = "";
    foreach ($arr as $a)
      $cname.= ucwords($a);
    $cname.= "_Model";
    $Model = $this->newClass($cname);
    if (!$Model && $fallback)
      $Model = $this->newClass("Model");
    return $Model;
  }

  /**
   * Get current user
   */
  protected function getUser() {
    $User = $this->getEntity("User");
    if (!empty($_SESSION["user_id"]))
      $User->load($_SESSION["user_id"]);
    return $User;
  }

  /**
   * Get entity of given name
   * @see    newClass()
   * @param  string $name
   * @param  int    $id
   * @return \Entity
   */
  protected function getEntity($name, $id = null) {
    return newClass($name."_Entity", $this->Db, $id);
  }

  /**
   * Get form of given name
   * @see    \Model::getForm()
   * @param  string $name
   * @param  array  $vars
   * @return \Form
   */
  protected function getForm($name, $vars = []) {
    return $this->Model->getForm($name, $vars);
  }

  /**
   * Runs automatic cron if enabled and is due
   * @see \Config_Core::getAutomaticCron()
   * @see \Cron_Model_Core::run()
   */
  protected function automaticCron() {
    if (!IS_CLI && $this->Config->getAutomaticCron()) {
      $last = $this->Variable->get("cron", 0);
      if (date("Y-m-d", $last) != date("Y-m-d", REQUEST_TIME)) {
        $Cron = $this->getModel("Cron");
        $time = microtime(true);
        $Cron->run();
        $time = round(microtime(true) - $time, 4);
        $this->Variable->set("cron", REQUEST_TIME);
        addlog("cron", "Cron completed in ".$time." seconds");
      }
    }
  }

  /**
   * Load enabled libraries
   * @see \Config_Core::getLibraries()
   * @see \Library
   */
  protected function loadLibraries() {
    foreach ($this->Config->getLibraries() as $lib) {
      $Library = newClass($lib."_Library", $this->Db);
      if (!$Library)
        continue;
      foreach ($Library->getIncludes() as $incl) {
        $uri = "library/".classToDir($lib)."/".$incl;
        $path = filePath($uri);
        if ($path)
          require_once($path);
      }
      $this->viewData["html"]["css"] = array_merge($this->viewData["html"]["css"], $Library->getCss());
      $this->viewData["html"]["js"] = array_merge($this->viewData["html"]["js"], $Library->getJs());
    }
  }

  /**
   * Load menus
   * @see \Config_Core::getMenus()
   */
  protected function loadMenus() {
    foreach ($this->Config->getMenus() as $key => $menu) {
      if (!empty($menu["acl"]) && !$this->Acl->access($this->User, $menu["acl"]))
        continue;
      $this->viewData["html"]["menu"][$key] = $this->menuAccess($menu);
      if (!empty($menu["body_class"]))
        $this->viewData["html"]["body_class"][] = cssClass($menu["body_class"]);
    }
  }

  /**
   * Filter menu based on current user access
   * @see uriAccess
   * @param  array $menu
   * @return array
   */
  protected function menuAccess($menu) {
    if (!empty($menu["always_show"]))
      return $menu;
    if (array_key_exists("href", $menu)) {
      if (!$this->uriAccess($menu["href"]))
        return [];
    }
    if (!empty($menu["links"])) {
      foreach ($menu["links"] as $key => $link) {
        $re = $this->menuAccess($link);
        if (!$re)
          unset($menu["links"][$key]);
        else
          $menu["links"][$key] = $re;
      }
    }
    if (!array_key_exists("href", $menu) && empty($menu["links"]))
      return [];
    return $menu;
  }

  /**
   * Get default viewData structure
   * @see    viewData
   * @return array
   */
  protected function defaultViewData() {
    $this->viewData["html"] = [
      "menu" => [],
      "body_class" => [],
      "css" => [],
      "js" => [],
    ];
  }
  
  /**
   * Get view object based on the use
   * @param  string $name
   * @param  string $type
   * @return \View_Core
   */
  protected function getView($name, $type = null) {
    if ($type == "default")
      $View = $this->newClass("View", "default", $name, $this->viewData);
    else if ($type == "bare")
      $View = newClass("View", null, null, null, null, null, null, null, "default", $name, $this->viewData);
    else
      $View = $this->newClass("View", $this->name, $name, $this->viewData);
    return $View;
  }

  /**
   * Get rendered view
   * @see    \View_Core
   * @param  string $name
   * @param  string $type
   * @return string
   */
  protected function view($name, $type = null) {
    if ($type)
      ob_clean();
    $View = $this->getView($name, $type);
    try {
      return $View->render();
    }
    catch (Exception $e) {
      if ($type == "default")
        die($e->getMessage());
      if ($this->Config->getDebug())
        $this->viewData["console"] = $e->getMessage();
      return $this->internalError();
    }
  }
  
  /**
   * Get rendered default view
   * @see    \View_Core
   * @param  string $name
   * @return string
   */
  protected function viewDefault($name) {
    return $this->view($name, "default");
  }
  
  /**
   * Get rendered view without database access
   * @see    \View_Core
   * @param  string $name
   * @return string
   */
  protected function viewBare($name) {
    return $this->view($name, "bare");
  }

  /**
   * Get content of rendered view
   * @see    \View_Core
   * @param  string $name
   * @param  string $type
   * @return string
   */
  protected function viewContent($name, $type = null) {
    $View = $this->getView($name, $type);
    try {
      return $View->renderContent();
    }
    catch (Exception $e) {
      if ($this->Config->getDebug())
        $this->viewData["console"] = $e->getMessage();
      return $this->internalError();
    }
  }

  /**
   * Get JSON-encoded view
   * @see    \View_Core
   * @param  string $name
   * @param  string $type
   * @return string
   */
  protected function viewJson($name, $type = null) {
    $View = $this->getView($name, $type);
    try {
      return json_encode([
        "content" => jth($View->renderContent($name)),
        "title" => $View->Html->getTitle(),
      ]);
    }
    catch (Exception $e) {
      return $this->jsone(t("Error"));
    }
  }

  /**
   * Get viewData as json
   * @return string
   */
  protected function json() {
    unset($this->viewData["html"]);
    $json = json_encode($this->viewData);
    if ($json === false)
      throw new Exception("Json encoding error: ".json_last_error_msg());
    return $json;
  }
  
  /**
   * Quick way of returning an error message in json
   * @param  string $msg
   * @param  string $code
   * @return string
   */
  protected function jsone($msg, $code = null) {
    return json_encode(["status" => "error", "error" => $msg, "error_code" => $code]);
  }

};