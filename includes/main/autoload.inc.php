<?php

/**
 * Autoloader for php class
 * @param unknown $class_name
 * @throws Exception
 */
function __autoload($class_name) {
	$path = $_SERVER['DOCUMENT_ROOT'] . '/../class';
	foreach (explode('_', $class_name) As $e) {
		$path .= '/' . strtolower($e);
	}
	$path .= '.class.php';

	if (file_exists($path) === false) {
		var_dump("Failed to open <b>'" . $class_name . "'</b> class file :" . $path/*, E_USER_WARNING*/);
	} else {
		include_once $path;
		if (class_exists($class_name, false) === false) {
			var_dump("Failed to autoload class " . $class_name/*, E_USER_ERROR*/);
		}
	}
}
