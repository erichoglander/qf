<?php
define("DOC_ROOT", str_replace("/core/inc", "", __DIR__));

require_once(DOC_ROOT."/core/inc/constants.php");
require_once(DOC_ROOT."/core/inc/functions.php");
require_once(DOC_ROOT."/core/controller/controller.php");
require_once(DOC_ROOT."/core/model/model.php");

if (file_exists(DOC_ROOT."/extend/inc/functions.php"))
	require_once(DOC_ROOT."/extend/inc/functions.php");

$Config = new Config();
$dbc = $Config->getDatabase();

$Db = new Db();
if (!$Db->connect($dbc['user'], $dbc['pass'], $dbc['db'], $dbc['host'])) {
	$Controller = new Error_Controller($Db);
	die($Controller->databaseFail().PHP_EOL);
}