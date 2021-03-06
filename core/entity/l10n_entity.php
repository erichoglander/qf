<?php
/**
 * Contains the l10n entity
 */
/**
 * l10n entity
 *
 * An extension of the base entity to be used as a new base
 * for translatable entities.
 * The 'source entity' is the original entity when there was no other translations
 */
class l10n_Entity extends Entity {

  /**
   * The default language of an entity
   * @var string
   */
  public $default_lang = "sv";


  /**
   * The entity translations
   * @var array
   */
  protected $translations = [];

  /**
   * Whether or not all translations have been fetched
   * @var bool
   */
  protected $translations_fetched = false;


  /**
   * Constructor
   * Sets default language from config
   * @param \Db_Core $Db
   * @param int      $id   Id of entity to load
   * @param string   $lang Language code of entity to load
   */
  public function __construct($Db, $id = null, $lang = null) {
    parent::__construct($Db, null);
    $Config = newClass("Config");
    $this->default_lang = $Config->getDefaultLanguage();
    if ($id)
      $this->load($id, $lang);
  }
  
  /**
   * Clone
   */
  public function __clone() {
    $this->set("sid", $this->sid());
    parent::__clone();
  }

  /**
   * Set default language for current entity
   * @param  string $lang
   */
  public function setLang($lang = null) {
    if (!$lang)
      $lang = $this->default_lang;
    $this->set("lang", $lang);
  }

  /**
   * Get url path to the translated entity. Returns source entity url if there is no translation.
   * @return string
   */
	public function translationUrl($lang = null, $abs = false) {
    if (!$lang)
      $lang = LANG;
    $path = ($this->translation($lang) ? $this->translation($lang)->getPath() : $this->getPath());
		$url = url($path, ["lang" => $lang]);
		if ($abs && strpos($url, "http") !== 0)
			$url = SITE_URL.$url;
		return $url;
	}

  /**
   * Entity as json-encodable data
   * @param  bool  $include_translations
   * @param  bool  $include_id
   * @return array
   */
  public function json($include_translations = true, $include_id = true) {
    $json = parent::json($include_id);
    if ($include_translations) {
      $json["translations"] = [];
      foreach ($this->translations() as $lang => $Entity)
        $json["translations"][$lang] = $Entity->json(false, $include_id);
    }
    return $json;
  }

  /**
   * Get a translation for a field
   * @see    \Entity::get()
   * @param  string $key
   * @param  string $lang Language code
   * @param  mixed  $def  Default value
   * @return mixed
   */
  public function translate($key, $lang = null, $def = null) {
    if (!$lang)
      $lang = LANG;
    if ($this->translation($lang))
      return $this->translation($lang)->get($key, $def);
    return $def;
  }

  /**
   * Get a translation for a field
   *
   * Returns current field value if translation is missing
   * @see    translate
   * @see    \Entity::get()
   * @param  string $key
   * @param  string $lang Language code
   * @param  mixed  $def  Default value
   * @return mixed
   */
  public function translateFallback($key, $lang = null, $def = null) {
    if (!$lang)
      $lang = LANG;
    if ($this->translation($lang))
      return $this->translation($lang)->get($key, $def);
    else
      return $this->get($key, $def);
  }

  /**
   * The id of the source entity
   * @return int
   */
  public function sid() {
    return $this->get("sid", $this->id());
  }

  /**
   * Save entity and all translations
   * @return bool
   */
  public function saveAll() {
    if (!$this->save())
      return false;
    foreach ($this->translations as $lang => $Entity) {
      $Entity->set("sid", $this->sid());
      $Entity->set("lang", $lang);
      if (!$Entity->save())
        return false;
    }
    return true;
  }

  /**
   * Save entity
   * @return bool
   */
  public function save() {
    if ($this->id()) {
      $original = $this->Db->getRow("
          SELECT * FROM `".$this->schema["table"]."`
          WHERE id = :id",
          [":id" => $this->id()]);
    }
    else {
      $original = null;
    }

    if (!parent::save())
      return false;

    // Sync field values if needed
    $data = [];
    $vars = [];
    foreach ($this->schema["fields"] as $key => $field) {
      if (!empty($field["sync"]) && (!$original || $original->{$key} != $this->fields[$key])) {
        $data[] = "`".$key."`=:".$key;
        $vars[":".$key] = $this->fields[$key];
      }
    }
    if (!empty($data)) {
      $sql = "
        UPDATE `".$this->schema["table"]."`
        SET ".implode(", ", $data)."
        WHERE
          (sid = :sid || id = :sid) &&
          id != :id";
      $vars[":sid"] = $this->sid();
      $vars[":id"] = $this->id();
      return $this->Db->query($sql, $vars);
    }
    return true;
  }

  /**
   * Load entity
   *
   * If $lang is specified, attempt to load translation of that language
   * @param  int    $id
   * @param  string $lang Language code
   * @return bool
   */
  public function load($id, $lang = null) {
    if ($lang)
      return $this->loadTranslation($id, $lang);
    else
      return parent::load($id);
  }

