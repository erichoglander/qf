<?php
class Entity {

	protected $type = "default";
	protected $fields;
	protected $schema;
	protected $Db;

	public function __construct(&$Db, $id = null) {
		$this->Db = $Db;
		$this->schema = $this->schema();
		if ($id)
			$this->load($id);
	}

	public function id() {
		if (empty($this->fields['id']))
			return null;
		return (int) $this->fields['id'];
	}

	public function get($field) {
		if (!array_key_exists($field, $this->fields))
			return null;
		$value = $this->$fields[$field];
		if (!empty($this->schema[$field]['serialize']))
			$value = unserialize($value);
		if (!empty($this->schema[$field]['json']))
			$value = json_decode($value);
		return $value;
	}

	public function set($field, $value) {
		if (!array_key_exists($field, $this->schema))
			return null;
		if (!empty($this->schema[$field]['serialize']))
			$value = serialize($value);
		if (!empty($this->schema[$field]['json']))
			$value = json_encode($value);
		if ($this->schema[$field]['type'] == "uint")
			$value = abs((int) $value);
		else if ($this->schema[$field]['type'] == "int")
			$value = (int) $value;
		$this->fields[$field] = $value;
		return $value;
	}

	public function load($id) {
		$row = $this->Db->getRow("SELECT * FROM `".$this->schema['table']."` WHERE id = :id", [":id" => $id]);
		if (!$row)
			return false;
		foreach ($row as $key => $value) {
			if (array_key_exists($key, $this->schema))
				$this->fields[$key] = $value;
		}
		return true;
	}

	public function save() {
		$this->set("updated", REQUEST_TIME);
		if (!$this->id())
			$this->set("created", REQUEST_TIME);
		$data = [];
		foreach ($this->schema['fields'] as $key => $field) {
			if (array_key_exists($key, $this->fields))
				$data[$key] = $this->get($key);
		}
		if ($this->id()) {
			return $this->update($this->schema['table'], $data, ["id" => $this->id()]);
		}
		else {
			foreach ($this->schema['fields'] as $key => $field) {
				if (!array_key_exists($key, $data) && array_key_exists("default", $field))
					$data[$key] = $field['default'];
			}
			return $this->fields['id'] = $this->insert($this->schema['table'], $data);
		}
	}

	public function delete() {
		if (!$this->id())
			return false;
		return $this->delete($this->schema['table'], ["id" => $this->id()]);
	}


	protected function schema() {
		$schema = [
			"table" => "",
			"fields" => [],
		];
		if ($this->type == "default") {
			$schema['fields'] = [
				"status" => [
					"type" => "uint",
					"default" => 0,
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

};