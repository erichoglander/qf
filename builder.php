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
 
define("DOC_ROOT", __DIR__);

include "core/inc/constants.php";
include "core/builder/builder.php";
include "core/builder/schema.php";

function arg($prompt, $default = null) {
  if (empty($GLOBALS["stdin_args"])) {
    print $prompt.($default ? " [".$default."]" : "").": ";
    $in = trim(fgets(STDIN));
    if (!strlen($in))
      return arg($prompt, $default);
    if ($in == "quit" || $in == "exit")
      die("Aborted\n");
    return $in;
  }
  return array_shift($GLOBALS["stdin_args"]);
}

$GLOBALS["stdin_args"] = array_slice($_SERVER["argv"], 1);

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
foreach ($class::input() as $key => $input)
  $args[$key] = arg($input["prompt"], (!empty($input["default"]) ? $input["default"] : null));

$files = $class::files($args);
if (Builder::filesExists($files)) {
  print "One or more files already exists. Continue? ";
  $in = strtolower(trim(fgets(STDIN)));
  if ($in != "y" && $in != "yes")
    die("Aborted\n");
}

Builder::createFiles($files);
$class::mods($args);

print "Completed\n";