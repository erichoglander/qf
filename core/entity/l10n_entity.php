<?php
class l10n_Entity extends Entity {

	public $default_lang = LANG;
	public $translations;


	public function __construct(&$Db, $id = null, $lang = null) {
		parent::__construct($Db, null);
		if ($id)
			$this->load($id, $lang);
	}

	public function json() {
		$json = parent::json();
		if (!empty($this->translations)) {
			$json->translations = [];
			foreach ($this->translations as $lang => $Entity) {
				$json->translations[$lang] = $Entity->json();
			}
		}
		return $json;
	}

	public function saveAll() {
		if (!$this->save())
			return false;
		if (!empty($this->translations)) {
			foreach ($this->translations as $Entity) {
				$Entity->set("sid", $this->id());
				if (!$Entity->save())
					return false;
			}
		}
		return true;
	}

	public function loadAll() {
		$this->translations = $this->getTranslations();
	}

	public function load($id, $lang = null) {
		if ($lang)
			$this->loadTranslation($id, $lang);
		else
			return parent::load($id);
	}

	public function deleteAll() {
		if (!$this->delete())
			return false;
		if (!empty($this->translations)) {
			foreach ($this->translations as $Entity) {
				if (!$Entity->delete())
					return false;
			}
		}
		return true;
	}

	public function delete() {
		if ($this->get("sid") === 0) {
			$row = $this->Db->getRow("
					SELECT * FROM `".$this->schema["table"]."`
					WHERE sid = :id
					ORDER BY id ASC",
					[	":id" => $this->id()]);
			if ($row)  {
				$this->Db->update($this->schema["table"], ["sid" => $row->id], ["sid" => $this->id()]);
				$this->Db->update($this->schema["table"], ["sid" => 0], ["id" => $row->id]);
			}
		}
		return parent::delete();
	}

	public function loadTranslation($id, $lang) {
		$row = $this->Db->getRow(
				"SELECT id FROM `".$this->schema["table"]."`
				WHERE
					(id = :id && sid = 0 || sid = :id) &&
					lang = :lang",
				[":id" => $id, ":lang" => $lang]);
		if ($row)
			return parent::load($id);
		else
			return false;
	}

	public function getTranslations() {
		if (!$this->id())
			return null;
		$sid = $this->get("sid");
		$list = [];
		if ($sid) {
			$rows = $this->Db->getRows(
					"SELECT id, lang FROM `".$this->schema["table"]."` 
					WHERE 
						(sid = :sid || id = :sid)", 
					[	":sid" => $sid]);
		}
		else {
			$rows = $this->Db->getRows(
					"SELECT id, lang FROM `".$this->schema["table"]."` 
					WHERE 
						sid = :sid", 
					[	":sid" => $this->id()]);
		}
		if (!empty($rows)) {
			foreach ($rows as $row) {
				$class = get_class($this);
				$list[$row->lang] = new $class($this->Db, $row->id);
			}
		}
		return $list;
	}

	public function getTranslation($lang) {
		if (!$this->id())
			return null;
		$sid = $this->get("sid");
		if ($sid) {
			$row = $this->Db->getRow(
					"SELECT id FROM `".$this->schema["table"]."` WHERE 
					(sid = :sid || id = :sid) && lang = :lang", 
					[":sid" => $sid, ":lang" => $lang]);
		}
		else {
			$row = $this->Db->getRow(
					"SELECT id FROM `".$this->schema["table"]."` WHERE 
					sid = :sid && lang = :lang", 
					[":sid" => $this->id(), ":lang" => $lang]);
		}
		if ($row) {
			$class = get_class($this);
			return new $class($this->Db, $row->id);
		}
		else {
			return null;
		}
	}


	protected function schema() {
		$schema = parent::schema();
		$schema["fields"]["sid"] = [
			"type" => "uint",
			"default" => 0,
		];
		$schema["fields"]["lang"] = [
			"type" => "varchar",
			"default" => $this->default_lang,
		];
		return $schema;
	}

};