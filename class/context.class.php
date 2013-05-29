<?php
class Context
{
	protected static $instance = null;

	protected $title;
	protected $subTitle = '';
	protected $showMenu = false;

	protected $user = null;
	protected $sql = null;
	protected $db = null;
	protected $debug = null;

	protected $hasNavBar = true;
	protected $light = false;

	protected $universe = false;

	protected $requiredAuths = array();

	protected $resources =
			array(
					Context_Resource::CSS => array(
						Context_Resource::FILE => array(), Context_Resource::INLINE => array()
					),
					Context_Resource::JS => array(
						Context_Resource::FILE => array(), Context_Resource::INLINE => array()
					),
			);

	function __construct() {
		$this->title = Config::get('SITE_TITLE');
		$this->initUniverse();
	}

	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = false;
			self::$instance = new Context();
		}
		return self::$instance;
	}

	public function getTitle() {
		return $this->title . (strlen($this->subTitle) > 0 ? ' - ' . $this->subTitle : '');
	}

	public function setSubTitle($t) {
		$this->subTitle = $t;
	}

	public function hasShowMenu() {
		return $this->showMenu;
	}

	public function getUser() {
		return $this->user;
	}

	public function loadUser() {
		if (isset($_SESSION['USER']) === true) {
			$this->user = unserialize($_SESSION['USER']);
		} elseif (isset($_COOKIE['USER']) === true) {
			$cookieUser = unserialize($_COOKIE['USER']);
			if ($cookieUser !== false && isset($cookieUser['id']) === true && isset($cookieUser['c']) === true) {

				$typeAutorisationQuery = 'SELECT MotDePasse FROM `autorisations` Where MD5(CONCAT(MotDePasse, ?, ?)) = ? Order By id Desc';

				$typeAutorisation =
						$this->getSql()
								->fetchOne(
										$typeAutorisationQuery,
										array(
											$cookieUser['id'], Config::get('KEY', 'MANAGEMENT'), $cookieUser['c']
										));

				if (is_array($typeAutorisation) === true) {
					try {
						$this->loginUser(false, $typeAutorisation['MotDePasse'], $cookieUser['id']);
					} catch (User_Exception $e) {
						//	Nothing to do specifically
					}
				}

				if (($this->getUser() instanceof User) === false) {
					$this->forgetUser();
				}
			}
		}
	}

	public function logoutUser() {
		if (isset($_SESSION['USER'])) {
			unset($_SESSION['USER']);
		}
		$this->forgetUser();
		$this->user = null;
	}

	public function loginUser($log, $pass, $id = false) {
		try {
			$this->user = new User($log, $pass, $id);
			$_SESSION['USER'] = serialize($this->user);
			return $this->user;
		} catch (Exception $e) {
			if ($e->getCode() === User_Exception::PASSWORD) {
				$this->setBlackList($this->getIp());
			}
			throw $e;
		}
	}

	protected function setBlackList($ip) {
		$table = $this->getDb()->autorisations_blacklist()->where(array(
							'ip' => ($ip)
						));

		$previousLock = $table->fetch();
		$tries = 1;
		if ($previousLock === false) {
			$this->getDb()->autorisations_blacklist()->insert(array(
						'ip' => ($ip), 'tries' => $tries, 'lastDate' => Db_Sql::getNowString()
					));
		} else {
			$tries = $previousLock['tries'] + 1;
			$this->getDb()->autorisations_blacklist()
					->insert_update(
							array(
								'ip' => ($ip)
							),
							array(
								'lastDate' => Db_Sql::getNowString(), 'tries' => $previousLock['tries'] + 1
							));
		}

		$lockFile = $this->getLockfilePath($ip);
		$lockDelay = min(pow(Config::get('LOCK_INTERVAL', 'MANAGEMENT'), $tries), 60 * 60 * 24 * 365 * 10);
		if (touch($lockFile, time() + $lockDelay) === false) {
			mvd("Failed to create lock file '" . $lockFile . "' for then next " . $lockDelay . " seconds");
		}
	}

	public function unsetBlacklist($ip) {
		$stmt = $this->getSql()->prepare("Delete FROM autorisations_blacklist WHERE ip = ?");
		$stmt->execute(array(
					$ip
				));
		$filePath = $this->getLockfilePath($ip);
		if (file_exists($filePath) === true) {
			unset($filePath);
		}
	}

	public function isBlackListed($ip = null) {
		if ($ip === null) {
			$ip = $this->getIp();
		}
		if ($ip !== false) {
			$filePath = $this->getLockfilePath($ip);
			if (file_exists($filePath) === true) {
				if (filemtime($filePath) > time()) {
					return filemtime($filePath) - time();
				} else {
					unlink($filePath);
				}
			}
		}
		return false;
	}

	protected function getLockfilePath($ip) {
		return $_SERVER['DOCUMENT_ROOT'] . '/../tmp/lock_' . md5($ip);
	}

	public function memorizeUser(User $u, $md5Pass) {
		if ($u instanceof User) {
			setcookie(
					'USER',
					serialize(array(
						'id' => $u->getId(), 'c' => md5($md5Pass . $u->getId() . Config::get('KEY', 'MANAGEMENT'))
					)),
					time() + 60 * 60 * 24 * 30);
		}
		return false;
	}

	public function forgetUser() {
		setcookie('USER', null);
	}

	public function getSql() {
		if ($this->sql === null) {
			$this->sql =
					new Db_Sql('mysql:host=' . Config::get('HOST', 'SQL') . ';dbname=' . Config::get('DATABASE', 'SQL'), Config::get('USER', 'SQL'),
							Config::get('PASS', 'SQL'), array(
								PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
							));
		}
		return $this->sql;
	}

	public function getDb() {
		if ($this->db === null) {
			$this->db = new Db($this->getSql());
		}
		return $this->db;
	}

	public function getDebug() {
		if ($this->debug === null) {
			$this->debug = new Debug();
		}
		return $this->debug;
	}

	public function getIp() {
		$ip = false;
		//Test if it is a shared client
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
			//Is it a proxy address
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

	public function addHtmlInline($inline, $priority = 'N') {
		$this->addResource(new Context_Resource(Context_Resource::HTML, Context_Resource::INLINE, $inline), $priority);
	}

	public function addCssFile($url, $priority = 'N') {
		$this->addResource(new Context_Resource(Context_Resource::CSS, Context_Resource::FILE, $url), $priority);
	}

	public function addCssInline($inline, $priority = 'N') {
		$this->addResource(new Context_Resource(Context_Resource::CSS, Context_Resource::INLINE, $inline), $priority);
	}

	public function addJsFile($url, $priority = 'N') {
		$this->addResource(new Context_Resource(Context_Resource::JS, Context_Resource::FILE, $url), $priority);
	}

	public function addJsInline($inline, $priority = 'N') {
		$this->addResource(new Context_Resource(Context_Resource::JS, Context_Resource::INLINE, $inline), $priority);
	}

	protected function addResource(Context_Resource $r, $priority = 'N') {
		$this->resources[$r->getNature()][$r->getType()][$priority][$r->getHash()] = $r;
	}

	public function injectResources($output) {
		$output = $this->injectResourcesCssFiles($output);
		$output = $this->injectResourcesCssInlines($output);
		$output = $this->injectResourcesHtmlInlines($output);
		$output = $this->injectResourcesJsFiles($output);
		$output = $this->injectResourcesJsInlines($output);
		return $output;
	}

	protected function injectResourcesCssFiles($output) {
		$resourcesString = '';
		ksort($this->resources[Context_Resource::CSS][Context_Resource::FILE]);
		foreach ($this->resources[Context_Resource::CSS][Context_Resource::FILE] As $group) {
			foreach ($group AS $r) {
				$resourcesString .= '<link href="' . $r->getContent() . '" rel="stylesheet"	media="screen">' . "\r\n";
			}
		}
		return str_replace(Config::get('CONTEXT_TAG_CSS_FILES', 'HTML'), $resourcesString, $output);
	}

	protected function injectResourcesCssInlines($output) {
		$resourcesString = '';
		ksort($this->resources[Context_Resource::CSS][Context_Resource::INLINE]);
		foreach ($this->resources[Context_Resource::CSS][Context_Resource::INLINE] As $group) {
			foreach ($group AS $r) {
				$resourcesString .= '<style>' . $r->getContent() . '</style>' . "\r\n";
			}
		}
		return str_replace(Config::get('CONTEXT_TAG_CSS_INLINE', 'HTML'), $resourcesString, $output);
	}

	protected function injectResourcesHtmlInlines($output) {
		$resourcesString = '';
		ksort($this->resources[Context_Resource::HTML][Context_Resource::INLINE]);
		foreach ($this->resources[Context_Resource::HTML][Context_Resource::INLINE] As $group) {
			foreach ($group AS $r) {
				$resourcesString .= $r->getContent() . "\r\n";
			}
		}
		return str_replace(Config::get('CONTEXT_TAG_HTML_INLINE', 'HTML'), $resourcesString, $output);
	}

	protected function injectResourcesJsFiles($output) {
		$resourcesString = '';
		ksort($this->resources[Context_Resource::JS][Context_Resource::FILE]);
		foreach ($this->resources[Context_Resource::JS][Context_Resource::FILE] As $group) {
			foreach ($group AS $r) {
				$resourcesString .= '<script type="text/javascript" src="' . $r->getContent() . '"></script>' . "\r\n";
			}
		}
		return str_replace(Config::get('CONTEXT_TAG_JS_FILES', 'HTML'), $resourcesString, $output);
	}

	protected function injectResourcesJsInlines($output) {
		$resourcesString = '';
		ksort($this->resources[Context_Resource::JS][Context_Resource::INLINE]);
		foreach ($this->resources[Context_Resource::JS][Context_Resource::INLINE] As $group) {
			foreach ($group AS $r) {
				$resourcesString .=
						'<script type="text/javascript">
					$(function() {
						try {
						' . $r->getContent() . '
						}
						catch(e) {
							console.log("Caught error", e);
						}
					});
</script>' . "\r\n";
			}
		}
		return str_replace(Config::get('CONTEXT_TAG_JS_INLINE', 'HTML'), $resourcesString, $output);
	}

	public function initUniverse() {
		if (isset($_SERVER['REQUEST_URI']) === true) {
			if (stripos($_SERVER['REQUEST_URI'], '/famille/') === 0) {
				$this->universe = Context_Universe::FAMILLE;
			} elseif (stripos($_SERVER['REQUEST_URI'], '/photos/') === 0) {
				$this->universe = Context_Universe::PHOTOS;
			} elseif (stripos($_SERVER['REQUEST_URI'], '/discussions/') === 0) {
				$this->universe = Context_Universe::DISCUSSIONS;
			}
		}
	}

	public function getUniverse() {
		return $this->universe;
	}

	public function isLight() {
		return $this->light;
	}

	public function setLight($l = true) {
		$this->light = $l;
		if ($l === true) {
			$this->setHasNavBar(false);
		}
	}

	public function getHasNavbar() {
		return $this->hasNavBar;
	}

	public function setHasNavBar($b) {
		$this->hasNavBar = $b;
	}

	public function addRequiredAuth($a) {
		$this->requiredAuths[md5($a)] = $a;
	}

	public function getRequiredAuths() {
		return $this->requiredAuths;
	}
}
