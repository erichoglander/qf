<?php
/**
 * Unix timestamp of when the request began
 * @var int
 */
define("REQUEST_TIME", time());

/**
 * Where or not the request is made through cli
 * @var bool
 */
define("IS_CLI", !empty($_SERVER["SHELL"]));

if (!IS_CLI) {
	/**
	 * What http protocol was used for the request, http or https
	 * @var string
	 */
	define("HTTP_PROTOCOL", (empty($_SERVER["HTTPS"]) ? "http" : "https"));

	/**
	 * Domain of site excluding subdomains
	 *
	 * Ex: www.mysite.com turns into mysite.com
	 * @var string
	 */
	define("BASE_DOMAIN", preg_replace("/^.*\.([^\.]+\.[^\.]+)$/", "$1", $_SERVER["SERVER_NAME"]));

	/**
	 * Site url including protocol
	 *
	 * Ex: https://www.mysite.com
	 * @var string
	 */
	define("SITE_URL", HTTP_PROTOCOL."://".$_SERVER["HTTP_HOST"]);
}