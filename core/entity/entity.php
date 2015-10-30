<?php
class Entity {
	
	public $update_alias = false;
	
	protected $type = "default";
	protected $fields = [];
	protected $entities = [];
	protected $schema;
	protected $Db, $Io;

	public function __construct($Db, $id = null) {
		$this->Db = $Db;
		$this->schema = $this->schema();
		if ($id)
			$this->load($id);
	}

	public function json() {
		$json = [
			"id" => $this->id(),
		];
		foreach ($this->schema["fields"] as $key => $field) 
			$json[$key] = $this->get($key);
		return $json;
	}

	public function id() {
		if (empty($this->fields["id"]))
			return null;
		return (int) $this->fields["id"];
	}

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
	
	public function deleteAlias() {
		if (!$this->id() || !is_callable([$this, "getPath"]) || !is_callable([$this, "getAlias"]))
			return false;
		return $this->Db->delete("alias", ["path" => $this->getPath()]);
	}


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

	protected function getEntity($name, $id = null) {
		return newClass($name."_Entity", $this->Db, $id);
	}

	protected function getCreateAlias($new) {
		return $new || $this->update_alias;
	}
	
	protected function getPath() {
		return $this->schema["table"]."/view/".$this->id();
	}

};