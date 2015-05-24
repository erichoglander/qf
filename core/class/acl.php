<?php
class Acl_Core {

	protected $Db, $User;

	public function __construct($Db) {
		$this->Db = &$Db;
	}

	public function access($User, $names, $args = []) {
		if ($names === null)
			return true;
		if (!is_array($names))
			$names = [$names];
		foreach ($names as $name) {
			$func = $name."Access";
			if (is_callable([$this, $func]) && $this->$func($User, $args))
				return true;
		}
		return false;
	}


	/* MENU ACCESS */
	protected function menuAdminAccess($User) {
		return $User->id() == 1;
	}

	/* USER ACCESS */
	protected function userAdminAccess($User) {
		return $User->id() == 1;
	}
	protected function userEditAccess($User) {
		return $User->id() == 1;
	}
	protected function userDeleteAccess($User) {
		return $User->id() == 1;
	}
	protected function userListAccess($User) {
		return $User->id() == 1;
	}
	protected function userSigninAccess($User) {
		return $User->id() == 1;
	}

	/* ALIAS ACCESS */
	protected function aliasAdminAccess($User) {
		return $User->id() == 1;
	}

	/* REDIRECT ACCESS */
	protected function redirectAdminAccess($User) {
		return $User->id() == 1;
	}


	/* LOG ACCESS */
	protected function logAdminAccess($User) {
		return $User->id() == 1;
	}

}