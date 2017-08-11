<?php
/**
 * A number of default functions
 *
 * Includes both necessary functions and helper functions
 * 
 * @author Eric Höglander
 */

/**
 * Autoloader for classses
 *
 * Includes the necessary file based on class name
 * 
 * @param  string $class
 */
function classAutoload($class) {
  $fname = classToFile($class);
  $suffixes = ["controller", "model", "entity", "form_item", "form", "mail"];
  $dirs = ["theme", "library"];
  foreach ($suffixes as $suffix) {
    $csuffix = "";
    foreach (explode("_", $suffix) as $sfx)
      $csuffix.= ucwords($sfx);
    if (preg_match("/_".$csuffix."(_|$)/", $class)) {
      $cpath = DOC_ROOT."/core/".$suffix."/".$fname;
      $epath = DOC_ROOT."/extend/".$suffix."/".$fname;
      if (file_exists($cpath))
        require_once($cpath);
      if (file_exists($epath))
        require_once($epath);
      return;
    }
  }
  foreach ($dirs as $dir) {
    $csuffix = "_";
    foreach (explode("_", $dir) as $sfx)
      $csuffix.= ucwords($sfx);
    if (strpos($class, $csuffix) !== false) {
      $fdir = str_replace(".php", "", $fname);
      $cpath = DOC_ROOT."/core/".$dir."/".$fdir."/".$dir.".php";
      $epath = DOC_ROOT."/extend/".$dir."/".$fdir."/".$dir.".php";
      if (file_exists($cpath))
        require_once($cpath);
      if (file_exists($epath)) 
        require_once($epath);
      return;
    }
  }
  $cpath = DOC_ROOT."/core/class/".$fname;
  $epath = DOC_ROOT."/extend/class/".$fname;
  if (file_exists($cpath))
    require_once($cpath);
  if (file_exists($epath))
    require_once($epath);
}
spl_autoload_register("classAutoload");

/**
 * Creates a class object based on $cname
 *
 * If no extended class is found, the object
 * is created from the core class
 * Ex: newClass("Io") might create class from Io or Io_Core
 * 
 * @param  string $cname The class name
 * @return object        A class object
 */
function newClass($cname) {
  if (!class_exists($cname)) {
    $cname.= "_Core";
    if (!class_exists($cname))
      return null;
  }
  $args = func_get_args();
  array_shift($args); // Remove the class name from argument list
  if (empty($args)) {
    return new $cname();
  }
  else {
    $r = new ReflectionClass($cname);
    return $r->newInstanceArgs($args);
  }
}

/**
 * Gets the correct filename from a class name
 * @param  string $class Ex: SomeName_Model_Core
 * @return string        Ex: some_name_model.php
 */
function classToFile($class) {
  return str_replace(["_core", "_theme", "_library"], "", classToDir($class).".php");
}
/**
 * Transforms a class name to something more fitting a filesystem
 * @see pascalToSnake()
 * @param  string $class Ex: SomeName_Model
 * @return string        Ex: some_name_model
 */
function classToDir($class) {
  return pascalToSnake($class);
}

/**
 * Converts string from snake_case to PascalCase
 * @param  string $str
 * @param  string $delimiter
 * @return string
 */
function snakeToPascal($str, $delimiter = "_") {
  $arr = explode($delimiter, $str);
  $new_str = "";
  foreach ($arr as $i => $word)
    $new_str.= ucwords($word);
  return $new_str;
}

/**
 * Converts string from PascalCase or camelCase to snake_case
 * @param  string $str
 * @param  string $delimiter
 * @return string
 */
function pascalToSnake($str, $delimiter = "_") {
  return strtolower(preg_replace("/([a-z])([A-Z])/", "$1".$delimiter."$2", $str));
}


/**
 * Formats a number of bytes into a readable format
 * @param  int $bytes Ex: 2048
 * @return string     Ex: 2kB
 */
function formatBytes($bytes) {
  if (!$bytes) 
    return "0B";
  $units = Array("B", "kB", "MB", "GB", "TB");
  $pow = floor(log($bytes)/log(1024));
  $bytes/= pow(1024, $pow);
  return round($bytes, 2).$units[$pow];
}

/**
 * Turns a readable number into an integer
 *
 * Used primarily to store currency without getting rounding errors
 * 
 * @param  string $value Ex: 3,75
 * @return int           Ex: 375
 */
