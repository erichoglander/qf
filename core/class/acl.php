<?php
/**
 * Contains acl class
 */

/**
 * Acl class
 *
 * Access control list
 * Sets access/permissions
 * 
 * @author Eric Höglander
 */
class Acl_Core {

	/**
	 * Database object
	 * @var \Db_Core
	 */
	protected $Db;


	/**
	 * Constructor
	 * @param \Db_Core $Db
	 */
	public function __construct($Db) {
		$this->Db = $Db;
	}

	/**
	 * Checks access for a user
	 * @param  \User_Entity_Core $User
	 * @param  array|string      $names What permissions to check
	 * @param  array             $args
	 * @return bool
	 */
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


	/**
	 * Admin menu access
	 * @param  \User_Entity_Core $User
	 * @return bool
	 */
	protected function menuAdminAccess($User) {
		return $User->id() == 1;
	}

	/**
	 * Updater access
	 * @see    \Updater_Controller_Core::acl()
	 * @param  \User_Entity_Core $User
	 * @return bool
	 */
	protected function updateAccess($User) {
		return IS_CLI;
	}
	
	/**
	 * Access to remove a file from a form
	 * @see    \Form_Controller_Core::acl()
	 * @param  \User_Entity_Core $User
	 * @param  array             $args
	 * @return bool
	 */
	protected function formFileRemoveAccess($User, $args = []) {
		if ($User->id() == 1)
			return true;
		if (!empty($args[0]) && !empty($_SESSION["file_uploaded"]) && in_array($args[0], $_SESSION["file_uploaded"]))
			return true;
		return false;
	}

	/**
	 * Complete user admin access
	 * @see    \User_Controller_Core::acl()
	 * @param  \User_Entity_Core $User
	 * @return bool
	 */
	protected function userAdminAccess($User) {
		return $User->id() == 1;
	}
	/**
	 * Access to edit a user
	 * @see    \User_Controller_Core::acl()
	 * @param  \User_Entity_Core $User
	 * @return bool
	 */
	protected function userEditAccess($User) {
		return $User->id() == 1;
	}
	/**
	 * Access to delete a user
	 * @see    \User_Controller_Core::acl()
	 * @param  \User_Entity_Core $User
	 * @return bool
	 */
	protected function userDeleteAccess($User) {
		return $User->id() == 1;
	}
	/**
	 * Access to the user list
	 * @see    \User_Controller_Core::acl()
	 * @param  \User_Entity_Core $User
	 * @return bool
	 */
	protected function userListAccess($User) {
		return $User->id() == 1;
	}
	/**
	 * Access to sign in as another user
	 * @see    \User_Controller_Core::acl()
	 * @param  \User_Entity_Core $User
	 * @return bool
	 */
	protected function userSigninAccess($User) {
		return $User->id() == 1;
	}
	/**
	 * Access to the users own settings
	 * @see    \User_Controller_Core::acl()
	 * @param  \User_Entity_Core $User
	 * @return bool
	 */
	protected function userSettingsAccess($User) {
		return !!$User->id();
	}
	/**
	 * Access to set the password of another user through
	 * @see    \User_Controller_Core::acl()
	 * @param  \User_Entity_Core $User
	 * @return bool
	 */
	protected function userSetPassAccess($User) {
		return IS_CLI;
	}
	/**
	 * Access to clear failed login attempts
	 * @see    \User_Controller_Core::acl()
	 * @param  \User_Entity_Core $User
	 * @return bool
	 */
	protected function userClearFloodAccess($User) {
		return $User->id() == 1;
	}

	/**
	 * Complete localization admin access
	 * @see    \l10n_Controller_Core::acl()
	 * @param  \User_Entity_Core $User
	 * @return bool
	 */
	protected function l10nAdminAccess($User) {
		return $User->id() == 1;
	}

	/**
	 * Complete url alias admin access
	 * @see    \Alias_Controller_Core::acl()
	 * @param  \User_Entity_Core $User
	 * @return bool
	 */
	protected function aliasAdminAccess($User) {
		return $User->id() == 1;
	}

	/**
	 * Complete redirect admin access
	 * @see    \Redirect_Controller_Core::acl()
	 * @param  \User_Entity_Core $User
	 * @return bool
	 */
	protected function redirectAdminAccess($User) {
		return $User->id() == 1;
	}

	/**
	 * Complete log admin access
	 * @see    \Log_Controller_Core::acl()
	 * @param  \User_Entity_Core $User
	 * @return bool
	 */
	protected function logAdminAccess($User) {
		return $User->id() == 1;
	}

	/**
	 * Access to run manually cron
	 * @see    \Cron_Controller_Core::acl()
	 * @param  \User_Entity_Core $User
	 * @return bool
	 */
	protected function cronRunAccess($User) {
		return $User->id() == 1 || IS_CLI;
	}

	/**
	 * Access to manually clear the cache
	 * @see    \Cache_Controller_Core::acl()
	 * @param  \User_Entity_Core $User
	 * @return bool
	 */
	protected function cacheClearAccess($User) {
		return $User->id() == 1;
	}

	/**
	 * Complete content admin access
	 * @see    \Content_Controller_Core::acl()
	 * @param  \User_Entity_Core $User
	 * @return bool
	 */
	protected function contentAdminAccess($User) {
		return $User->id() == 1;
	}
	/**
	 * Access to configure content
	 * @see    \Content_Controller_Core::acl()
	 * @param  \User_Entity_Core $User
	 * @return bool
	 */
	protected function contentConfigAccess($User) {
		return $User->id() == 1;
	}
	/**
	 * Access to edit content
	 * @see    \Content_Controller_Core::acl()
	 * @param  \User_Entity_Core $User
	 * @return bool
	 */
	protected function contentEditAccess($User) {
		return $User->id() == 1;
	}
	/**
	 * Access to delete content
	 * @see    \Content_Controller_Core::acl()
	 * @param  \User_Entity_Core $User
	 * @return bool
	 */
	protected function contentDeleteAccess($User) {
		return $User->id() == 1;
	}
	
	/**
	 * Access to private files
	 * @see    \File_Controller_Core::acl()
	 * @param  \User_Entity_Core $User
	 * @return bool
	 */
	protected function filePrivateAccess($User) {
		if ($User->id() == 1)
			return true;
		return false;
	}
	/**
	 * Access to a private file uri
	 * @see    \File_Controller_Core::privateAction()
	 * @param  \User_Entity_Core $User
	 * @param  string $uri
	 * @return bool
	 */
	protected function filePrivateUriAccess($User, $uri) {
		return true;
	}

}