<?php
/**
 * Updater file
 *
 * Runs database updates and installs any new translations
 * Ex: php updater.php
 * 
 * @author Eric Höglander
 */

require_once("core/inc/bootstrap.php");

if (!IS_CLI)
	die("Must be run through cli");

if (!$Db->connected)
	die("Could not connect to database\n");

$request_uri = "updater/update";
$doc = $ControllerFactory->executeUri($request_uri);
print $doc.PHP_EOL;