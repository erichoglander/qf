<?php
/**
 * Builder file
 * 
 * Create files and basic structures
 * Schemas are stored at core/builder/[schema].php
 * 
 * Usage: php builder.php [schema]
 * 
 * @author Eric Höglander
 */

require_once("core/inc/bootstrap.php");
require_once("core/builder/builder.php");
require_once("core/builder/schema.php");

function arg($prompt, $opt = []) {
  $opt+= [
    "type" => null,
    "options" => null,
  ];
  if ($opt["type"] == "bool")
    $opt["options"] = ["y", "n"];
  if (empty($GLOBALS["stdin_args"])) {
    print $prompt;
    if (!empty($opt["options"]))
      print " (".implode("/", $opt["options"]).")";
    if (array_key_exists("default", $opt))
      print " [".$opt["default"]."]";
    if ($opt["type"] == "bool")
      print "? ";
    else
      print ": ";
    $in = trim(fgets(STDIN));
  }
  else {
    $in = array_shift($GLOBALS["stdin_args"]);
    if ($in == "-")
      $in = "";
  }
  $re = false;
  if (!strlen($in)) {
    if (array_key_exists("default", $opt))
      return $opt["default"];
    $re = true;
  }
  if (!$re && !empty($opt["options"]) && !in_array($in, $opt["options"]))
    $re = true;
  if ($re)
    return arg($prompt, $opt);
  if (in_array($in, [":quit", ":q"]))
    die("Aborted\n");
  return $in;
}

$GLOBALS["stdin_args"] = array_slice($_SERVER["argv"], 1);

print "Builder script. Type :quit at any time to abort.\n";

if (!IS_CLI)
  die("Can only be run through CLI");
  
do {
  $name = arg("Enter schema");
  if ($name == "list")
    die("Available schemas: ".implode(", ", Builder::schemaList())."\n");
} while (!Builder::schemaExists($name));

Builder::schemaLoad($name);
$class = Builder::schemaClass($name);

$args = [];
while ($input = $class::input($args))
  $args[$input["key"]] = arg($input["prompt"], $input);

$files = $class::files($args);
if (Builder::filesExists($files)) {
  $in = arg("One or more files already exists. Overwrite?", ["options" => ["y", "n", "ask"]]);
  if ($in == "n")
    die("Aborted\n");
}

Builder::createFiles($files, $in == "y");
$class::mods($args);

print "Completed\n";