  /**
   * Delete entity and all translations
   * @return bool
   */
  public function deleteAll() {
    foreach ($this->translations() as $Entity) {
      if (!$Entity->delete(false))
        return false;
    }
    return $this->delete(false);
  }

  /**
   * Delete entity
   *
   * If specified and entity is the source entity, reassign source entity
   * and all source ids of the translation
   * @param  bool $change_sid Whether or not to reassign source entity if needed
   * @return bool
   */
  public function delete($change_sid = true) {
    if ($this->get("sid") === null && $change_sid) {
      $row = $this->Db->getRow("
          SELECT * FROM `".$this->schema["table"]."`
          WHERE sid = :id
          ORDER BY id ASC",
          [  ":id" => $this->id()]);
      if ($row)  {
        $this->Db->update($this->schema["table"], ["sid" => $row->id], ["sid" => $this->id()]);
        $this->Db->update($this->schema["table"], ["sid" => null], ["id" => $row->id]);
      }
    }
    return parent::delete();
  }

  /**
   * Create a new translation of entity
   * @param  $lang Language code
   * @return \l10n_Entity The new entity
   */
  public function newTranslation($lang) {
    $class = get_class($this);
    $this->translations[$lang] = new $class($this->Db);
    $this->translations[$lang]->set("sid", $this->sid());
    $this->translations[$lang]->set("lang", $lang);
    foreach ($this->schema["fields"] as $key => $field) {
      if (!empty($field["sync"]))
        $this->translations[$lang]->set($key, $this->get($key));
    }
    return $this->translations[$lang];
  }

  /**
   * Load entity of given language
   * @param  int    $id
   * @param  string $lang Language code
   * @return bool
   */
  public function loadTranslation($id, $lang) {
    $row = $this->Db->getRow(
        "SELECT id FROM `".$this->schema["table"]."`
        WHERE
          (id = :id && sid IS NULL || sid = :id) &&
          lang = :lang",
        [":id" => $id, ":lang" => $lang]);
    if ($row)
      return parent::load($id);
    else
      return false;
  }

  /**
   * Get all translations of entity
   * @return array
   */
  public function translations() {
    if (!$this->translations_fetched && $this->sid()) {
      $this->translations_fetched = true;
      $query = [
        "cols" => ["id", "lang"],
        "table" => $this->schema["table"],
        "where" => ["(sid = :sid || id = :sid)"],
        "vars" => [":sid" => $this->sid()],
      ];
      if ($this->id()) {
        $query["where"][] = "id != :id";
        $query["vars"][":id"] = $this->id();
      }
      $rows = $this->Db->getRows($query);
      $this->translations = [];
      foreach ($rows as $row) {
        $class = get_class($this);
        $this->translations[$row->lang] = new $class($this->Db, $row->id);
      }
    }
    return $this->translations;
  }

  /**
   * Get a specific translation of entity
   * @param  string $lang   Language code
   * @param  boolt  $create Create translation if it doesnt exist
   * @return \l10n_Entity
   */
  public function translation($lang, $create = false) {
    if ($this->get("lang") == $lang)
      return $this;
    if (!array_key_exists($lang, $this->translations) || (!$this->translations[$lang] && $create)) {
      $this->translations[$lang] = null;
      if ($this->sid()) {
        $query = [
          "cols" => ["id", "lang"],
          "table" => $this->schema["table"],
          "where" => [
            "(sid = :sid || id = :sid)",
            "lang = :lang",
          ],
          "vars" => [
            ":sid" => $this->sid(),
            ":lang" => $lang,
          ],
        ];
        if ($this->id()) {
          $query["where"][] = "id != :id";
          $query["vars"][":id"] = $this->id();
        };
        $row = $this->Db->getRow($query);
        if ($row) {
          $class = get_class($this);
          $this->translations[$lang] = new $class($this->Db, $row->id);
        }
        else if ($create) {
          $this->newTranslation($lang);
        }
      }
    }
    return $this->translations[$lang];
  }

  /**
   * Create an url-alias for the entity if possible
   * @see    getPath
   * @see    getAlias
   * @return bool
   */
  public function createAlias($lang = null) {
    return parent::createAlias($this->get("lang", $this->default_lang));
  }

  /**
   * Delete entity url-alias
   * @see    getPath
   * @see    getAlias
   * @return bool
   */
  public function deleteAlias($lang = null) {
    return parent::deleteAlias($this->get("lang"));
  }


  /**
   * Database schema
   * @return array
   */
  protected function schema() {
    $schema = parent::schema();
    $schema["fields"]["sid"] = [
      "type" => "uint",
    ];
    $schema["fields"]["lang"] = [
      "type" => "varchar",
      "default" => $this->default_lang,
    ];
    return $schema;
  }

};