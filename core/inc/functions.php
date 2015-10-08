<?php
function classAutoload($class) {
	$fname = classToFile($class);
	$suffixes = ["controller", "model", "entity", "form_item", "form", "mail"];
	$dirs = ["theme", "library"];
	foreach ($suffixes as $suffix) {
		$csuffix = "";
		foreach (explode("_", $suffix) as $sfx)
			$csuffix.= ucwords($sfx);
		if (preg_match("/_".$csuffix."(_|$)/", $class)) {
			$cpath = DOC_ROOT."/core/".$suffix."/".$fname;
			$epath = DOC_ROOT."/extend/".$suffix."/".$fname;
			if (file_exists($cpath))
				require_once($cpath);
			if (file_exists($epath))
				require_once($epath);
			return;
		}
	}
	foreach ($dirs as $dir) {
		$csuffix = "_";
		foreach (explode("_", $dir) as $sfx)
			$csuffix.= ucwords($sfx);
		if (strpos($class, $csuffix) !== false) {
			$fdir = str_replace(".php", "", $fname);
			$cpath = DOC_ROOT."/core/".$dir."/".$fdir."/".$dir.".php";
			$epath = DOC_ROOT."/extend/".$dir."/".$fdir."/".$dir.".php";
			if (file_exists($cpath))
				require_once($cpath);
			if (file_exists($epath)) 
				require_once($epath);
			return;
		}
	}
	$cpath = DOC_ROOT."/core/class/".$fname;
	$epath = DOC_ROOT."/extend/class/".$fname;
	if (file_exists($cpath))
		require_once($cpath);
	if (file_exists($epath))
		require_once($epath);
}
spl_autoload_register("classAutoload");

function newClass($cname) {
	if (!class_exists($cname)) {
		$cname.= "_Core";
		if (!class_exists($cname))
			return null;
	}
	$args = func_get_args();
	array_shift($args); // Remove the class name from argument list
	if (empty($args)) {
		if ($cname == "ControllerFactory_Core")
			pr(debug_backtrace());
		return new $cname();
	}
	else {
		$r = new ReflectionClass($cname);
		return $r->newInstanceArgs($args);
	}
}

function classToFile($class) {
	// SomeClassName_Model_Core -> some_class_name_model.php
	return str_replace(["_core", "_theme", "_library"], "", classToDir($class).".php");
}
function classToDir($class) {
	return strtolower(preg_replace("/([a-z])([A-Z])/", "$1_$2", $class));
}

function formatBytes($bytes) {
	if (!$bytes) return "0B";
	$units = Array("B", "kB", "MB", "GB", "TB");
	$pow = floor(log($bytes)/log(1024));
	$bytes/= pow(1024, $pow);
	return round($bytes, 2).$units[$pow];
}

// 3,74 -> 374
function decimalInt($value) {
	$value = str_replace(" ", "", $value);
	$value = str_replace(",", ".", $value);
	$x = strpos($value, ".");
	if ($x === false)
		return $value*100;
	else {
		$int = substr($value, 0, $x);
		$dec = substr($value, $x+1, 2);
		if (strlen($dec) == 1)
			$dec.= "0";
		return (int) $int.$dec;
	}
}
// 374 -> 3,74
function decimalFloat($value, $p = 2, $dec = ",", $thousand = " ") {
	return number_format($value/100, $p, $dec, $thousand);
}

function getjson($assoc = false) {
	$post = @file_get_contents("php://input");
	if (!$post)
		return null;
	$post = trim($post);
	if (strpos($post, "{") === 0 || strpos($post, "[") === 0)
		return @json_decode($post, $assoc);
	return null;
}

function pr($data, $ret = 0) {
	$html = "<pre>".print_r($data,1)."</pre>";
	if ($ret)
		return $html;
	else
		print $html;
}

function xss($str) {
	return htmlspecialchars($str, ENT_QUOTES);
}

function shorten($str, $len) {
	if (strlen($str) > $len) {
		$x = strrpos($str, " ");
		if ($x > $len-3)
			return shorten(substr($str, 0, $x));
		$str = substr($str, 0, $x)."...";
	}
	return $str;
}

