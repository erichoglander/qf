<?php
function classAutoload($class) {
	$fname = strtolower($class).".php";
	$suffixes = ["controller", "model", "entity"];
	foreach ($suffixes as $suffix) {
		$csuffix = "_".ucwords($suffix);
		if (strpos($class, $csuffix) !== false) {
			$epath = DOC_ROOT."/extend/".$suffix."/".$fname;
			$cpath = DOC_ROOT."/core/".$suffix."/".$fname;
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
		$fname = strtolower(preg_replace("/([a-z])([A-Z])/", "$1_$2", $class).".php");
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
	if (empty($args))
		return new $cname();
	else {
		$r = new ReflectionClass($cname);
		return $r->newInstanceArgs($args);
	}
}


function pr($data) {
	print "<pre>".print_r($data,1)."</pre>";
}

function xss($str) {
	return htmlspecialchars($str, ENT_QUOTES);
}

function guid() {
	return substr(md5(microtime().REQUEST_TIME.rand(1, 1000)), 0, 12);
}

function renderTemplate($path, $vars) {
	extract($vars);
	ob_start();
	include $path;
	return ob_get_clean();
}