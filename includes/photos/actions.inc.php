<?php
$fs = new Filesystem();
switch (Html::getRequestOrPost('action', false, HTML::TEXT))
	{
	case 'cancel':
		if (isset($_SESSION['PHOTOS_UPLOAD_TMP_PATH']) === true && file_exists($_SESSION['PHOTOS_UPLOAD_TMP_PATH']) === true) {
			$fs->unlink_dir($_SESSION['PHOTOS_UPLOAD_TMP_PATH']);
		}
		unset($_SESSION['PHOTOS_UPLOAD_TMP_PATH']);
		header('Location: /photos/');
		exit();
		break;
	case 'share':
		if (isset($_SESSION['PHOTOS_UPLOAD_TMP_PATH']) === true && file_exists($_SESSION['PHOTOS_UPLOAD_TMP_PATH']) === true) {
			$uploadedPhotos = array();
			foreach (scandir($_SESSION['PHOTOS_UPLOAD_TMP_PATH']) As $entry) {
				$f = $_SESSION['PHOTOS_UPLOAD_TMP_PATH'] . DIRECTORY_SEPARATOR . $entry;
				if (is_file($f) === true) {
					$is = getimagesize($f);
					if ($is !== false && $is['mime'] === 'image/jpeg') {
						$exif = read_exif_data($f, 'EXIF', true);

						$photoDb =
								array(
									'DateUpload' => Db_Sql::getNowString(), 'personnes_id' => $context->getUser()->getId(),
								);

						// 						mvd($exif);

						if (isset($exif['EXIF']['DateTimeOriginal']) === true) {

							// 							mvd($exif['EXIF']['DateTimeOriginal']);
							$date = DateTime::createFromFormat('Y:m:d H:i:s', $exif['EXIF']['DateTimeOriginal']);
							if ($date instanceof DateTime) {
								$photoDb['DateCliche'] = utf8_encode(strftime('%Y-%m-%d', intval($date->format('U'))));
							}
						}

						if (preg_match('/<digit>/', $entry) > 0) {
							$photoDb['Titre'] = $entry;
						}

						if (isset($exif['GPS']) === true && isset($exif['GPS']['GPSLatitude']) === true
								&& isset($exif['GPS']['GPSLongitude']) === true) {

							try {
								$geo = new Geo($exif['GPS']);
								$photoDb['Lieu'] = $geo->getLabel();
							} catch (Exception $e) {

							}
						}
						$photos_id = $context->getDb()->photos()->insert($photoDb);

						$uploadedPhotos[] = $photos_id;

						$fs->unlink_file($f);
						rename(
								$_SESSION['PHOTOS_UPLOAD_TMP_PATH'] . DIRECTORY_SEPARATOR . 'resized' . DIRECTORY_SEPARATOR . $entry,
								Config::get('PHOTOS_PATH', 'PHOTOS') . $photos_id . '.jpg');
						rename(
								$_SESSION['PHOTOS_UPLOAD_TMP_PATH'] . DIRECTORY_SEPARATOR . 'mini' . DIRECTORY_SEPARATOR . $entry,
								Config::get('PHOTOS_PATH', 'PHOTOS') . $photos_id . '-mini.jpg');
					}
				}
			}
			$fs->unlink_dir($_SESSION['PHOTOS_UPLOAD_TMP_PATH']);
			unset($_SESSION['PHOTOS_UPLOAD_TMP_PATH']);

			$url = '/photos/photo.edit.php?';
			$params = array();
			foreach ($uploadedPhotos As $key => $val) {
				$params[] = 'id[' . $key . ']=' . $val;
			}

			header('Location: ' . $url . implode('&', $params));
			exit();
		}

		break;
	case 'delete':
		$id = Html::getRequestOrPost('id', false, HTML::INTEGER);
		if ($id !== false) {
			$p = $context->getDb()->photos[$id];
			if ($p['personnes_id'] == $context->getUser()->getId() || $context->getUser()->hasAuth('Gestion') === true) {
				foreach ($p->photos_presences() As $presence) {
					if ($presence['Portrait'] != '0') {
						//	Deleting photo files
						$fs->unlink_file(Config::get('PORTRAITS_PATH', 'PHOTOS') . $presence['id'] . '.jpg');
						$fs->unlink_file(Config::get('PORTRAITS_PATH', 'PHOTOS') . $presence['id'] . '-mini.jpg');
					}
					$presence->delete();
				}

				//	Deleting photo files
				$fs->unlink_file(Config::get('PHOTOS_PATH', 'PHOTOS') . $id . '.jpg');
				$fs->unlink_file(Config::get('PHOTOS_PATH', 'PHOTOS') . $id . '-mini.jpg');

				//	Removing entry
				$p->delete();
			}
		}
		break;

	case 'addPresence':
		$id = Html::getRequestOrPost('id', false, HTML::INTEGER);
		$personnes_id = Html::getRequestOrPost('personnes_id', false, HTML::INTEGER);
		$coords = Html::getRequestOrPost('coords', false, HTML::ANY);

		$photo = $context->getDb()->photos[$id];
		$personne = $context->getDb()->personnes[$personnes_id];

		if ($photo !== null && $personne !== null) {
			$pres =
					$context->getDb()->photos_presences()
							->insert(array(
								'photos_id' => $id, 'personnes_id' => $personnes_id, 'Portrait' => 0
							));
			if ($pres !== null) {
				$pres = $context->getDb()->photos_presences[$pres['id']];

				$drawing = Html::getRequestOrPost('drawing', false, HTML::FLOAT);
				if ($drawing !== false) {
					$photo = new Photo($pres['photos_id']);
					if ($photo->setPortrait($pres['id'], $drawing) === true) {
						$pres['Portrait'] = 1;
						$pres->update();
					}
				}

				include $_SERVER['DOCUMENT_ROOT'] . '/../includes/photos/presence.inc.php';
			}
			exit;
		}

		break;

	case 'removePresence':
		$id = Html::getRequestOrPost('id', false, HTML::INTEGER);
		$personnes_id = Html::getRequestOrPost('personnes_id', false, HTML::INTEGER);
		$photos_id = Html::getRequestOrPost('photos_id', false, HTML::ANY);

		$p = $context->getDb()->photos_presences[$id];

		if ($p !== null) {
			if ($p['Portrait'] != '0') {
				//	Deleting photo files
				$fs->unlink_file(Config::get('PORTRAITS_PATH', 'PHOTOS') . $p['id'] . '.jpg');
				$fs->unlink_file(Config::get('PORTRAITS_PATH', 'PHOTOS') . $p['id'] . '-mini.jpg');
			}

			$p->delete();
		}
		exit;

		break;

	case 'update':
		$photos = Html::getPost('photos', false, HTML::ANY);
		foreach ($photos As $id => $photo) {
			$p = $context->getDb()->photos[$id];
			foreach ($photo As $key => $val) {
				if (in_array($key, array(
					'Titre', 'Lieu', 'DateCliche', 'Commentaire'
				)) === true) {
					if (strlen($val) == 0) {
						$val = null;
					}
					$p[$key] = $val;
				}
			}
			$p->update();
		}
		header('Location: /photos/');
		exit();
		break;

	case 'tracePortrait':
		$id = Html::getRequestOrPost('id', false, HTML::INTEGER);
		$drawing = Html::getRequestOrPost('drawing', false, HTML::FLOAT);

		$pres = $context->getDb()->photos_presences[$id];

		if ($pres !== null) {

			if ($pres['Portrait'] == '0') {
				$pres['Portrait'] = 1;
				$pres->update();
			}

			$photo = new Photo($pres['photos_id']);
			$photo->setPortrait($id, $drawing);

			$pres = $context->getDb()->photos_presences[$pres['id']];
			include $_SERVER['DOCUMENT_ROOT'] . '/../includes/photos/presence.inc.php';
		}

		break;
	}

