<?php
define("DOC_ROOT", str_replace("/core/inc", "", __DIR__));

require_once(DOC_ROOT."/core/inc/constants.php");
require_once(DOC_ROOT."/core/inc/functions.php");

if (!IS_CLI)
	die("Updater must be run through CLI\n");

$Config = newClass("Config");
$Db = newClass("Db");
$dbc = $Config->getDatabase();
$Db->connect($dbc["user"], $dbc["pass"], $dbc["db"], $dbc["host"]);

$Updater = newClass("Updater", $Db);
$updates = $Updater->getUpdates();
$n = count($updates);
if (!$n)
	die("No updates required.\n");
print $n." updates needed. Proceeding.\n";
foreach ($updates as $update) {
	print "Running update ".$update."\n";
	if (!$Updater->runUpdate($update))
		print "Update failed. Shutting down\n";
}
print "Updates completed.\n";
