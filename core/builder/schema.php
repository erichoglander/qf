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
  
  /**
   * Get localized string from LANG
   * Uses english as fallback,
   * @param  string $token
   * @param  array  $replacements
   * @return string
   */
  public static function l10n($token, $replacements = []) {
    $strings = static::l10n_strings();
    if (!empty($strings[$token])) {
      $arr = $strings[$token];
      if (array_key_exists(LANG, $arr))
        $str = $arr[LANG];
      else if (array_key_exists("en", $arr))
        $str = $arr["en"];
      else
        return null;
      foreach ($replacements as $key => $val)
        $str = str_replace($key, $val, $str);
      return $str;
    }
    return null;
  }
  
  /**
   * Localized strings
   * Structure: [
   *   "my_token" => [
   *     "en" => "My string",
   *     "sv" => "Min text",
   *   ],
   * ]
   * @return array
   */
  public static function l10n_strings() {
    return [];
  }
  
}