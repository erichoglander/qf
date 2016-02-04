<?php
/**
 * Updater file
 *
 * Runs database updates and installs any new translations
 * Ex: php updater.php
 * Skips confirmation step if run with flag --confirm
 * 
 * @author Eric Höglander
 */

require_once("core/inc/bootstrap.php");

if (!IS_CLI)
	die("Must be run through cli");

if (!$Db->connected)
	die("Could not connect to database\n");

$request_uri = "updater/update";
if (in_array("--confirm", $_SERVER["argv"]))
	$request_uri.= "/1";
$doc = $ControllerFactory->executeUri($request_uri, true);
print $doc.PHP_EOL;