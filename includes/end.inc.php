<?php
include $_SERVER['DOCUMENT_ROOT'] . '/../includes/footer/foot.inc.php';
if (Config::get('DEBUG') === true && isset($_SERVER['CRON_ID']) === false) {
	include $_SERVER['DOCUMENT_ROOT'] . '/../includes/footer/debug.inc.php';
}
