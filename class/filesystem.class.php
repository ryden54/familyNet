<?php
class Filesystem
{
	public function unlink_dir($path) {
		$path = realpath($path);
		foreach (scandir($path) As $folderEntry) {
			if (in_array($folderEntry, array(
				'.', '..'
			)) === false) {
				$folderEntry = realpath($path . DIRECTORY_SEPARATOR . $folderEntry);
				if (strpos($folderEntry, $path) !== 0) {
					mvd("don't go outside of initial folder");
				} elseif (is_dir($folderEntry) === true) {
					$this->unlink_dir($folderEntry);
				} else {
					unlink($folderEntry);
				}

			}
		}
		return rmdir($path);
	}

	public function unlink_file($path) {
		if (file_exists($path) === true) {
			return unlink($path);
		}
		return false;
	}
}
