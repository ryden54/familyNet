<?php
$_NO_TOKEN = true;
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';

$id = Html::getRequest('id', false, Html::INTEGER);
$format = Html::getRequest('f', false, Html::TEXT);

if ($format !== false && in_array($format, array(
			'mini', 'medium'
		)) === false) {
	$format = false;
}

if ($id !== false) {

	$imgPath = Config::get('PHOTOS_PATH', 'PHOTOS') . $id . ($format === false ? '' : '-' . $format) . '.jpg';
	$fullImgPath = Config::get('PHOTOS_PATH', 'PHOTOS') . $id . '.jpg';

	if ($format !== false && file_exists($fullImgPath) === true && file_exists($imgPath) === false) {
		$p = new Photo($id);
		$dim = false;

		switch ($format)
			{
			case 'mini':
				$dim = Config::get('PHOTOS_MINI_DIMENSIONS', 'PHOTOS');
				break;
			case 'medium':
				$dim = Config::get('PHOTOS_MEDIUM_DIMENSIONS', 'PHOTOS');
				break;
			}
		if ($dim !== false) {
			$p->createScaledImage($imgPath, $dim[0], $dim[1]);
		}
	}

	if (file_exists($imgPath)) {
		header('Content-type: image/jpeg');
		session_cache_limiter('private');
		echo file_get_contents($imgPath, false);
		exit();
	}
}

header("HTTP/1.0 404 Not Found");
