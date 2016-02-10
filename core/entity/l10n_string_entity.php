<?php
/**
 * Contains l10n string entity
 */
/**
 * l10n string entity
 *
 * Used for single string translations
 * @author Eric Höglander
 */
class l10nString_Entity_Core extends l10n_Entity {

  /**
   * Load entity from string and language code
   * @param  string $string
   * @param  string $lang Language code
   * @return bool
   */
  public function loadFromString($string, $lang) {
    $row = $this->Db->getRow("
        SELECT id FROM `l10n_string`
        WHERE 
          lang = :lang && 
          string = :string",
        [  ":lang" => $lang,
          ":string" => $string]);
    if ($row)
      return $this->load($row->id);
    else
      return false;
  }

  
  /**
   * Database schema
   * @return array
   */
  protected function schema() {
    $schema = parent::schema();
    $schema["table"] = "l10n_string";
    $schema["fields"]["string"] = [
      "type" => "varchar",
    ];
    $schema["fields"]["input_type"] = [
      "type" => "enum",
      "values" => ["import", "manual", "code"],
      "default" => "code",
    ];
    return $schema;
  }

}