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
		$cpath = DOC_ROOT."/core/class/".$fname;
		if (file_exists($epath)) {
			if (file_exists($cpath))
				require_once($cpath);
			require_once($epath);
		}
		else if (file_exists($cpath))
			require_once($cpath);
		else
			throw new Exception("Can't find class ".$class." (".$fname.")");
	}
}
spl_autoload_register("classAutoload");


function pr($data) {
	print "<pre>".print_r($data,1)."</pre>";
}

function promptFile($path) {
	$info = pathinfo($path);
	header_remove("Content-Type");
	$ext = strtolower($info['extension']);
	$images = ["png", "jpg", "jpeg", "gif"];
	if (in_array($ext, $images))
		header("Content-Type: image/".$ext);
	$file = fopen($path, "r");
	echo fpassthru($file);
	fclose($file);
	exit;
}

function formatBytes($bytes) {
	if (!$bytes) return "0B";
	$units = ["B", "kB", "MB", "GB", "TB"];
	$pow = floor(log($bytes)/log(1024));
	$bytes/= pow(1024, $pow);
	return round($bytes, 2).$units[$pow];
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