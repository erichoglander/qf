<?php
require_once("core/inc/bootstrap.php");

if (!IS_CLI)
	die("Must be run through cli");

$request_uri = ($_SERVER['argc'] < 2 ? "page/index" : $_SERVER['argv'][1]);
$doc = $ControllerFactory->executeUri($request_uri);

print $doc.PHP_EOL;