function decimalInt($value) {
  $value = str_replace(" ", "", $value);
  $value = str_replace(",", ".", $value);
  $x = strpos($value, ".");
  if ($x === false)
    return $value*100;
  else {
    $int = substr($value, 0, $x);
    $dec = substr($value, $x+1, 2);
    if (strlen($dec) == 1)
      $dec.= "0";
    return (int) $int.$dec;
  }
}

/**
 * Turns an integer inte a readable format
 *
 * Used primarily for formatting stored currency
 * 
 * @param  int     $value    Ex: 375
 * @param  int     $p        Number of decimals
 * @param  string  $dec      Decimal point
 * @param  string  $thousand Thousand separator
 * @return string            Ex: 3,75
 */
function decimalFloat($value, $p = 2, $dec = ",", $thousand = " ") {
  return number_format($value/100, $p, $dec, $thousand);
}

/**
 * Get json data from POST body
 * @param  boolean $assoc If true, returns an assoc array, otherwise an object
 * @return array|object
 */
function getjson($assoc = false) {
  $post = @file_get_contents("php://input");
  if (!$post)
    return null;
  $post = trim($post);
  if (strpos($post, "{") === 0 || strpos($post, "[") === 0)
    return @json_decode($post, $assoc);
  return null;
}

/**
 * print_r for usage in HTML
 * @param  mixed  $data
 * @param  int    $ret  If true, returns the output, otherwise prints it
 * @return string
 */
function pr($data, $ret = 0) {
  $html = "<pre>".print_r($data,1)."</pre>";
  if ($ret)
    return $html;
  else
    print $html;
}

/**
 * Encode certain characters to avoid Cross Site Scripting
 * @param  string $str
 * @return string
 */
function xss($str) {
  return htmlspecialchars($str, ENT_QUOTES);
}

/**
 * Shorten a string in between words
 * @param  string $str
 * @param  int    $len
 * @return string
 */
function shorten($str, $len) {
  if (strlen($str) > $len) {
    $str = trim(substr($str, 0, $len));
    $x = strrpos($str, " ", -3);
    if ($x === false)
      $x = $len-3;
    $str = substr($str, 0, $x)."...";
  }
  return $str;
}

/**
 * Formats a string to suitable css class
 * @param  string $str Ex: Some weird_String
 * @return string      Ex: some-weird-string
 */
function cssClass($str) {
  $str = strtolower($str);
  $str = preg_replace("/[\ \_]/", "-", $str);
  $str = preg_replace("/[\-]+/", "-", $str);
  $str = preg_replace("/[^a-z0-9\-]/", "", $str);
  return $str;
}

/**
 * Create a random hexstring that is hopefully unique
 * @return string 12 characters long
 */
function guid() {
  return substr(md5(microtime().REQUEST_TIME.rand(1, 1000)), 0, 12);
}

/**
 * Get absolute file path
 *
 * Returns file in extended directory if it exists
 * otherwise return file from core directory
 *
 * @see fileUrl()
 * @param  string $path Ex: library/mylib/somefile.php
 * @return string       Ex: /usr/share/nginx/myweb/web/extend/library/mylib/somefile.php
 */
function filePath($path) {
  $epath = DOC_ROOT."/extend/".$path;
  $cpath = DOC_ROOT."/core/".$path;
  if (file_exists($epath))
    return $epath;
  if (file_exists($cpath))
    return $cpath;
  return null;
}

/**
 * Get a file url
 *
 * Returns file in extended directory if it exists
 * otherwise return from core directory
 * 
 * @see filePath()
 * @param  string $path Ex: library/mylib/somefile.png
 * @return string       Ex: /extend/library/mylib/somefile.png
 */
function fileUrl($path) {
  $epath = "extend/".$path;
  $cpath = "core/".$path;
  if (file_exists(DOC_ROOT."/".$epath))
    return BASE_PATH.$epath;
  if (file_exists(DOC_ROOT."/".$cpath))
    return BASE_PATH.$cpath;
  return null;
}

/**
 * Render a template from path
 * @param  string $include_path
 * @param  array  $vars
 * @return string
 */
function renderTemplate($include_path, $vars = null) {
  if (!empty($vars))
    extract($vars);
  ob_start();
  include $include_path;
  return ob_get_clean();
}

/**
 * Shortcut for rendering template in template/ directory
 * @param  string $name
 * @param  array  $vars
 * @return string
 */
function tpl($name, $vars = null) {
  $path = filePath("template/".$name.".php");
  if (!$path)
    return null;
  return renderTemplate($path, $vars);
}

