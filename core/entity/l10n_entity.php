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
	 * Set default language for current entity
	 * @param  string $lang
	 */
	public function setLang($lang = null) {
		if (!$lang)
			$lang = $this->default_lang;
		$this->set("lang", $lang);
	}

	/**
	 * Include translations in the json object
	 * @return array
	 */
	public function json($include_translations = true) {
		$json = parent::json();
		if ($include_translations || !empty($this->translations())) {
			$json->translations = [];
			foreach ($this->translations() as $lang => $Entity) {
				$json->translations[$lang] = $Entity->json();
			}
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
					[	":id" => $this->id()]);
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
	public function createAlias() {
		if (!$this->id() || !is_callable([$this, "getPath"]) || !is_callable([$this, "getAlias"]))
			return false;
		if (!$this->Io)
			$this->Io = newClass("Io");
		$alias = $this->Io->filter($this->getAlias(), "alias");
		$path = $this->getPath();
		$lang = $this->get("lang", $this->default_lang);
		if (!$alias || !$path)
			return false;
		$q = "
				SELECT * FROM `alias`
				WHERE 
					alias = :alias &&
					(lang = :lang || lang IS NULL)";
		$vars = [	
			":alias" => $alias,
			":lang" => $lang
		];
		$row = $this->Db->getRow($q, $vars);
		// Find an available alias
		if ($row && $row->path != $path) {
			for ($i = 1; $row && $row->path != $path; $i++) {
				$vars[":alias"] = $alias."-".$i;
				$row = $this->Db->getRow($q, $vars);
			}
			$alias = $a;
		}
		if ($row) {
			if ($row->status == 0)
				$this->Db->update("alias", ["status" => 1], ["id" => $row->id]);
			return true;
		}
		else {
			$this->deleteAlias();
			$this->Db->delete("alias", ["path" => $path, "lang" => $lang]);
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
	 * @return bool
	 */
	public function deleteAlias() {
		if (!$this->id() || !is_callable([$this, "getPath"]) || !is_callable([$this, "getAlias"]))
			return false;
		return $this->Db->delete("alias", ["path" => $this->getPath(), "lang" => $this->get("lang")]);
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