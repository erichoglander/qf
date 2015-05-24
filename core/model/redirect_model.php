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

}