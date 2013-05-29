<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';
$context->addRequiredAuth('Gestion');

$id = Html::getRequestOrPost('id', false, Html::INTEGER);
$context->setLight(true);

if ($id !== false) {
	$letter = $context->getDb()->newsletters[$id];
	if (is_object($letter) === true) {
		Config::set('DEBUG', false);
		include $_SERVER['DOCUMENT_ROOT'] . '/../includes/start.inc.php';
		echo $letter['content'];
		include $_SERVER['DOCUMENT_ROOT'] . '/../includes/end.inc.php';
		exit;
	}
}

header("HTTP/1.0 404 Not Found");

