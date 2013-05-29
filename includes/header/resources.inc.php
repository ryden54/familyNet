<?php

$context->addCssFile('/static/libs/bootstrap/css/bootstrap.css', 'F');
$context->addCssFile('/static/libs/bootstrap/css/bootstrap-responsive.css', 'F');
$context->addCssFile('/static/css/master.css', 'G');
$context->addCssFile('/static/css/bootstrap-theme.css', 'G');
$context->addJsFile("http://code.jquery.com/jquery-latest.js", 'F');
$context->addJsFile("/static/libs/bootstrap/js/bootstrap.js", 'F');

$context
		->addHtmlInline(
				'<!-- Bootstrap CSS fixes for IE6 -->
<!--[if lt IE 7]><link rel="stylesheet" href="/static/libs/bootstrap/css/bootstrap-ie6.min.css"><![endif]-->');

// $context->addCssFile('/static/libs/bootstrap-combobox/css/bootstrap-combobox.css', 'F');
// $context->addJsFile('/static/libs/bootstrap-combobox/js/bootstrap-combobox.js', 'F');

$context->addCssFile('/static/libs/nicolasbize-magicsuggest/bin/magicsuggest-1.3.0-min.css', 'F');
$context->addJsFile('/static/libs/nicolasbize-magicsuggest/bin/magicsuggest-1.3.0-min.js', 'F');

$context
		->addHtmlInline(
				'<!-- Force latest IE rendering engine or ChromeFrame if installed -->
<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->');

if (isset($_NO_USER_NEEDED) === false) {
	$context->addJsFile("/static/js/ga.js", 'Z');
}

//	Start output buffering
ob_start(array(
	$context, "injectResources"
));
