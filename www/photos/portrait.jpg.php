<?php
$_NO_TOKEN = true;
session_cache_limiter('private_no_cache');
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';

$id = Html::getRequest('id', false, Html::INTEGER);
$format = Html::getRequest('f', false, Html::TEXT);

$personnes_id = Html::getRequest('personnes_id', false, Html::INTEGER);

if ($personnes_id !== false) {
	if (isset($_SESSION['portraits']) === false || isset($_SESSION['portraits'][$personnes_id]) === false) {
		if (isset($_SESSION['portraits']) === false) {
			$_SESSION['portraits'] = array(
						-1
					);
		}

		$RSQL =
				'select id as personnes_id, Sexe, (SELECT id FROM `photos_presences` WHERE personnes_id = personnes.id AND Portrait = 1 ORDER BY RAND() Limit 1 ) as id From personnes WHERE id NOT IN ('
						. implode(',', array_keys($_SESSION['portraits'])) . ')';
		foreach ($context->getSql()->fetchAll($RSQL) As $p) {

			if ($p['id'] !== null) {
				$_SESSION['portraits'][$p['personnes_id']] = $p['id'];
			} else {
				$_SESSION['portraits'][$p['personnes_id']] = '0.' . strtolower($p['Sexe']);
			}
		}

	}

	$id = $_SESSION['portraits'][$personnes_id];
}

if ($id !== false) {
	$imgPath = Config::get('PORTRAITS_PATH', 'PHOTOS') . $id . ($format === 'mini' ? '-mini' : '') . '.jpg';

	header('Content-type: image/jpeg');

	if (file_exists($imgPath)) {
		// 				header_remove();
		// 		mvd(headers_list());
		// 		die();
		// 		header("Cache-Control: no-store, cache, must-revalidate, post-check=0, pre-check=0");
		// 		header("Expires: ");
		// 		header("Pragma: ");
		echo file_get_contents($imgPath, false);
	} else {
		echo file_get_contents(Config::get('PORTRAITS_PATH', 'PHOTOS') . '0.m.jpg', false);
	}
	exit();
}

header("HTTP/1.0 404 Not Found");