/**
 * Translates a string
 * @param  string $str  String to be translated
 * @param  string $lang The language of the string
 * @param  array  $vars Replacements for the string
 * @return str
 */
function t($str, $lang = "en", $vars = []) {
  static $cache = [];
  if (isset($cache[$lang][$str][LANG])) {
    $str = $cache[$lang][$str][LANG];
  }
  else {
    global $Db;
    $l10nString = newClass("l10nString_Entity", $Db);
    if (!$l10nString->loadFromString($str, $lang)) {
      $l10nString->set("lang", $lang);
      $l10nString->set("string", $str);
      $l10nString->set("input_type", "code");
      $l10nString->save();
    }
    else {
      $Translation = $l10nString->translation(LANG);
      if ($Translation) {
        $cache[$lang][$str][LANG] = $Translation->get("string");
        $str = $Translation->get("string");
      }
    }
  }
  if (!empty($vars))
    $str = str_replace(array_keys($vars), array_values($vars), $str);
  return $str;
}

/**
 * Fetches alias uri for a path
 * @see uri
 * @param  string $path Ex: news/view/18
 * @param  string $lang
 * @return string       Ex: blog/my-blog-post
 */
function uri($path, $lang = null) {
  global $Db;
  $x = strpos($path, "?");
  if ($x) {
    $q = substr($path, $x);
    $path = substr($path, 0, $x);
  }
  else {
    $q = null;
  }
  if ($path == "<front>") {
    $path = "";
  }
  else {
    if (!$lang)
      $lang = LANG;
    $row = $Db->getRow("
        SELECT * FROM `alias`
        WHERE 
          path = :path && 
          status = 1 &&
          (lang IS NULL || lang = :lang)",
        [ ":path" => $path,
          ":lang" => $lang]);
    if ($row)
      $path = $row->alias;
  }
  return $path.$q;
}

/**
 * Returns the formatted url for a path
 * @see uri()
 * @see redirect()
 * @param  string  $path    Ex: news/view/18
 * @param  mixed   $options
 * @return string           Ex: /en/blog/my-blog-post
 */
function url($path, $options = []) {
  if (!strlen($path))
    return BASE_URL;
  // Just return absolute urls
  if (preg_match("/^[a-z]+\:\/\//", $path))
    return $path;
  if ($options && !is_array($options))
    $options = ["redir" => true];
  // Remove leading slashes
  $path = preg_replace("/^[\/]+/", "", $path);
  if (!empty($options["lang"]))
    $url = langBaseUrl($options["lang"]).uri($path, $options["lang"]);
  else 
    $url = BASE_URL.uri($path);
  if (!empty($options["redir"])) {
    $uri = REQUEST_PATH;
    if (QUERY_STRING)
      $uri.= "?".QUERY_STRING;
    $sep = (strpos($url, "?") === false ? "?" : "&");
    $url.= $sep."redir=".urlencode($uri);
  }
  return $url;
}

/**
 * Returns the localized url if it exists, otherwise it returns the base language url
 * @param  string $path
 * @param  string $lang
 * @return string
 */
function l10n_url($path, $lang) {
  global $Config, $Db;
  if ($lang == LANG)
    return url($path);
  // TODO: Smart way to find l10n url, maybe through l10n_Entity
  $path = null;
  return langBaseUrl($lang).uri($path);
}

/**
 * Returns the base language url
 * @param  string $lang
 * @return string
 */
function langBaseUrl($lang) {
  global $Config;
  if ($Config->getLanguageDetection() == "path") {
    return BASE_PATH.$lang."/";
  }
  else if ($Config->getLanguageDetection() == "domain") {
    $domains = $Config->getDomains();
    foreach ($domains as $l => $domain) {
      if ($lang == $l) {
        $base = HTTP_PROTOCOL."://".$domain;
        break;
      }
    }
    if (!isset($base))
      $base = HTTP_PROTOCOL."://".$domains["default"];
    return $base."/";
  }
  return SITE_URL;
}

/**
 * Check if current user has access to given uri
 * @param  string $uri
 * @param  bool   $raw
 * @return bool
 */
function uriAccess($uri, $raw = false) {
  global $Config, $Db;
  $Acl = newClass("Acl", $Db);
  $CF = newClass("ControllerFactory", $Config, $Db);
  $User = newClass("User_Entity", $Db);
  if (!empty($_SESSION["user_id"]))
    $User->load($_SESSION["user_id"]);
  $request = $CF->parseUri($uri, $raw);
  $Controller = $CF->getController($request["controller"], false);
  $acl = $Controller->acl($request["action"], $request["args"]);
  return $Acl->access($User, $acl, $request["args"]);
}

