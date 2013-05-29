<?php
if (isset($_SERVER['CRON_ID']) === true || in_array($_SERVER['SERVER_ADDR'], array(
			'127.0.0.1'
		)) === false) {
	$env = 'PROD';
} else {
	$env = 'DEV';
}
