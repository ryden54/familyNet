<html>
<head>
<meta charset="utf-8">
</head>
<body>
	<?php
$_NO_USER_NEEDED = true;
ob_start();
include '../cron/sendNewsletter.php';
echo ob_get_clean();

include $_SERVER['DOCUMENT_ROOT'] . '/../includes/end.inc.php';
