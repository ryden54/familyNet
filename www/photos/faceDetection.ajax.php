<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/../libs/PHP-FaceDetector/FaceDetector.php';

$id = Html::getRequestOrPost('id', false, HTML::INTEGER);

$detector = new FaceDetector();
$detector->scan(Config::get('PHOTOS_PATH', 'PHOTOS') . $id . "-mini.jpg");
$faces = $detector->getFaces();
foreach ($faces as $face) {
	echo "Face found at x: {$face['x']}, y: {$face['y']}, width: {$face['width']}, height: {$face['height']}<br />\n";
}
