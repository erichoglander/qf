<?php
class l10n_Entity extends Entity {

	public $default_lang = "sv";
	
	protected $translations = [];
	protected $translations_fetched = false;


	public function __construct($Db, $id = null, $lang = null) {
		parent::__construct($Db, null);
		$Config = newClass("Config");
		$this->default_lang = $Config->getDefaultLanguage();
		if ($id)
			$this->load($id, $lang);
	}

	public function json() {
		$json = parent::json();
		if (!empty($this->translations())) {
			$json->translations = [];
			foreach ($this->translations() as $lang => $Entity) {
				$json->translations[$lang] = $Entity->json();
			}
		}
		return $json;
	}
	
	public function translate($key, $lang = null, $def = null) {
		if (!$lang)
			$lang = LANG;
		if ($this->translation($lang))
			return $this->translation($lang)->get($key, $def);
		return null;
	}
	
	public function translateFallback($key, $lang = null, $def = null) {
		if (!$lang)
			$lang = LANG;
		if ($this->translation($lang))
			return $this->translation($lang)->get($key, $def);
		else
			return $this->get($key, $def);
	} 
	
	public function sid() {
		return $this->get("sid", $this->id());
	}

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

	public function load($id, $lang = null) {
		if ($lang)
			$this->loadTranslation($id, $lang);
		else
			return parent::load($id);
	}

	public function deleteAll() {
		foreach ($this->translations() as $Entity) {
			if (!$Entity->delete(false))
				return false;
		}
		return $this->delete(false);
	}

	public function delete($change_sid = true) {
		if ($this->get("sid") === null && $change_sid) {
			$row = $this->Db->getRow("
					SELECT * FROM `".$this->schema["table"]."`
					WHERE sid = :id
					ORDER BY id ASC",
					[	":id" => $this->id()]);
			if ($row)  {
				$this->Db->update($this->schema["table"], ["sid" => $row->id], ["sid" => $this->id()]);
				$this->Db->update($this->schema["table"], ["sid" => null], ["id" => $row->id]);
			}
		}
		return parent::delete();
	}
	
	public function newTranslation($lang) {
		$class = get_class($this);
		$this->translations[$lang] = new $class($this->Db);
		$this->translations[$lang]->set("sid", $this->sid());
		$this->translations[$lang]->set("lang", $lang);
	}

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

	public function translations() {
		if (!$this->translations_fetched && $this->id()) {
			$this->translations_fetched = true;
			$rows = $this->Db->getRows(
					"SELECT id, lang FROM `".$this->schema["table"]."` 
					WHERE 
						(sid = :sid || id = :sid) &&
						id != :id", 
					[	":sid" => $this->sid(),
						":id" => $this->id()]);
			$this->translations = [];
			foreach ($rows as $row) {
				$class = get_class($this);
				$this->translations[$row->lang] = new $class($this->Db, $row->id);
			}
		}
		return $this->translations;
	}

	public function translation($lang) {
		if ($this->get("lang") == $lang)
			return $this;
		if (!array_key_exists($lang, $this->translations)) {
			$this->translations[$lang] = null;
			if ($this->id()) {
				$sid = $this->get("sid");
				$row = $this->Db->getRow("
						SELECT id, lang FROM `".$this->schema["table"]."`
						WHERE
							(sid = :sid || id = :sid) &&
							id != :id &&
							lang = :lang",
						[	":sid" => $this->sid(),
							":id" => $this->id(),
							":lang" => $lang]);
				if ($row) {
					$class = get_class($this);
					$this->translations[$lang] = new $class($this->Db, $row->id);
				}
			}
		}
		return $this->translations[$lang];
	}


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