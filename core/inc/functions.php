<?php
function classAutoload($class) {
	$fname = classToFile($class);
	$suffixes = ["controller", "model", "entity", "form_item", "form", "mail"];
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
	if (strpos($class, "_Theme") !== false) {
		$dir = str_replace(".php", "", $fname);
		$cpath = DOC_ROOT."/core/theme/".$dir."/theme.php";
		$epath = DOC_ROOT."/extend/theme/".$dir."/theme.php";
		if (file_exists($cpath))
			require_once($cpath);
		if (file_exists($epath)) 
			require_once($epath);
	}
	else {
		$cpath = DOC_ROOT."/core/class/".$fname;
		$epath = DOC_ROOT."/extend/class/".$fname;
		if (file_exists($cpath))
			require_once($cpath);
		if (file_exists($epath))
			require_once($epath);
	}
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
	if (empty($args))
		return new $cname();
	else {
		$r = new ReflectionClass($cname);
		return $r->newInstanceArgs($args);
	}
}

function classToFile($class) {
	// SomeClassName_Model_Core -> some_class_name_model.php
	return str_replace(["_core", "_theme"], ["", ""], strtolower(preg_replace("/([a-z])([A-Z])/", "$1_$2", $class).".php"));
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
	$epath = "/extend/".$path;
	$cpath = "/core/".$path;
	if (file_exists(DOC_ROOT.$epath))
		return $epath;
	if (file_exists(DOC_ROOT.$cpath))
		return $cpath;
	return null;
}

function renderTemplate($path, $vars) {
	extract($vars);
	ob_start();
	include $path;
	return ob_get_clean();
}

function t($str) {
	// TODO: Translation
	return $str;
}

function setmsg($msg, $type = "normal") {
	if (!isset($_SESSION["sysmsg"]))
		$_SESSION["sysmsg"] = [];
	if (!isset($_SESSION["sysmsg"][$type]))
		$_SESSION["sysmsg"][$type] = [];
	$_SESSION["sysmsg"][$type][] = $msg;
}
function getmsgs($type = null) {
	if ($type)
		return (isset($_SESSION["sysmsg"][$type]) ? $_SESSION["sysmsg"][$type] : null);
	return (isset($_SESSION["sysmsg"]) ? $_SESSION["sysmsg"] : null);
}
function clearmsgs($type = null) {
	if ($type)
		unset($_SESSION["sysmsg"][$type]);
	else
		unset($_SESSION["sysmsg"]);
}

function addlog($category, $subject, $data = null) {
	// TODO: Logging
}

function redirect($url = "", $redir = true) {
	if ($redir && array_key_exists("redir", $_GET))
		$url = $_GET['redir'];
	if (strpos($url, "http") !== 0)
		$url = "/".$url;
	header("Location: ".$url);
	exit;
}
function refresh() {
	$url = substr($_SERVER['REQUEST_URI'], 1);
	redirect($url);
}