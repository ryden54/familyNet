<?php
class Photo
{
	protected $id;
	protected $path;

	public function __construct($id) {
		$this->id = $id;
		$this->path = Config::get('PHOTOS_PATH', 'PHOTOS') . $this->id . ".jpg";
	}

	protected function getScaledDimensions($params) {
		if (isset($params['totalW']) === true) {
			$dim = getimagesize($this->path);
			$ratio = $dim[0] / $params['totalW'];

			foreach (array(
				'x', 'y', 'w', 'h'
			) As $k) {
				if (isset($params[$k]) === true) {
					$params[$k] *= $ratio;
				}
			}
		}
		return $params;
	}

	function setPortrait($presence_id, $params) {
		$params = $this->getScaledDimensions($params);
		$resultat = true;

		$cheminFichierPortrait = Config::get('PORTRAITS_PATH', 'PHOTOS') . $presence_id . ".jpg";
		$cheminFichierPortraitMini = Config::get('PORTRAITS_PATH', 'PHOTOS') . $presence_id . "-mini.jpg";

		$dimensions['x'] = round(max(2, $params['w']));
		$dimensions['y'] = round(max(2, $params['h']));

		$debut['x'] = round(max(0, $params['x']));
		$debut['y'] = round(max(0, $params['y']));

		$imageSource = imagecreatefromjpeg($this->path);
		$dimensionsOriginelles = getImageSize($this->path);

		if ($dimensions['x'] < 5 || $dimensions['y'] < 5) {
			$debut['x'] = 0;
			$debut['y'] = 0;
			$dimensions['x'] = $dimensionsOriginelles['0'];
			$dimensions['y'] = $dimensionsOriginelles['1'];
		}

		$maxDimPortrait = Config::get('PORTRAITS_DIMENSIONS', 'PHOTOS');
		$maxDimPortraitMini = Config::get('PORTRAITS_MINI_DIMENSIONS', 'PHOTOS');

		$ratioPortrait = min($maxDimPortrait[0] / $dimensions['x'], $maxDimPortrait[1] / $dimensions['y']);
		$ratioPortraitMini = min($maxDimPortraitMini[0] / $dimensions['x'], $maxDimPortraitMini[1] / $dimensions['y']);

		$dimensionsPortrait['x'] = round($dimensions['x'] * $ratioPortrait);
		$dimensionsPortrait['y'] = round($dimensions['y'] * $ratioPortrait);

		$dimensionsPortraitMini['x'] = round($dimensions['x'] * $ratioPortraitMini);
		$dimensionsPortraitMini['y'] = round($dimensions['y'] * $ratioPortraitMini);

		$imagePortrait = imagecreatetruecolor($dimensionsPortrait['x'], $dimensionsPortrait['y']);
		$imagePortraitMini = imagecreatetruecolor($dimensionsPortraitMini['x'], $dimensionsPortraitMini['y']);

		imagecopyresized(
				$imagePortrait,
				$imageSource,
				0,
				0,
				$debut['x'],
				$debut['y'],
				$dimensionsPortrait['x'],
				$dimensionsPortrait['y'],
				$dimensions['x'],
				$dimensions['y']);

		imagecopyresized(
				$imagePortraitMini,
				$imageSource,
				0,
				0,
				$debut['x'],
				$debut['y'],
				$dimensionsPortraitMini['x'],
				$dimensionsPortraitMini['y'],
				$dimensions['x'],
				$dimensions['y']);

		$resultat = $resultat && imagejpeg($imagePortrait, $cheminFichierPortrait, 75);
		$resultat = $resultat && imagejpeg($imagePortraitMini, $cheminFichierPortraitMini, 75);

		//On libère la mémoire
		imagedestroy($imageSource);
		imagedestroy($imagePortrait);
		imagedestroy($imagePortraitMini);

		return $resultat;
	}

	public function createScaledImage($destPath, $destMaxWidth, $destMaxHeight) {
		list($img_width, $img_height) = @getimagesize($this->path);
		if (!$img_width || !$img_height) {
			return false;
		}


		$scale = min($destMaxWidth / $img_width, $destMaxHeight / $img_height);

		if ($scale >= 1) {
			return copy($this->path, $destPath);
			return true;
		}
		$new_width = $img_width * $scale;
		$new_height = $img_height * $scale;
		$new_img = @imagecreatetruecolor($new_width, $new_height);

		$src_img = @imagecreatefromjpeg($this->path);
		$image_quality = 75;

		$success =
				$src_img && @imagecopyresampled($new_img, $src_img, 0, 0, 0, 0, $new_width, $new_height, $img_width, $img_height)
						&& imagejpeg($new_img, $destPath, $image_quality);
		// Free up memory (imagedestroy does not delete files):
		@imagedestroy($src_img);
		@imagedestroy($new_img);
		return $success;
	}
}
