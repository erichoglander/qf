<?php
/**
 * CLI execution file
 *
 * Execute an uri through cli
 * Ex: php cli.php my-controller/my-action/arg1
 * 
 * @author Eric Höglander
 */

require_once("core/inc/bootstrap.php");

if (!IS_CLI)
  die("Must be run through cli");

if (!$Db->connected)
  die("Could not connect to database\n");

$request_uri = ($_SERVER["argc"] < 2 ? "page/index" : $_SERVER["argv"][1]);
$doc = $ControllerFactory->executeUri($request_uri);

print $doc.PHP_EOL;