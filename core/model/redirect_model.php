<?php
class Redirect_Model_Core extends Model {
	
	public function addRedirect($values) {
		$Redirect = $this->getEntity("Redirect");
		return $this->editRedirect($Redirect, $values);
	}

	public function editRedirect($Redirect, $values) {
		foreach ($values as $key => $value)
			$Redirect->set($key, $value);
		return $Redirect->save();
	}

	public function deleteRedirect($Redirect) {
		return $Redirect->delete();
	}

	public function getRedirectes() {
		$rows = $this->Db->getRows("SELECT id FROM `redirect` ORDER BY source ASC");
		$redirects = [];
		foreach ($rows as $row)
			$redirects[] = $this->getEntity("Redirect", $row->id);
		return $redirects;
	}
	
	public function listSearchQuery($values) {
		$sql = "SELECT id FROM `redirect`";
		$vars = [];
		if (!empty($values["q"])) {
			$sql.= " WHERE source LIKE :q || target LIKE :q";
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
		$sql.= " ORDER BY source ASC LIMIT ".$start.", ".$stop;
		$rows = $this->Db->getRows($sql, $vars);
		$list = [];
		foreach ($rows as $row)
			$list[] = $this->getEntity("Redirect", $row->id);
		return $list;
	}

}