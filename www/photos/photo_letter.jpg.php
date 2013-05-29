<?php
$_NO_TOKEN = true;
$_NO_USER_NEEDED = true;
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';

$id = false;
$format = 'mini';

$hash = Html::getRequest('hash', false, Html::TEXT);
$newsletter_id = Html::getRequest('letter_id', false, Html::INTEGER);

if ($hash !== false && $newsletter_id !== false) {
	$image =
			Context::getInstance()->getDb()->newsletters_images(array(
						'hash' => $hash, 'newsletters_id' => $newsletter_id
					))->select('*, newsletters.readDate')->fetch();
	if (is_object($image) === true) {
		if (isset($_SERVER["HTTP_REFERER"]) == false || strpos($_SERVER["HTTP_REFERER"], '/gestion/newsletter') === false) {
			if ($image['readDate'] === null) {
				$image->newsletters['readDate'] = Db_Sql::getNowString();
				$image->newsletters->update();
			}
		}
		$id = $image['photos_id'];
	}
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
