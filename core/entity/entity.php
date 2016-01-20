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
	 * Get url path to the entity
	 * @return string
	 */
	public function getPath() {
		return $this->schema["table"]."/view/".$this->id();
	}

	/**
	 * Entity as json-encodable data
	 * @return array
	 */
	public function json() {
		$json = [
			"id" => $this->id(),
		];
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
			if ($this->schema["fields"][$field]["type"] == "int" || 
					$this->schema["fields"][$field]["type"] == "uint" ||
					$this->schema["fields"][$field]["type"] == "file")
				$value = (int) $value;
			else if ($this->schema["fields"][$field]["type"] == "float")
				$value = (float) $value;
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
			if ($this->schema["fields"][$field]["type"] == "uint" ||
					$this->schema["fields"][$field]["type"] == "file")
				$value = abs((int) $value);
			else if ($this->schema["fields"][$field]["type"] == "int")
				$value = (int) $value;
			else if ($this->schema["fields"][$field]["type"] == "float")
				$value = (float) $value;
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
	 * @param  int $id
	 * @return bool
	 */
	public function load($id) {
		$row = $this->Db->getRow("SELECT * FROM `".$this->schema["table"]."` WHERE id = :id", [":id" => $id]);
		if (!$row)
			return false;
		foreach ($row as $key => $value) {
			if ($key == "id" || array_key_exists($key, $this->schema["fields"]))
				$this->fields[$key] = $value;
		}
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
		foreach ($this->schema["fields"] as $key => $field) {
			if (array_key_exists($key, $this->fields)) {
				$data[$key] = $this->fields[$key];
				if (!$has_file && $field["type"] == "file")
					$has_file = true;
			}
		}
		if ($this->id()) {
			$new = false;
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
							if ($File->get("status") == 0) {
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
			$new = true;
			foreach ($this->schema["fields"] as $key => $field) {
				if ((!array_key_exists($key, $data) || $data[$key] === null) && array_key_exists("default", $field))
					$data[$key] = $field["default"];
			}
			$this->fields["id"] = $this->Db->insert($this->schema["table"], $data);
			if (!$this->id())
				return false;
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
	 * @return bool
	 */
	public function createAlias() {
		if (!$this->id() || !is_callable([$this, "getPath"]) || !is_callable([$this, "getAlias"]))
			return false;
		if (!$this->Io)
			$this->Io = newClass("Io");
		$alias = $this->Io->filter($this->getAlias(), "alias");
		$path = $this->getPath();
		if (!$alias || !$path)
			return false;
		$row = $this->Db->getRow("
				SELECT * FROM `alias`
				WHERE alias = :alias",
				[":alias" => $alias]);
		// Find an available alias
		if ($row && $row->path != $path) {
			for ($i = 1; $row && $row->path != $path; $i++) {
				$a = $alias."-".$i;
				$row = $this->Db->getRow("
					SELECT * FROM `alias`
					WHERE alias = :alias",
					[":alias" => $a]);
			}
			$alias = $a;
		}
		if ($row) {
			if ($row->status == 0)
				$this->Db->update("alias", ["status" => 1], ["id" => $row->id]);
			return true;
		}
		else {
			$this->Db->delete("alias", ["path" => $path]);
			$Alias = $this->getEntity("Alias");
			$Alias->set("path", $path);
			$Alias->set("alias", $alias);
			return $Alias->save();
		}
	}
	
	/**
	 * Delete entity url-alias
	 * @see    getPath
	 * @see    getAlias
	 * @return bool
	 */
	public function deleteAlias() {
		if (!$this->id() || !is_callable([$this, "getPath"]) || !is_callable([$this, "getAlias"]))
			return false;
		return $this->Db->delete("alias", ["path" => $this->getPath()]);
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
	 * Whether or not to generate an url-alias for the entity
	 * @param  bool $new
	 * @return bool
	 */
	protected function getCreateAlias($new) {
		return $new || $this->update_alias;
	}

};