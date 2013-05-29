<?php
$_NO_TOKEN = true;
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';

$name = Html::getRequest('name', false, Html::ANY);

if ($name !== false) {
	$name = str_replace('fichiers/', '', $name);
	$realPath = $_SERVER['DOCUMENT_ROOT'] . '/../files/' . $name;
	if (file_exists($realPath) === true) {
		session_cache_limiter('private');
		header("Content-Length: " . (string) (filesize($realPath)));
		if (strpos($name, '.jpg') === false) {
			header('Content-Disposition: attachment; filename="' . $name . '"');
		} else {
			header('Content-type: image/jpeg');
		}
		readfile($realPath);
		exit();
	}

}
header("HTTP/1.0 404 Not Found");

