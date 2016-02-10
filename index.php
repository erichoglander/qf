<?php
/**
 * Index file
 * @author Eric Höglander
 */

require_once("core/inc/bootstrap.php");

if (IS_CLI)
  die("Cannot be run through cli\n");

$uri = substr($_SERVER["REQUEST_URI"], strlen(DOC_ROOT) - strlen($_SERVER["DOCUMENT_ROOT"]) + 1);
$doc = $ControllerFactory->executeUri($uri);

print $doc;