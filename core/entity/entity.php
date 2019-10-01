<?php
/**
 * Contains the base entity class
 */
/**
 * Base entity
 * @author Eric Höglander
 */
class Entity {
  
  /**
   * If true, try to generate a new url-alias
   * @see getCreateAlias
   * @var bool
   */
  public $update_alias = false;
  
  /**
   * Type of entity, is used to set a default schema
   * @var string
   */
  protected $type = "default";
  
  /**
   * The raw field data
   * @var array
   */
  protected $fields = [];
  
  /**
   * The database schema
   * @var array
   */
  protected $schema;
  
  /**
   * Any stored entities
   * @see getStoredEntity
   */
  protected $stored_entities = [];
  
  /**
   * Set to false on load or save
   * @var bool
   */
  protected $is_new = true;
  
  /**
   * Database object
   * @var \Db_Core
   */
  protected $Db;
  
  /**
   * Io object
   * @var \Io_Core
   */
  protected $Io;

  
  /**
   * Constructor
   * @param  \Db_Core $Db
   * @param  int      $id Id of entity to load
   */
  public function __construct($Db, $id = null) {
    $this->Db = $Db;
    $this->schema = $this->schema();
    if ($id)
      $this->load($id);
  }
  
  /**
   * Clone
   */
  public function __clone() {
    $this->is_new = true;
    $this->set("created", null);
    $this->set("updated", null);
    unset($this->fields["id"]);
    $this->stored_entities = [];
  }
  
  /**
   * Get url path to the entity
   * @return string
   */
  public function getPath() {
    return $this->schema["table"]."/view/".$this->id();
  }
  
  /**
   * Get url to the entity
   * @param  bool   $abs If true, returns absolute path
   * @return string
   */
  public function url($abs = false) {
    $url = url($this->getPath());
    if ($abs)
      $url = SITE_URL.$url;
    return $url;
  }

  /**
   * Entity as json-encodable data
   * @param  bool  $include_id
   * @return array
   */
  public function json($include_id = true) {
    $json = [];
    if ($include_id)
      $json["id"] = $this->id();
    foreach ($this->schema["fields"] as $key => $field) 
      $json[$key] = $this->get($key);
    return $json;
  }

  /**
   * The entity id
   * @return int
   */
  public function id() {
    if (empty($this->fields["id"]))
      return null;
    return (int) $this->fields["id"];
  }
  
  /**
   * The entity name derived from class name
   * @return string
   */
  public function entityName() {
    $name = get_class($this);
    return str_replace(["_Entity", "_Core"], "", $name);
  }
  
  /**
   * The table name 
   * @return string
   */
  public function tableName() {
    return $this->schema["table"];
  }
  
  /**
   * Check if entity has a field of given name
   * @param  string $field
   * @return bool
   */
  public function hasField($name) {
    return array_key_exists($name, $this->schema["fields"]);
  }
  
  /**
   * HTML-safe value
   * @see get
   * @see xss()
   * @param  string $field
   * @param  mixed $def
   * @return mixed
   */
  public function safe($field, $def = null) {
    return xss($this->get($field, $def));
  }
  
  /**
   * Get an entity from storage if it exists, otherwise fetch it
   * @param  string  $name
   * @param  int     $id
   * @param  bool    $create_new If true, an Entity is always returned
   * @param  bool    $reload
   * @return \Entity
   */
  public function getStoredEntity($name, $id, $create_new = false, $reload = false) {
    if (!array_key_exists($name, $this->stored_entities))
      $this->stored_entities[$name] = [];
    if (!array_key_exists($id, $this->stored_entities[$name]) || $reload) {
      $Entity = $this->getEntity($name, $id);
      if (!$Entity)
        throw new Exception("Unknown entity: ".$name);
      if (!$Entity->id() && !$create_new)
        $Entity = null;
      $this->stored_entities[$name][$id] = $Entity;
    }
    return $this->stored_entities[$name][$id];
  }

  /**
   * Get field value if it exists
   *
   * Will return formatted value of the specified field
   * For instance if the field has type "int", it will always 
   * return an integer (or $def) regardless of the raw data
   * @param  string $field
   * @param  mixed  $def
   * @return mixed
   */
  public function get($field, $def = null) {
    if (!array_key_exists($field, $this->fields))
      return $def;
    $value = $this->fields[$field];
    if (array_key_exists($field, $this->schema["fields"]) && $value !== null) {
      if (in_array($this->schema["fields"][$field]["type"], ["int", "uint", "file"]))
        $value = (int) $value;
      else if (in_array($this->schema["fields"][$field]["type"], ["float", "ufloat"]))
        $value = (float) $value;
      else if (in_array($this->schema["fields"][$field]["type"], ["decimal", "udecimal"])) {
        if ($this->schema["fields"][$field]["type"] == "udecimal")
          $value = str_replace("-", "", $value);
        $len = -($this->schema["fields"][$field]["length"][0]+1);
        if ($value < 0)
          $len--;
        $value = substr(@number_format($value, $this->schema["fields"][$field]["length"][1], ".", ""), $len);
      }
      if (!empty($this->schema["fields"][$field]["serialize"]))
        $value = unserialize($value);
      if (!empty($this->schema["fields"][$field]["json"]))
        $value = json_decode($value);
    }
    if ($value === null && $def !== null)
      return $def;
    return $value;
  }

