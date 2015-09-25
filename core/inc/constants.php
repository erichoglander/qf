<?php
define("REQUEST_TIME", time());
define("IS_CLI", !empty($_SERVER["SHELL"]));
if (!IS_CLI) {
	define("HTTP_PROTOCOL", (empty($_SERVER["HTTPS"]) ? "http" : "https"));
	define("BASE_DOMAIN", preg_replace("/^.*\.([^\.]+\.[^\.]+)$/", "$1", $_SERVER["SERVER_NAME"]));
	define("SITE_URL", HTTP_PROTOCOL."://".$_SERVER["HTTP_HOST"]);
}