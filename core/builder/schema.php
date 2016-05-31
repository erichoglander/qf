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
   * The input needed to create the schema
   * Each item consisting of:
   *   prompt, the text to display if input is needed
   *   default (optional), the default value if no value is given
   * @return array
   */
  public static function input() {
    return [];
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
  
}