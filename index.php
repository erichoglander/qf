<?php
require_once("core/inc/bootstrap.php");

if (IS_CLI)
	die("Cannot be run through cli\n");

$doc = $ControllerFactory->executeUri($_SERVER['REQUEST_URI']);

print $doc;