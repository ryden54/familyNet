<?php
$context = Context::getInstance();
$user = $context->getUser();
foreach ($context->getRequiredAuths() AS $a) {
	if ($user->hasAuth($a) === false) {
		header('HTTP/1.1 403 Forbidden - Missing right "' . $a . '"');
		exit();
	}
}
