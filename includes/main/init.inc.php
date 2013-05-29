<?php
//	Starting session
session_start();

//	Setting locale to french
setlocale(LC_ALL, 'fr_FR.UTF8', 'fra');

//	Init DOCUMENT_ROOT  if not specified
if (isset($_SERVER['DOCUMENT_ROOT']) === false || realpath($_SERVER['DOCUMENT_ROOT']) === false) {
	$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
	if (isset($_SERVER['HOME']) === true && realpath($_SERVER['HOME']) !== false) {
		$_SERVER['DOCUMENT_ROOT'] = $_SERVER['HOME'] . DIRECTORY_SEPARATOR.'www'.DIRECTORY_SEPARATOR;
	} else {
		$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'www') . DIRECTORY_SEPARATOR;
	}
}
