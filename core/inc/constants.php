<?php
/**
 * Contains some basic constants based on the request
 * @author Eric Höglander
 */

/**
 * Unix timestamp of when the request began
 * @var int
 */
define("REQUEST_TIME", time());

/**
 * Whether or not the request is made through cli
 * @var bool
 */
define("IS_CLI", !empty($_SERVER["SHELL"]));


if (IS_CLI)
  $base_path = "/";
else
  $base_path = substr($_SERVER["SCRIPT_NAME"], 0, strrpos($_SERVER["SCRIPT_NAME"], "/")+1);
/**
 * The path before files
 * 
 * It's usually the same as BASE_URL, but does
 * not contain prefixes, so it can be used locate files
 * @var string
 */
define("BASE_PATH", $base_path);

if (!IS_CLI) {
  /**
   * Which http protocol was used for the request: http or https
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