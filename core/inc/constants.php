<?php
define("REQUEST_TIME", time());
define("PUBLIC_URI", "/files");
define("PRIVATE_URI", "/file/private");
define("PUBLIC_PATH", DOC_ROOT.PUBLIC_URI);
define("PRIVATE_PATH", DOC_ROOT."/files/private");
define("MAX_LOGS", 100000);
define("IS_CLI", !empty($_SERVER["SHELL"]));
define("LANG", "sv");
if (!IS_CLI) {
	define("HTTP_PROTOCOL", (empty($_SERVER["HTTPS"]) ? "http" : "https"));
	define("BASE_DOMAIN", preg_replace("/^.*\.([^\.]+\.[^\.]+)$/", "$1", $_SERVER["SERVER_NAME"]));
	define("SITE_URL", HTTP_PROTOCOL."://".$_SERVER["HTTP_HOST"]);
}