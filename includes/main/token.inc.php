<?php
if (isset($_NO_TOKEN) === false && isset($_SERVER['CRON_ID']) === false) {
	//	Generate new Token for use in forms
	$_SESSION['NEXT_TOKEN'] = uniqid(true);

	function setTokenOnShutdown() {
		//	Get new token for check against for values
		$_SESSION['TOKEN'] = (isset($_SESSION['NEXT_TOKEN']) === true ? $_SESSION['NEXT_TOKEN'] : null);
	}

	register_shutdown_function("setTokenOnShutdown");
}
if (isset($_SESSION['TOKEN']) === false) {
	$_SESSION['TOKEN'] = false;
}
