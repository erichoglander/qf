<?php
class i18n_Entity extends Entity {

	public $default_lang = "en";

	public function __construct(&$Db, $id = null, $lang = null) {
		parent::__construct($Db, null);
		if ($id)
			$this->load($id, $lang);
	}

	public function load($id, $lang = null) {
		if ($lang)
			$this->loadTranslation($id, $lang);
		else
			parent::load($id);
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
						(sid = :sid || id = :sid) && lang = :lang", 
					[":sid" => $sid]);
		}
		else {
			$rows = $this->Db->getRows(
					"SELECT id, lang FROM `".$this->schema["table"]."` 
					WHERE 
						sid = :sid && lang = :lang", 
					[":sid" => $this->id()]);
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