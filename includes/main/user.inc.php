<?php
$context->loadUser();
if (isset($_SERVER['CRON_ID']) === false) {
	if (isset($_NO_USER_NEEDED) === false) {
		if (Html::getRequest('logout', false, Html::BOOLEAN) === true) {
			$context->logoutUser();
			header('Location: /', true);
			exit;
		}

		if (($context->getUser() instanceof User) === false) {
			header(
					'Location: ' . Config::get('LOGIN', 'PAGES')
							. (isset($_SERVER['REQUEST_URI']) === true && $_SERVER['REQUEST_URI'] !== '/' ? '?org=' . urlencode($_SERVER['REQUEST_URI']) : '')
							. '#notconnected',
					true);
		}
	}
}
