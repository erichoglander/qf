<?php
require_once("core/inc/bootstrap.php");

if (IS_CLI)
	die("Cannot be run through cli\n");

$request_uri = $_SERVER['REQUEST_URI'];

$ControllerFactory = new ControllerFactory($Db);
$doc = $ControllerFactory->executeUri($request_uri);

print $doc.PHP_EOL;