<?php
class Entity {

	protected $type = "default";
	protected $fields = [];
	protected $entities = [];
	protected $schema;
	protected $Db;

	public function __construct(&$Db, $id = null) {
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
			if ($this->schema["fields"][$field]["type"] == "int" || $this->schema["fields"][$field]["type"] == "uint")
				$value = (int) $value;
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
			if ($this->schema["fields"][$field]["type"] == "uint")
				$value = abs((int) $value);
			else if ($this->schema["fields"][$field]["type"] == "int")
				$value = (int) $value;
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
		foreach ($this->schema["fields"] as $key => $field) {
			if (array_key_exists($key, $this->fields))
				$data[$key] = $this->get($key);
		}
		if ($this->id()) {
			return $this->Db->update($this->schema["table"], $data, ["id" => $this->id()]);
		}
		else {
			foreach ($this->schema["fields"] as $key => $field) {
				if (!array_key_exists($key, $data) && array_key_exists("default", $field))
					$data[$key] = $field["default"];
			}
			return $this->fields["id"] = $this->Db->insert($this->schema["table"], $data);
		}
	}

	public function delete() {
		if (!$this->id())
			return false;
		return $this->Db->delete($this->schema["table"], ["id" => $this->id()]);
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

};