<?php
/**
 * Contains builder class
 *
 * @author Eric Höglander
 */
 
/**
 * Builder class
 */
class Builder {
  
  /**
   * Base path for files created by schema
   * @return string
   */
  public static function basePath() {
    return DOC_ROOT."/extend/";
  }
  
  /**
   * Get path for specified schema
   * @param  string $name
   * @return string
   */
  public static function schemaPath($name) {
    return DOC_ROOT."/core/builder/schema/".$name.".php";
  }
  
  /**
   * Check if specified schema exists
   * @param  string $name
   * @return bool
   */
  public static function schemaExists($name) {
    return file_exists(self::schemaPath($name));
  }
  
  /**
   * Load schema file
   * @param string $name
   */
  public static function schemaLoad($name) {
    require_once(self::schemaPath($name));
  }
  
  /**
   * Get the class name of specified schema
   * @param  string $name
   * @return string
   */
  public static function schemaClass($name) {
    return self::snakeToCamel($name);
  }
  
  /**
   * List of all available schemas
   * @return array
   */
  public static function schemaList() {
    $path = DOC_ROOT."/core/builder/schema/";
    $files = glob($path."*.php");
    foreach ($files as $key => $file)
      $files[$key] = substr($file, strlen($path), -4);
    return $files;
  }
  
  /**
   * Check if files exists
   * @param  array $files
   * @return bool
   */
  public static function filesExists($files) {
    foreach ($files as $key => $file) {
      $path = self::basePath().$file["path"];
      if (file_exists($path))
        return $key;
    }
    return false;
  }
  
  /**
   * Create files from associative array
   * @param  array $files
   */
  public static function createFiles($files) {
    foreach ($files as $file) {
      $path = self::basePath().$file["path"];
      if (file_exists($path)) {
        print $file["path"]." already exists. Overwrite? ";
        $in = strtolower(trim(fgets(STDIN)));
        if ($in != "y" && $in != "yes")
          continue;
      }
      self::mkdir($path);
      print "Writing to file ".$file["path"]."... ";
      if (!@file_put_contents($path, $file["content"]))
        die("Failed\n");
      print "OK\n";
    }
  }
  
  /**
   * Create directory for specified file if needed
   * @param string $path
   */
  public static function mkdir($path) {
    $info = pathinfo($path);
    if (!file_exists($info["dirname"]))
      mkdir($info["dirname"], 0775, true);
  }
  
  /**
   * Helper function to convert CamelCase to snake_case
   * @param  string
   * @return string
   */
  public static function camelToSnake($str) {
    return strtolower(preg_replace("/([a-z])([A-Z])/", "$1_$2", $str));
  }
  
  /**
   * Helper function to convert snake_case to CamelCase
   * @param  string
   * @return string
   */
  public static function snakeToCamel($str) {
    return str_replace(" ", "", ucwords(str_replace("_", " ", $str)));
  }
  
}