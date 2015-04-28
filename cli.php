<?php
require_once("core/inc/bootstrap.php");

if (!IS_CLI)
	die("Must be run through cli");

if ($_SERVER['argc'] < 2)
	die("No path specified - for start page enter 'index'\n");

$request_uri = $_SERVER['argv'][1];

$ControllerFactory = new ControllerFactory($Db);
$doc = $ControllerFactory->executeUri($request_uri);

print $doc.PHP_EOL;