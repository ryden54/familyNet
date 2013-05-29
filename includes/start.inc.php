<?php
include $_SERVER['DOCUMENT_ROOT'] . '/../includes/header/redirect.inc.php';

if ($context->isLight() === false) {
	include $_SERVER['DOCUMENT_ROOT'] . '/../includes/header/resources.inc.php';
}
include $_SERVER['DOCUMENT_ROOT'] . '/../includes/header/head.inc.php';
if ($context->getHasNavbar() === true) {
	include $_SERVER['DOCUMENT_ROOT'] . '/../includes/header/navbar.inc.php';
}
