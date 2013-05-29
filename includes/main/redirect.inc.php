<?php
//	Redirect if wrong host & not cron
if (isset($_SERVER['HTTP_HOST']) === true && isset($_SERVER['REQUEST_URI']) === true && $_SERVER['HTTP_HOST'] !== Config::getHost()) {
	header('Location: http' . ($_SERVER['SERVER_PORT'] == 443 ? 's' : '') . '://' . Config::getHost() . $_SERVER['REQUEST_URI'], true, 301);
	exit();
}

if (Config::get('MAINTENANCE', 'MANAGEMENT') === true) {
	include($_SERVER['DOCUMENT_ROOT'] . '/../includes/maintenance.inc.php');
	exit();
}
