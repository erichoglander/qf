<?php
class Alias_Model_Core extends Model {
	
	public function filterAlias($alias) {
		$alias = str_replace(["Å", "å", "Ä", "ä", "À", "à", "Á", "á", "Æ", "æ"], "a", $alias);
		$alias = str_replace(["Ö", "ö", "Õ", "õ", "Ó", "ó", "Ò", "ò", "Ø", "ø", "ð"], "o", $alias);
		$alias = str_replace(["Ë", "ë", "É", "é", "È", "è", "Ê", "ê"], "e", $alias);
		$alias = str_replace(["Ï", "ï", "Í", "í", "Ì", "ì", "Î", "î"], "i", $alias);
		$alias = str_replace(["Ü", "ü", "Ú", "ú", "Ù", "ù", "Û", "û"], "u", $alias);
		$alias = str_replace(["Ç", "ç"], "c", $alias);
		$alias = str_replace(["Ñ", "ñ"], "n", $alias);
		$alias = str_replace("ß", "ss", $alias);
		$alias = str_replace(["Ž", "ž"], "z", $alias);
		$alias = strtolower($alias);
		$alias = preg_replace("/\s+/", "-", $alias);
		$alias = preg_replace("/[^a-z0-9\-\_\/]/", "", $alias);
		$alias = preg_replace("/[\-]+/", "-", $alias);
		return $alias;
	}
	
	public function createAlias($path, $alias, $unique = false) {
		$alias = $this->filterAlias($alias);
		$row = $this->Db->getRow("
				SELECT * FROM `alias`
				WHERE alias = :alias",
				[":alias" => $alias]);
		if ($row) {
			if ($row->path != $path)
				return null;
			if ($row->status == 0)
				$this->Db->update("alias", ["status" => 1], ["id" => $row->id]);
			return $this->getEntity("Alias", $row->id);
		}
		if ($unique)
			$this->Db->delete("alias", ["path" => $path]);
		$Alias = $this->getEntity("Alias");
		$Alias->set("path", $path);
		$Alias->set("alias", $alias);
		if (!$Alias->save())
			return null;
		return $Alias;
	}
	
	public function addAlias($values) {
		$Alias = $this->getEntity("Alias");
		return $this->editAlias($Alias, $values);
	}

	public function editAlias($Alias, $values) {
		foreach ($values as $key => $value)
			$Alias->set($key, $value);
		return $Alias->save();
	}

	public function deleteAlias($Alias) {
		return $Alias->delete();
	}

	public function getAliases() {
		$rows = $this->Db->getRows("SELECT id FROM `alias` ORDER BY alias ASC");
		$aliases = [];
		foreach ($rows as $row)
			$aliases[] = $this->getEntity("Alias", $row->id);
		return $aliases;
	}

}