/**
 * Sets a system message
 * @param string $msg
 * @param string $type
 */
function setmsg($msg, $type = "info") {
  if (!isset($_SESSION["sysmsg"]))
    $_SESSION["sysmsg"] = [];
  $_SESSION["sysmsg"][] = [
    "type" => $type,
    "message" => $msg,
  ];
}

/**
 * Fetches all stored system messages
 * @return array
 */
function getmsgs() {
  return (isset($_SESSION["sysmsg"]) ? $_SESSION["sysmsg"] : null);
}

/**
 * Clears all stored system messages
 */
function clearmsgs() {
  unset($_SESSION["sysmsg"]);
}

/**
 * Add a log entry
 *
 * Ex: addlog("myclass", "Something bad happened", ["foo" => "bar"], "error");
 * 
 * @param  string $category
 * @param  string $text
 * @param  mixed  $data Any serializable data
 * @param  string $type Can be anything, but common ones are: info, warning, error, debug
 */
function addlog($category, $text, $data = null, $type = "info") {
  global $Db, $Config;
  $obj = [
    "user_id" => (!empty($_SESSION["user_id"]) ? $_SESSION["user_id"] : null),
    "ip" => (!empty($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : null),
    "created" => REQUEST_TIME,
    "type" => $type,
    "category" => $category,
    "text" => $text,
  ];
  try {
    $obj["data"] = serialize($data);
  }
  catch (Exception $e) {
    $string = print_r($data, 1);
    $obj["data"] = serialize($string);
  }
  $Db->insert("log", $obj);
  $Db->query("DELETE FROM `log` ORDER BY id ASC LIMIT ".$Config->getMaxLogs().", 1");
}

/**
 * Attempt to set HTTP header based on code
 * @param int $code
 */
function setHeaderFromCode($code) {
  if ($code == 301)
    header("HTTP/1.1 301 Moved Permanently");
  else if ($code == 302)
    header("HTTP/1.1 302 Moved");
  else if ($code == 303)
    header("HTTP/1.1 302 See Other");
  else if ($code == 307)
    header("HTTP/1.1 302 Temporary Redirect");
  else if ($code == 403)
    header("HTTP/1.1 403 Forbidden");
  else if ($code == 404)
    header("HTTP/1.1 404 Not found");
  else if ($code == 500)
    header("HTTP/1.1 500 Internal error");
  else if ($code == 503)
    header("HTTP/1.1 503 Service unavailable");
}

/**
 * Redirects the visitor to given url
 * @see url()
 * @param  string  $url
 * @param  boolean $redir If true, it will redirect to the redir param (if set)
 */
function redirect($url = "", $redir = true) {
  if ($redir && array_key_exists("redir", $_GET))
    $url = urldecode($_GET["redir"]);
  $pcl = strpos($url, "://");
  if ($pcl === false || $pcl > 8)
    $url = url($url);
  if (IS_CLI)
    print "Redirect: ".$url."\n";
  else
    header("Location: ".$url);
  exit;
}

/**
 * Redirects the visitor to the current page
 */
function refresh() {
  $uri = REQUEST_PATH;
  if (QUERY_STRING)
    $uri.= "?".QUERY_STRING;
  redirect($uri);
}

/**
 * Simple http request with cURL
 * @param  string $url
 * @param  bool   $binary
 * @return string
 */
function httpRequest($url, $binary = false) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  if ($binary)
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
  $re = curl_exec($ch);
  curl_close($ch);
  return $re;
}

/**
 * Prompt a file download
 * @param string $path Path to file
 */
function promptFile($path) {
  header("Content-Type: application/octet-stream");
  readfile($path);
  exit;
}

/**
 * Shortcut for JsonToHtml\htmlToJson
 * @see JsonToHtml\htmlToJson
 * @param  string $html
 * @return array
 */
function jth($html) {
  if (function_exists("JsonToHtml\htmlToJson"))
    return JsonToHtml\htmlToJson($html);
  return null;
}

/**
 * Displays an icon with a link if user has access to the uri
 * @see FontAwesome\Icon
 * @see uriAccess()
 * @param  string $uri
 * @param  string $icon
 * @return string
 */
function iconBtn($uri, $icon = "pencil") {
  if (!uriAccess($uri))
    return null;
  return '<a class="icon-btn" href="'.url($uri, true).'">'.FontAwesome\Icon($icon).'</a>';
}