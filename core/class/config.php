<?php
/**
 * Default config file
 */

/**
 * Config class
 *
 * Each function returns a specific configuration option
 * 
 * @author Eric Höglander
 */
class Config_Core {

	/**
	 * The name of the site
	 * @return string
	 */
	public function getSiteName() {
		return BASE_DOMAIN;
	}

	/**
	 * The database config
	 * @return array
	 */
	public function getDatabase() {
		$file = filePath("inc/database.php");
		if (!$file)
			return null;
		require_once($file);
		if (!isset($database))
			return null;
		return $database;
	}

	/**
	 * If there is a default subdomain that should be used when visiting the site
	 * @return string Ex: www
	 */
	public function getSubdomain() {
		return null;
	}

	/**
	 * If the site should redirect visitors to https
	 * @return bool
	 */
	public function getHttps() {
		return false;
	}

	/**
	 * If the site should be in debug mode
	 * @return bool
	 */
	public function getDebug() {
		return false;
	}

	/**
	 * What type of user registration should be active
	 * @return string "closed", "email_confirmation", "admin_approval", or "open"
	 */
	public function getUserRegistration() {
		return "closed";
	}

	/**
	 * Libraries to be included 
	 * @return array
	 */
	public function getLibraries() {
		return ["Default", "JsonToHtml", "FontAwesome", "CKEditor"];
	}

	/**
	 * If automatic cron should be activated
	 * @return bool If true, cron will run when people visit the site
	 */
	public function getAutomaticCron() {
		return true;
	}

	/**
	 * The default language of the site
	 * @return string
	 */
	public function getDefaultLanguage() {
		return "sv";
	}

	/**
	 * What type of language detection to use
	 * @return string null or "path"
	 */
	public function getLanguageDetection() {
		return null;
	}

	/**
	 * How many log entries to save in the database
	 * @return int
	 */
	public function getMaxLogs() {
		return 100000;
	}

	/**
	 * The uri for public files
	 * @see PUBLIC_URI
	 * @return string
	 */
	public function getPublicUri() {
		return "files";
	}

	/**
	 * The uri for private files
	 * @see PRIVATE_URI
	 * @return string
	 */
	public function getPrivateUri() {
		return "file/private";
	}

	/**
	 * The path for public files
	 * @see PUBLIC_PATH
	 * @return string
	 */
	public function getPublicPath() {
		return DOC_ROOT."/".$this->getPublicUri();
	}

	/**
	 * The path for private files
	 * @see PRIVATE_PATH
	 * @return string
	 */
	public function getPrivatePath() {
		return substr(DOC_ROOT, 0, strrpos(DOC_ROOT, "/"))."/private";
	}
	
	/**
	 * The timespan for failed login attempts
	 * @see \User_Entity_Core::userFloodProtection()
	 * @return int
	 */
	public function getFloodProtectionTime() {
		return 60*60*12;
	}

	/**
	 * The menus for the site
	 * @return array An array of menus and links to be rendered
	 */
	public function getMenus() {
		$menu = [
			"admin" => [
				"body_class" => "admin-menu",
				"acl" => "menuAdmin",
				"links" => [
					"home" => [
						"faicon" => "home",
						"href" => "",
					],
					"logout" => [
						"faicon" => "sign-out",
						"href" => "user/logout",
						"return" => true,
					],
					"settings" => [
						"faicon" => "cog",
						"href" => "user/settings",
					],
					"user" => [
						"title" => "Users",
						"href" => "user/list",
						"links" => [
							"user-add" => [
								"title" => "Add user",
								"href" => "user/add",
							],
						],
					],
					"content" => [
						"title" => "Content",
						"href" => "content/list",
						"links" => [
							"content-add" => [
								"title" => "Add content",
								"href" => "content/add",
							],
						],
					],
					"system" => [
						"title" => "System",
						"links" => [
							"alias" => [
								"title" => "Aliases",
								"href" => "alias/list",
								"links" => [
									"alias-add" => [
										"title" => "Add alias",
										"href" => "alias/add",
									],
								],
							],
							"redirect" => [
								"title" => "Redirects",
								"href" => "redirect/list",
								"links" => [
									"redirect-add" => [
										"title" => "Add redirect",
										"href" => "redirect/add",
									],
								],
							],
							"l10n" => [
								"title" => "Localization",
								"href" => "l10n/list",
								"links" => [
									"scan" => [
										"title" => "Scan code",
										"href" => "l10n/scan",
									],
									"export" => [
										"title" => "Export",
										"href" => "l10n/export",
									],
									"import" => [
										"title" => "Import",
										"href" => "l10n/import",
									],
								],
							],
							"logs" => [
								"title" => "Logs",
								"href" => "log/list",
							],
							"cache" => [
								"title" => "Cache",
								"href" => "cache/list",
								"links" => [
									"clear" => [
										"title" => "Clear all",
										"href" => "cache/clear",
										"return" => true,
									],
									"clear-data" => [
										"title" => "Clear data",
										"href" => "cache/clear/data",
										"return" => true,
									],
									"clear-images" => [
										"title" => "Clear images",
										"href" => "cache/clear/images",
										"return" => true,
									],
								],
							],
							"cron-run" => [
								"title" => "Run cron",
								"href" => "cron",
								"return" => true,
							],
							"user-clear-flood" => [
								"title" => "Clear login attempts",
								"href" => "user/clear-flood",
							],
						],
					],
				],
			],
		];
		return $menu;
	}

};