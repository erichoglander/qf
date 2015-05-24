<?php
class Alias_Model_Core extends Model {
	
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