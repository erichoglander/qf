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

	/* UPDATER ACCESS */
	protected function updateAccess($User) {
		return IS_CLI;
	}
	
	/* FORM ACCESS */
	protected function formFileRemoveAccess($User, $file_id) {
		if ($User->id() == 1)
			return true;
		if (!empty($_SESSION["file_uploaded"]) && in_array($file_id, $_SESSION["file_uploaded"]))
			return true;
		return false;
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
	protected function userSettingsAccess($User) {
		return !!$User->id();
	}

	/* L10N ACCESS */
	protected function l10nAdminAccess($User) {
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

	/* CRON ACCESS */
	protected function cronRunAccess($User) {
		return $User->id() == 1 || IS_CLI;
	}

	/* CACHE ACCESS */
	protected function cacheClearAccess($User) {
		return $User->id() == 1;
	}

	/* CONTENT ACCESS */
	protected function contentAdminAccess($User) {
		return $User->id() == 1;
	}
	protected function contentConfigAccess($User) {
		return $User->id() == 1;
	}
	protected function contentEditAccess($User) {
		return $User->id() == 1;
	}
	protected function contentDeleteAccess($User) {
		return $User->id() == 1;
	}

}