function cssClass($str) {
	$str = strtolower($str);
	$str = preg_replace("/[\ \_]/", "-", $str);
	$str = preg_replace("/[\-]+/", "-", $str);
	$str = preg_replace("/[^a-z0-9\-]/", "", $str);
	return $str;
}

function guid() {
	return substr(md5(microtime().REQUEST_TIME.rand(1, 1000)), 0, 12);
}

function filePath($path) {
	$epath = DOC_ROOT."/extend/".$path;
	$cpath = DOC_ROOT."/core/".$path;
	if (file_exists($epath))
		return $epath;
	if (file_exists($cpath))
		return $cpath;
	return null;
}
function fileUrl($path) {
	$epath = "extend/".$path;
	$cpath = "core/".$path;
	if (file_exists(DOC_ROOT."/".$epath))
		return BASE_PATH.$epath;
	if (file_exists(DOC_ROOT."/".$cpath))
		return BASE_PATH.$cpath;
	return null;
}

function renderTemplate($include_path, $vars) {
	extract($vars);
	ob_start();
	include $include_path;
	return ob_get_clean();
}

function t($str, $lang = "en", $vars = []) {
	global $Db;
	$l10nString = newClass("l10nString_Entity", $Db);
	if (!$l10nString->loadFromString($str, $lang)) {
		$l10nString->set("lang", $lang);
		$l10nString->set("string", $str);
		$l10nString->set("input_type", "code");
		$l10nString->save();
	}
	else {
		$Translation = $l10nString->translation(LANG);
		if ($Translation)
			$str = $Translation->get("string");
	}
	if (!empty($vars))
		$str = str_replace(array_keys($vars), array_values($vars), $str);
	return $str;
}

function uri($path) {
	global $Db;
	if ($path == "<front>") {
		$path = "";
	}
	else {
		$row = $Db->getRow("
				SELECT * FROM `alias`
				WHERE path = :path && status = 1",
				["path" => $path]);
		if ($row)
			$path = $row->alias;
	}
	return $path;
}
function url($path) {
	return BASE_URL.uri($path);
}

function setmsg($msg, $type = "info") {
	if (!isset($_SESSION["sysmsg"]))
		$_SESSION["sysmsg"] = [];
	$_SESSION["sysmsg"][] = [
		"type" => $type,
		"message" => $msg,
	];
}
function getmsgs() {
	return (isset($_SESSION["sysmsg"]) ? $_SESSION["sysmsg"] : null);
}
function clearmsgs() {
	unset($_SESSION["sysmsg"]);
}

function addlog($category, $text, $data = null, $type = "info") {
	global $Db, $Config;
	$obj = [
		"user_id" => (!empty($_SESSION["user_id"]) ? $_SESSION["user_id"] : 0),
		"ip" => (!empty($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : null),
		"created" => REQUEST_TIME,
		"type" => $type,
		"category" => $category,
		"text" => $text,
	];
	try {
		$obj["data"] = serialize($data);
	}
	catch (Exception $e) {
		$string = print_r($data, 1);
		$obj["data"] = serialize($string);
	}
	$Db->insert("log", $obj);
	$row = $Db->getRow("SELECT COUNT(id) as num FROM `log`");
	if ($row && $row->num > $Config->getMaxLogs())
		$Db->query("DELETE FROM `log` ORDER BY id ASC LIMIT 1");
}

function redirect($url = "", $redir = true) {
	if ($redir && array_key_exists("redir", $_GET))
		$url = $_GET["redir"];
	$pcl = strpos($url, "://");
	if ($pcl === false || $pcl > 8)
		$url = url($url);
	if (IS_CLI)
		print "Redirect: ".$url."\n";
	else
		header("Location: ".$url);
	exit;
}
function refresh() {
	redirect(REQUEST_URI);
}

function httpRequest($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$re = curl_exec($ch);
	curl_close($ch);
	return $re;
}

function promptFile($path) {
	header("Content-Type: application/octet-stream");
	readfile($path);
	exit;
}