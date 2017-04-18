<?php
/**
 * Bootstrap file
 *
 * Include the necessary files
 * Load config and connect to database
 * Initialize the controller factory
 * 
 * @author Eric Höglander
 */
session_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);

// Proxy support
if (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] == "https")
  $_SERVER["HTTPS"] = "on";
if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
  $_SERVER["REMOTE_ADDR"] = $_SERVER["HTTP_X_FORWARDED_FOR"];

// Set internal encoding to UTF-8
mb_internal_encoding("UTF-8");

/**
 * Document root
 *
 * Ex: /usr/share/nginx/mysite/web
 * @var string
 */
define("DOC_ROOT", str_replace("/core/inc", "", str_replace("\\", "/", __DIR__)));

require_once(DOC_ROOT."/core/inc/constants.php");
require_once(DOC_ROOT."/core/inc/functions.php");
require_once(filePath("inc/country_list.php"));
require_once(DOC_ROOT."/core/entity/entity.php");
require_once(DOC_ROOT."/core/entity/l10n_entity.php");
require_once(DOC_ROOT."/core/controller/controller.php");
require_once(DOC_ROOT."/core/model/model.php");
require_once(DOC_ROOT."/core/class/mail_message.php");

if (file_exists(DOC_ROOT."/extend/inc/bootstrap.php"))
  require_once(DOC_ROOT."/extend/inc/bootstrap.php");
if (file_exists(DOC_ROOT."/extend/inc/constants.php"))
  require_once(DOC_ROOT."/extend/inc/constants.php");
if (file_exists(DOC_ROOT."/extend/inc/functions.php"))
  require_once(DOC_ROOT."/extend/inc/functions.php");

$Config = newClass("Config");
$Db = newClass("Db");
$Db->debug = $Config->getDebug();
$dbc = $Config->getDatabase();
$Db->connect($dbc["user"], $dbc["pass"], $dbc["db"], $dbc["host"]);

$ControllerFactory = newClass("ControllerFactory", $Config, $Db);