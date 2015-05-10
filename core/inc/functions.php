<?php
function classAutoload($class) {
	$fname = strtolower(preg_replace("/([a-z])([A-Z])/", "$1_$2", $class).".php");
	$suffixes = ["controller", "model", "entity", "form_item", "form"];
	foreach ($suffixes as $suffix) {
		$csuffix = "_";
		foreach (explode("_", $suffix) as $sfx)
			$csuffix.= ucwords($sfx);
		if (strpos($class, $csuffix) === strlen($class)-strlen($csuffix)) {
			$epath = DOC_ROOT."/extend/".$suffix."/".$fname;
			$cpath = DOC_ROOT."/core/".$suffix."/".str_replace("_core", "", $fname);
			if (file_exists($epath)) {
				if (file_exists($cpath))
					require_once($cpath);
				require_once($epath);
			}
			if (file_exists($cpath))
				require_once($cpath);
			return;
		}
	}
	if (strpos($class, "_Theme") !== false) {
		$dir = str_replace("_core", "", str_replace("_theme", "", strtolower($class)));
		$epath = DOC_ROOT."/extend/theme/".$dir."/theme.php";
		$cpath = DOC_ROOT."/core/theme/".$dir."/theme.php";
		if (file_exists($epath)) {
			if (file_exists($cpath))
				require_once($cpath);
			require_once($epath);
		}
		else if (file_exists($cpath))
			require_once($cpath);
	}
	else {
		$epath = DOC_ROOT."/extend/class/".$fname;
		if (file_exists($epath))
			require_once($epath);
	}
}
spl_autoload_register("classAutoload");

function newClass($cname) {
	if (!class_exists($cname) && strpos($cname, "_") !== false) {
		$cname = str_replace("_", "_Core_", $cname);
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