  /**
   * Set field value
   *
   * Value will be formatted/cast/sanitized for certain types
   * if specified in the schema
   * @param  string $field
   * @param  mixed  $value
   * @return mixed
   */
  public function set($field, $value) {
    if (is_string($value) && strlen($value) === 0)
      $value = null;
    if (array_key_exists($field, $this->schema["fields"]) && $value !== null) {
      if (!empty($this->schema["fields"][$field]["serialize"]))
        $value = serialize($value);
      if (!empty($this->schema["fields"][$field]["json"]))
        $value = json_encode($value);
      if (in_array($this->schema["fields"][$field]["type"], ["uint", "file"]))
        $value = abs((int) $value);
      else if ($this->schema["fields"][$field]["type"] == "int")
        $value = (int) $value;
      else if ($this->schema["fields"][$field]["type"] == "ufloat")
        $value = abs((float) $value);
      else if ($this->schema["fields"][$field]["type"] == "float")
        $value = (float) $value;
      else if (in_array($this->schema["fields"][$field]["type"], ["decimal", "udecimal"])) {
        if ($this->schema["fields"][$field]["type"] == "udecimal")
          $value = str_replace("-", "", $value);
        $len = -($this->schema["fields"][$field]["length"][0]+1);
        if ($value < 0)
          $len--;
        $value = substr(@number_format($value, $this->schema["fields"][$field]["length"][1], ".", ""), $len);
      }
      else if ($this->schema["fields"][$field]["type"] == "varchar" && isset($this->schema["fields"][$field]["length"]))
        $value = substr($value, 0, $this->schema["fields"][$field]["length"]);
      else if ($this->schema["fields"][$field]["type"] == "enum") {
        if (!in_array($value, $this->schema["fields"][$field]["values"]))
          return;
      }
    }
    $this->fields[$field] = $value;
    return $value;
  }

  /**
   * Attempt to load entity from database based on id and $schema
   * @see    loadRow
   * @param  int $id
   * @return bool
   */
  public function load($id) {
    $row = $this->Db->getRow("SELECT * FROM `".$this->schema["table"]."` WHERE id = :id", [":id" => $id]);
    return $this->loadRow($row);
  }

  /**
   * Attempt to load entity from a fetched row
   * @see    load
   * @param  object $row
   * @return bool
   */
  public function loadRow($row) {
    if (!$row)
      return false;
    foreach ($row as $key => $value) {
      if ($key == "id" || array_key_exists($key, $this->schema["fields"]))
        $this->fields[$key] = $value;
    }
    $this->is_new = false;
    if (is_callable([$this, "onLoad"]))
      $this->onLoad();
    return true;
  }

  /**
   * Attempt to save entity to database based on $schema
   * @return bool
   */
  public function save() {
    $this->set("updated", REQUEST_TIME);
    if (!$this->id())
      $this->set("created", REQUEST_TIME);
    $data = [];
    $has_file = false;
    $new = $this->is_new;
    foreach ($this->schema["fields"] as $key => $field) {
      if (array_key_exists($key, $this->fields)) {
        $data[$key] = $this->fields[$key];
        if (!$has_file && $field["type"] == "file")
          $has_file = true;
      }
    }
    if (!$this->is_new) {
      if ($has_file) {
        $row = $this->Db->getRow("SELECT * FROM `".$this->schema["table"]."` WHERE id = :id", [":id" => $this->id()]);
        // Delete old files and set new files as permanent
        foreach ($this->schema["fields"] as $key => $field) {
          if ($field["type"] == "file") {
            if ($row->{$key} && $row->{$key} != $data[$key]) {
              $File = $this->getEntity("File", $row->{$key});
              $File->delete();
            }
            if ($data[$key]) {
              $File = $this->getEntity("File", $data[$key]);
              if (!$File->id()) {
                $data[$key] = $this->fields[$key] = null;
              }
              else if ($File->get("status") == 0) {
                $File->set("status", 1);
                $File->save();
              }
            }
          }
        }
      }
      if (!$this->Db->update($this->schema["table"], $data, ["id" => $this->id()]))
        return false;
    }
    else {
      foreach ($this->schema["fields"] as $key => $field) {
        if ((!array_key_exists($key, $data) || $data[$key] === null) && array_key_exists("default", $field))
          $data[$key] = $field["default"];
        // Set files as permanent
        if ($field["type"] == "file" && $data[$key]) {
          $File = $this->getEntity("File", $data[$key]);
          if (!$File->id()) {
            $data[$key] = $this->fields[$key] = null;
          }
          else if ($File->get("status") == 0) {
            $File->set("status", 1);
            $File->save();
          }
        }
      }
      if ($this->id())
        $data["id"] = $this->id();
      $id = $this->Db->insert($this->schema["table"], $data);
      if ($id)
        $this->set("id", $id);
      if (!$this->id())
        return false;
      $this->is_new = false;
    }
    if ($this->getCreateAlias($new))
      $this->createAlias();
    return true;
  }

