<!DOCTYPE html>
<html>
<head>
<title><?=Context::getInstance()->getTitle() ?></title>
<meta name="author" content="<?=Config::get('SITE_AUTHOR'); ?>">
<meta http-equiv="Content-language" content="fr">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="utf-8">
<?php
echo Config::get('CONTEXT_TAG_CSS_INLINE', 'HTML');
echo Config::get('CONTEXT_TAG_HTML_INLINE', 'HTML');
echo Config::get('CONTEXT_TAG_CSS_FILES', 'HTML');
?>
</head>
<body class="container-fluid">