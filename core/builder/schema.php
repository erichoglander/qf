<?php
/**
 * Contains schema class
 * Serves as a base class for schemas
 *
 * @author Eric Höglander
 */
 
/**
 * Schema class
 */
class Schema {
  
  /**
   * The next input needed to create the schema
   * Each item consisting of:
   *   prompt, the text to display if input is needed
   *   default (optional), the default value if no value is given
   * @param  array $args
   * @return mixed
   */
  public static function input($args) {
    return null;
  }
  
  /**
   * Fetches the files of the schema
   * Each file consisting of:
   *   path, relative to extend folder
   *   content
   * @param  array $args Input gathered based on input()
   * @return array
   */
  public static function files($args) {
    return [];
  }
  
  /**
   * Any modifications to existing files
   * @param  array $args Input gathered based on input()
   */
  public static function mods($args) {
    
  }
  
  /**
   * Fast way to get an input array for arg()
   * @param  string $key
   * @param  string $prompt
   * @param  string $default
   * @param  string $type
   * @return array
   */
  public static function arg($key, $prompt, $default = null, $type = null) {
    $arr = [
      "key" => $key,
      "prompt" => $prompt,
    ];
    if ($default !== null)
      $arr["default"] = $default;
    if ($type !== null)
      $arr["type"] = $type;
    return $arr;
  }
  
}