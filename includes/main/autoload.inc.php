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

	if (file_exists($path) === true) {
		include_once $path;
	}
}
