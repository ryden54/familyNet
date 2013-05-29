<?php
$return_code = 0;
$_NO_USER_NEEDED = true;
$_NO_TOKEN = true;
require_once dirname(__FILE__) . '/../includes/main.inc.php';

$photoDuJour = $context->getDb()->photos_du_jour()->where('Jour', Db_Sql::getNowString(false))->fetch();

if ($photoDuJour === false) {
	$newPdj =
			$context->getSql()
					->fetchOne(
							'Select photos.id From photos Left Outer Join photos_du_jour On(photos.id = photos_du_jour.photos_id) Where photos_du_jour.id Is Null Order By Rand() Limit 0, 1');

	if ($newPdj !== false) {
		$newPdj =
				$context->getSql()
						->fetchOne(
								'Select photos.id, Max(Jour) AS lastJour From photos Inner Join photos_du_jour On(photos.id = photos_du_jour.photos_id) Group By photos.id Order By lastJour asc, photos.id Limit 0, 1');
	}

	if ($newPdj !== false) {
		$insert =
				$context->getDb()->photos_du_jour()
						->insert(array(
							'photos_id' => $newPdj['id'], 'Jour' => Db_Sql::getNowString(false)
						));
		if ($insert !== false) {
			echo 'Photo du ' . Db_Sql::getNowString(false) . ' : ' . $newPdj['id'];
		} else {
			echo 'Echec de la saisie de la photo du ' . Db_Sql::getNowString(false) . ' : ' . $newPdj['id'];
			$return_code = 1;
		}
	} else {
		echo 'Impossible de determiner la nouvelle photo du jour';
		$return_code = 1;

	}
} else {
	echo 'Deja une photo du jour : ' . $photoDuJour['id'];
	$return_code = 1;
}

exit($return_code);
