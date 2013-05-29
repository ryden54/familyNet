<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';

if (isset($_SESSION['PHOTOS_UPLOAD_TMP_PATH']) === false) {
	$_SESSION['PHOTOS_UPLOAD_TMP_PATH'] = Config::get('TMP_PATH') . 'photos-upload-' . uniqid() . '/';
}

// $Toolkit_Dir = $_SERVER['DOCUMENT_ROOT'] . '/../libs/PHP_JPEG_Metadata_Toolkit_1.12/';

// include $Toolkit_Dir . 'Toolkit_Version.php'; // Change: added as of version 1.11
// include $Toolkit_Dir . 'JPEG.php'; // Change: Allow this example file to be easily relocatable - as of version 1.11
// include $Toolkit_Dir . 'JFIF.php';
// include $Toolkit_Dir . 'PictureInfo.php';
// include $Toolkit_Dir . 'XMP.php';
// include $Toolkit_Dir . 'Photoshop_IRB.php';
// include $Toolkit_Dir . 'EXIF.php';

/*
 * jQuery File Upload Plugin PHP Example 5.14
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

error_reporting(E_ALL | E_STRICT);
require $_SERVER['DOCUMENT_ROOT'] . '/../libs/jQuery-File-Upload/UploadHandler.php';
$upload_handler =
		new UploadHandler(
				array(
						'upload_dir' => $_SESSION['PHOTOS_UPLOAD_TMP_PATH'],
						'script_url' => $_SERVER['PHP_SELF'],
						'min_file_size' => 1024 * 300,
						'min_width' => 600,
						'min_height' => 600,
						'discard_aborted_uploads' => false,
						'accept_file_types' => '/\.(jpe?g)$/i',
						'orient_image' => true,
						'download_via_php' => true,
						'image_versions' => array(
								'resized' => array(
									'max_width' => 1920, 'max_height' => 1920, 'jpeg_quality' => 80,
								),
								'mini' => array(
									'max_width' => 160, 'max_height' => 160
								),
								'thumbnail' => array(
									'max_width' => 80, 'max_height' => 80
								)
						),
				));
