<?php
class Html
{

	const ANY = 0;
	const INTEGER = 1;
	const FLOAT = 2;
	const BOOLEAN = 3;
	const TEXT = 4;
	const HTML = 5;

	public static function getRequest($field, $default, $type = self::ANY) {
		try {
			return self::getField($_GET, $field, $type);
		} catch (Exception $e) {
			return $default;
		}
	}

	public static function getPost($field, $default, $type = self::ANY) {
		try {
			return self::getField($_POST, $field, $type);
		} catch (Exception $e) {
			return $default;
		}
	}

	public static function getRequestOrPost($field, $default, $type = self::ANY) {
		try {
			return self::getField(array_merge_recursive($_POST, $_GET), $field, $type);
		} catch (Exception $e) {
			return $default;
		}
	}

	protected static function getField($tab, $field, $type) {

		$fieldTab = explode('[', str_replace(']', '', $field));

		foreach ($fieldTab As $f) {
			if (isset($tab[$f]) === false) {
				throw new Exception('Field ' . $field . ' not present in tab ' . var_export($tab, true));
			}
			$tab = &$tab[$f];
		}

		if (isset($tab) === true) {
			return self::filter($tab, $type);
		}
		throw new Exception('Field ' . $field . ' not present in tab');
	}

	protected static function filter($val, $type) {
		if (is_array($val) === true) {
			foreach ($val AS $k => $v) {
				$val[$k] = self::filter($v, $type);
			}
		} else {
			switch ($type)
				{
				case self::TEXT:
					$val = strip_tags($val);
					break;

				case self::FLOAT:
					$val = floatval($val);
					break;

				case self::BOOLEAN:
					$val = ($val ? true : false);
					break;

				case self::INTEGER:
					$val = intval($val);
					break;

				case self::HTML:
					$val = str_replace(array(
								'<script', '<style', '<link'
							), '<x', $val);
					break;
				case self::ANY:
				default:
				//	No filtering
					break;
				}
		}
		return $val;

	}

	public static function escape($str) {
		return htmlentities($str);
	}
}
