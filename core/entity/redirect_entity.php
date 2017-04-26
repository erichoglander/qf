<?php
/**
 * Contains the redirect entity
 */
/**
 * Redirect entity
 *
 * Used to handle stored http redirects
 * @author Eric Höglander
 */
class Redirect_Entity_Core extends Entity  {
  
  /**
   * The loaded source
   * @see loadByRegExp
   * @var array
   */
  protected $loaded_source;
  
  /**
   * Language object
   * @var object
   */
  protected $language;
  
  
  /**
   * Get language object, if it exists
   * @return object
   */
  public function language() {
    if ($this->get("lang") && $this->language === null) {
      $this->language = $this->Db->getRow("
          SELECT * FROM `language`
          WHERE lang = :lang",
          [":lang" => $this->get("lang")]);
    }
    return $this->language;
  }
  
  /**
   * Url of the target
   * @see url()
   * @return string
   */
  public function url() {
    return url($this->target());
  }
  
  /**
   * Uri of the target
   * @see uri()
   * @param  string $lang
   * @return string
   */
  public function uri($lang = null) {
    return uri($this->target(), $lang);
  }
  
  /**
   * If target is an external web apge
   * @return bool
   */
  public function isExternal() {
    return strpos($this->target(), "http") === 0;
  }
  
  /**
   * Evaluated target
   * For type 'normal' this will just return the target field
   * For type 'regexp' this will return the evaluated regexp with matches
   */
  public function target() {
    if ($this->get("type") == "regexp") {
      if (!$this->loaded_source)
        throw new Exception("Cannot find redirect regexp target without a loaded source");
      return preg_replace("/".$this->get("source")."/i", $this->get("target"), $this->loaded_source);
    }
    return $this->get("target");
  }
  
  /**
   * Load redirect by normal source uri
   * @param  string $source
   * @return bool
   */
  public function loadBySource($source, $lang = null) {
    $tries = [$source, urldecode($source)];
    if (substr($source, -1) == "/")
      $tries[] = substr($source, 0, -1);
    else
      $tries[] = $source.= "/";
    foreach ($tries as $try) {
      $row = $this->Db->getRow("
          SELECT * FROM `redirect`
          WHERE 
            status = 1 &&
            type = 'normal' &&
            source = :source &&
            (lang IS NULL || lang = :lang)",
          [ ":source" => $try,
            ":lang" => $lang]);
      if ($row) {
        $this->loadRow($row);
        return true;
      }
    }
    return false;
  }
  
  /**
   * Load redirect by regexp source uri
   * @param  string $source
   * @return bool
   */
  public function loadByRegexp($source, $lang = null) {
    $tries = [$source, urldecode($source)];
    foreach ($tries as $try) {
      $row = $this->Db->getRow("
          SELECT * FROM `redirect`
          WHERE 
            status = 1 &&
            type = 'regexp' &&
            :source REGEXP source &&
            (lang IS NULL || lang = :lang)",
          [ ":source" => $try,
            ":lang" => $lang]);
      if ($row) {
        $this->loadRow($row);
        $this->loaded_source = $try;
        return true;
      }
    }
    return false;
  }
  
  
  /**
   * Database schema
   * @return array
   */
  protected function schema() {
    $schema = parent::schema();
    $schema["table"] = "redirect";
    $schema["fields"]["lang"] = [
      "type" => "varchar",
    ];
    $schema["fields"]["source"] = [
      "type" => "varchar",
    ];
    $schema["fields"]["target"] = [
      "type" => "varchar",
    ];
    $schema["fields"]["code"] = [
      "type" => "enum",
      "values" => ["301", "302", "303", "307"],
      "default" => "301",
    ];
    $schema["fields"]["type"] = [
      "type" => "enum",
      "values" => ["normal", "regexp"],
      "default" => "normal",
    ];
    return $schema;
  }

};