  /**
   * Delete entity from database
   * @return bool
   */
  public function delete() {
    if (!$this->id())
      return false;
    foreach ($this->schema["fields"] as $key => $field) {
      if ($field["type"] == "file" && $this->get($key)) {
        $File = $this->getEntity("File", $this->get($key));
        $File->delete();
      }
    }
    if (!$this->Db->delete($this->schema["table"], ["id" => $this->id()]))
      return false;
    $this->deleteAlias();
    return true;
  }
  
  /**
   * Create an url-alias for the entity if possible
   * @see    getPath
   * @see    getAlias
   * @param  string $lang
   * @return bool
   */
  public function createAlias($lang = null) {
    if (!$this->id() || !is_callable([$this, "getPath"]) || !is_callable([$this, "getAlias"]))
      return false;
    if (!$this->Io)
      $this->Io = newClass("Io");
    $alias = $this->Io->filter($this->getAlias(), "alias");
    $path = $this->getPath();
    if (!$alias || !$path)
      return false;
    $query = [
      "from" => "alias",
      "where" => ["alias = :alias"],
      "vars" => [":alias" => $alias],
    ];
    if ($lang) {
      $query["where"][] = "(lang IS NULL || lang = :lang)";
      $query["vars"][":lang"] = $lang;
    }
    $row = $this->Db->getRow($query);
    // Find an available alias
    if ($row && $row->path != $path) {
      for ($i = 1; $row && $row->path != $path; $i++) {
        $query["vars"][":alias"] = $alias."-".$i;
        $row = $this->Db->getRow($query);
      }
      $alias = $query["vars"][":alias"];
    }
    // If it exists, just activate it
    if ($row) {
      if ($row->status == 0)
        $this->Db->update("alias", ["status" => 1], ["id" => $row->id]);
      return true;
    }
    else {
      $this->deleteAlias();
      $Alias = $this->getEntity("Alias");
      $Alias->set("path", $path);
      $Alias->set("alias", $alias);
      $Alias->set("lang", $lang);
      return $Alias->save();
    }
  }
  
  /**
   * Delete entity url-alias
   * @see    getPath
   * @see    getAlias
   * @param  string $lang
   * @return bool
   */
  public function deleteAlias($lang = null) {
    if (!$this->id() || !is_callable([$this, "getPath"]) || !is_callable([$this, "getAlias"]))
      return false;
    return $this->Db->delete("alias", ["path" => $this->getPath(), "lang" => $lang]);
  }


  /**
   * The database schema
   * @return array
   */
  protected function schema() {
    $schema = [
      "table" => "",
      "fields" => [],
    ];
    if ($this->type == "default") {
      $schema["fields"] = [
        "status" => [
          "type" => "uint",
          "default" => 1,
        ],
        "created" => [
          "type" => "uint",
          "default" => 0,
        ],
        "updated" => [
          "type" => "uint",
          "default" => 0,
        ],
      ];
    }
    return $schema;
  }

  /**
   * Get an entity
   * @param  string $name
   * @param  int    $id
   * @return \Entity
   */
  protected function getEntity($name, $id = null) {
    return newClass($name."_Entity", $this->Db, $id);
  }
  
  /**
   * Get multiple entities of given name
   * @see getEntity
   * @param  string $name
   * @param  array  $arr Array of integers or objects/arrays with id as property/key
   * @return array
   */
  protected function getEntities($name, $arr = []) {
    if (empty($arr))
      return [];
    $items = [];
    foreach ($arr as $row) {
      if (is_numeric($row))
        $id = $row;
      else if (is_object($row) && property_exists($row, "id"))
        $id = $row->id;
      else if (is_array($row) && array_key_exists("id", $row))
        $id = $row["id"];
      else
        continue;
      $items[] = $this->getEntity($name, $id);
    }
    return $items;
  }

  /**
   * Whether or not to generate an url-alias for the entity
   * @param  bool $new
   * @return bool
   */
  protected function getCreateAlias($new) {
    return $new || $this->update_alias;
  }

};