<?php
class Alias_Model_Core extends Model {
	
	public function createAlias($path, $alias, $unique = false) {
		$alias = $this->Io->filter($alias, "alias");
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
	
	public function listSearchQuery($values) {
		$sql = "SELECT id FROM `alias`";
		$vars = [];
		if (!empty($values["q"])) {
			$sql.= " WHERE path LIKE :q || alias LIKE :q";
			$vars[":q"] = "%".$values["q"]."%";
		}
		return [$sql, $vars];
	}
	public function listSearchNum($values = []) {
		list($sql, $vars) = $this->listSearchQuery($values);
		return $this->Db->numRows($sql, $vars);
	}
	public function listSearch($values = [], $start = 0, $stop = 30) {
		list($sql, $vars) = $this->listSearchQuery($values);
		$sql.= " ORDER BY path ASC LIMIT ".$start.", ".$stop;
		$rows = $this->Db->getRows($sql, $vars);
		$list = [];
		foreach ($rows as $row)
			$list[] = $this->getEntity("Alias", $row->id);
		return $list;
	}

}