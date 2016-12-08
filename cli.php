<?php
/**
 * CLI execution file
 *
 * Execute an uri through cli
 * Ex: php cli.php my-controller/my-action/arg1
 *
 * Flags:
 *  --raw
 *    Passes the $raw parameter to \ControllerFactory 
 *    so the request ignores aliases and redirects
 * 
 * @author Eric Höglander
 */

require_once("core/inc/bootstrap.php");

if (!IS_CLI)
  die("Must be run through cli");

$raw = in_array("--raw", $_SERVER["argv"]);
$request_uri = ($_SERVER["argc"] < 2 ? "page/index" : end($_SERVER["argv"]));
$doc = $ControllerFactory->executeUri($request_uri, $raw);

print $doc.PHP_EOL;