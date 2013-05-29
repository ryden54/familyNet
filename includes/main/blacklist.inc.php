<?php
if (($delay = Context::getInstance()->isBlackListed()) !== false) {
	echo '<html>
	<head><meta http-equiv="refresh" content="5"></head>
	<body>';
	displayError(
			"Accès verrouillé",
			"Des tentatives d'accès non autorisé ont été détectées, votre accès au site est bloqué pendant " . $delay . " seconde(s), soit jusqu'au "
					. date('d/m/Y H:i:s', time() + $delay),
			'',
			true);
}
