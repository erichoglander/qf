<?php
function classAutoload($class) {
	$fname = strtolower($class).".php";
	if (strpos($class, "_Controller") !== false) {
		$epath = DOC_ROOT."/extend/controller/".$fname;
		$cpath = DOC_ROOT."/core/controller/".$fname;
		if (file_exists($epath)) {
			require_once($epath);
			if (file_exists($cpath))
				require_once($cpath);
		}
		else if (file_exists($cpath))
			require_once($cpath);
	}
	else if (strpos($class, "_Model") !== false) {
		$epath = DOC_ROOT."/extend/model/".$fname;
		$cpath = DOC_ROOT."/core/model/".$fname;
		if (file_exists($epath)) {
			require_once($epath);
			if (file_exists($cpath))
				require_once($cpath);
		}
		else if (file_exists($cpath))
			require_once($cpath);
	}
	else {
		$fname = strtolower(preg_replace("/([a-z])([A-Z])/", "$1_$2", $class).".php");
		$epath = DOC_ROOT."/extend/class/".$fname;
		$cpath = DOC_ROOT."/core/class/".$fname;
		if (file_exists($epath)) {
			require_once($epath);
			if (file_exists($cpath))
				require_once($cpath);
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
	$images = Array("png", "jpg", "jpeg", "gif");
	if (in_array($ext, $images))
		header("Content-Type: image/".$ext);
	$file = fopen($path, "r");
	echo fpassthru($file);
	fclose($file);
	exit;
}

function formatBytes($bytes) {
	if (!$bytes) return "0B";
	$units = Array("B", "kB", "MB", "GB", "TB");
	$pow = floor(log($bytes)/log(1024));
	$bytes/= pow(1024, $pow);
	return round($bytes, 2).$units[$pow];
}

function guid() {
	return substr(md5(microtime().REQUEST_TIME.rand(1, 1000)), 0, 12);
}
