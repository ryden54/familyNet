<?php
class Config
{

	protected static $openedModules = array();

	protected static $data = array();

	static protected $host = null;
	static protected $envLvl = null;

	public static function set($name, $value, $module = false) {
		if ($value !== self::get($name, $module)) {
			self::$data[$module][$name] = $value;
		}
	}

	public static function get($name, $module = false) {
		if (!in_array($module, self::$openedModules)) {
			self::loadModule($module);
		}

		if (isset(self::$data[$module][$name]) === false) {
			throw new Exception("Config value '" . $name . "' doesn't exist in module '" . $module . "'");
		}
		return self::$data[$module][$name];
	}

	protected static function loadModule($module) {
		self::$openedModules[] = $module;
		self::$data[$module] = array();

		$rootConfigPath = $_SERVER['DOCUMENT_ROOT'] . '/../config/';
		$path = '';
		if ($module !== false) {
			foreach (explode('|', $module) as $m) {
				$path .= strtolower($m) . '/';
			}
		}

		if (is_dir($rootConfigPath . 'generic/' . $path) === false) {
			throw new Exception("Config module " . $module . " doesn't exist");
		}

		self::$data[$module] = array_merge(self::$data[$module], self::loadFile($rootConfigPath . 'generic/' . $path . 'global.conf.php'));
		self::$data[$module] = array_merge(self::$data[$module], self::loadFile($rootConfigPath . 'site/' . $path . 'global.conf.php'));

		if (self::getEnvLevel() !== false) {
			self::$data[$module] =
					array_merge(self::$data[$module], self::loadFile($rootConfigPath . 'generic/' . $path . self::getEnvLevel() . '.conf.php'));
			self::$data[$module] = array_merge(self::$data[$module], self::loadFile($rootConfigPath . 'site/' . $path . self::getEnvLevel() . '.conf.php'));
		}

		if (self::isCron() === true) {
			self::$data[$module] = array_merge(self::$data[$module], self::loadFile($rootConfigPath . 'generic/' . $path . 'cron' . '.conf.php'));
			self::$data[$module] = array_merge(self::$data[$module], self::loadFile($rootConfigPath . 'site/' . $path . 'cron' . '.conf.php'));
		}

	}

	protected static function loadFile($path) {
		$config = array();
		if (file_exists($path) === true) {
			include $path;
			return $config;
		}
		return array();
	}

	public static function isCron() {
		return (isset($_SERVER['CRON_ID']) === true);
	}

	public static function getEnvLevel() {
		if (self::$envLvl === null) {
			self::$envLvl = false;
			//	host.conf can override host
			$envFile = $_SERVER['DOCUMENT_ROOT'] . '/../config/env.conf.php';
			if (file_exists($envFile) === true) {
				$env = null;
				include $envFile;
				if ($env !== null) {
					self::$envLvl = strtolower(trim($env));
				} else {
					die('No environment level specified in ' . $envFile . ' ! ');
				}
			} else {
				die('No environment level file found : ' . $envFile . ' ! ');
			}
		}
		return self::$envLvl;
	}

	public static function getHost() {
		if (self::$host === null) {
			self::$host = false;
			if (isset($_SERVER['SERVER_NAME']) === true) {
				//	Default host is server name
				self::$host = $_SERVER['SERVER_NAME'];
			}

			//	host.conf can override host
			$hostFile = $_SERVER['DOCUMENT_ROOT'] . '/../config/env.conf.php';
			if (file_exists($hostFile) === true) {
				$host = null;
				include $hostFile;
				if ($host !== null) {
					self::$host = $host;
				}
			}
		}
		return self::$host;
	}
}
