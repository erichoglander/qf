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
    return url($this->get("target"));
  }
  
  /**
   * Uri of the target
   * @see uri()
   * @param  string $lang
   * @return string
   */
  public function uri($lang = null) {
    return uri($this->get("target"), $lang);
  }
  
  /**
   * If target is an external web apge
   * @return bool
   */
  public function isExternal() {
    return strpos($this->get("target"), "http") === 0;
  }
  
  /**
   * Load redirect by source uri
   * @param  string $source
   * @return bool
   */
  public function loadBySource($source, $lang = null) {
    $row = $this->Db->getRow("
        SELECT * FROM `redirect`
        WHERE 
          status = 1 &&
          source = :source &&
          (lang IS NULL || lang = :lang)",
        [ ":source" => $source,
          ":lang" => $lang]);
    if ($row) {
      $this->load($row->id);
      return true;
    }
    $row = $this->Db->getRow("
        SELECT * FROM `redirect`
        WHERE 
          status = 1 &&
          source = :source &&
          (lang IS NULL || lang = :lang)",
        [ ":source" => urldecode($source),
          ":lang" => $lang]);
    if ($row) {
      $this->load($row->id);
      return true;
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
    return $schema;
  }

};