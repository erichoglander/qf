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
   * Access to autocomplete results
   * @see    \Form_Controller_Core::acl()
   * @param  \User_Entity_Core $User
   * @param  array             $args
   * @return bool
   */
  protected function formAutocompleteAccess($User, $args = []) {
    return $User->id() == 1 || $User->hasRole("administrator");
  }

  /**
   * Access to upload file from a form item
   * @see    \Form_Controller_Core::acl()
   * @param  \User_Entity_Core $User
   * @param  array             $args
   * @return bool
   */
  protected function formItemUploadAccess($User, $args = []) {
    return $User->id() == 1 || $User->hasRole("administrator");
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
   * Access to edit translations
   * @see    \l10n_Controller_Core::acl()
   * @param  \User_Entity_Core $User
   * @return bool
   */
  protected function l10nEditAccess($User) {
    return false;
  }
  /**
   * Access to delete translations
   * @see    \l10n_Controller_Core::acl()
   * @param  \User_Entity_Core $User
   * @return bool
   */
  protected function l10nDeleteAccess($User) {
    return false;
  }
  /**
   * Access to import translations
   * @see    \l10n_Controller_Core::acl()
   * @param  \User_Entity_Core $User
   * @return bool
   */
  protected function l10nImportAccess($User) {
    return false;
  }
  /**
   * Access to export translations
   * @see    \l10n_Controller_Core::acl()
   * @param  \User_Entity_Core $User
   * @return bool
   */
  protected function l10nExportAccess($User) {
    return false;
  }
  /**
   * Access to scan code for translations
   * @see    \l10n_Controller_Core::acl()
   * @param  \User_Entity_Core $User
   * @return bool
   */
  protected function l10nScanAccess($User) {
    return false;
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
   * Batch update url alias access
   * @see    \Alias_Controller_Core::acl()
   * @param  \User_Entity_Core $User
   * @return bool
   */
  protected function aliasBatchAccess($User) {
    return IS_CLI;
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
    return $User->id() == 1 || IS_CLI;
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
    return $User->id() == 1;
  }
  /**
   * Access to cleanup managed files
   * @see    \File_Controller_Core::acl()
   * @param  \User_Entity_Core $User
   * @return bool
   */
  protected function fileCleanupAccess($User) {
    return IS_CLI;
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
  /**
   * Access to a imagestyle on demand
   * @see    \File_Controller_Core::imagestyleAction()
   * @param  \User_Entity_Core $User
   * @return bool
   */
  protected function fileImagestyleAccess($User) {
    return true;
  }

}