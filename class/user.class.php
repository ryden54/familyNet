<?php
class User
{
	protected $id = false;
	protected $datas = array();
	protected $IdTypeAutorisation = false;
	protected $autorisations = array();
	protected $lastVisitDate = false;

	public function __construct($log, $pass, $id = false) {
		$this->id = $id;
		$this->datas = array(
					'log' => $log, 'pass' => $pass
				);

		$typeAutorisationQuery = 'SELECT * FROM `autorisations` Where MotDePasse = ? Order By id Desc';

		$typeAutorisation = Context::getInstance()->getSql()->fetchOne($typeAutorisationQuery, array(
							$this->datas['pass']
						));
		if ($typeAutorisation !== false) {
			$this->IdTypeAutorisation = $typeAutorisation['id'];
			$this->autorisations[$typeAutorisation['label']] = true;

			$ids = $this->getMatchingIds($typeAutorisation['id'], $log, $this->id);

			if (sizeof($ids) >= 1) {
				$this->id = $ids[0]['id'];
				$this->datas = array_merge($this->datas, $ids[0]);
				$lastLog =
						Context::getInstance()->getDb()->stats_logs()->select('Max(DateLog) As LastDateTimeLog')->where('personnes_id', $this->id)
								->where('DateLog < CURDATE()')->fetch();

				if (is_object($lastLog) == true) {
					$this->lastVisitDate = $lastLog['LastDateTimeLog'];
				}

				$this->logLogin();

			} else {
				throw new User_Exception("Aucune identité correspondante", User_Exception::IDENTITY);
			}
		} else {
			throw new User_Exception("Aucune autorisation correspondante", User_Exception::PASSWORD);
		}
	}

	public function __sleep() {
		return array(
			'id', 'datas', 'IdTypeAutorisation', 'autorisations', 'lastVisitDate'
		);
	}

	function getMatchingIds($IdTypeAutorisation, $log, $id = false) {
		$ids = array();

		if ($id !== false) {
			$identiteesQuery = 'Select personnes.* From personnes Where personnes.DateMort Is Null AND personnes.outOfFamily = 0 And personnes.id = ?';

			if ($identite = Context::getInstance()->getSql()->fetchOne($identiteesQuery, array(
								$id
							))) {
				$ids[] = $identite;
			}
		} else {
			$identifiants = explode(" ", trim(str_replace(array(
						"-", "'", "_", "\"", "+", ",", ";", ".", "%", "?", "*"
					), " ", $log)));

			if (sizeof($identifiants) > 1) {

				$identiteesQuery = 'Select personnes.* From personnes Where personnes.DateMort Is Null AND personnes.outOfFamily = 0 And ( TRUE ';
				$binds = array();

				foreach ($identifiants AS $i) {
					$identiteesQuery .= ' AND Concat(Concat(IF(ISNULL(NomJF), "", NomJF), Nom), Prenom) Like ?';
					$binds[] = '%' . $i . '%';
				}

				$identiteesQuery .= ' ) AND ( FALSE';

				foreach ($identifiants AS $i) {
					$identiteesQuery .= ' OR Concat(IF(ISNULL(NomJF), "", NomJF), Nom) Like ?';
					$binds[] = '%' . $i . '%';
				}

				$identiteesQuery .= ' ) AND ( FALSE';

				foreach ($identifiants AS $i) {
					$identiteesQuery .= ' OR Prenom Like ?';
					$binds[] = '%' . $i . '%';
				}
				$identiteesQuery .= ' )';

				$ids = Context::getInstance()->getSql()->fetchAll($identiteesQuery, $binds);
			}
		}
		return $ids;
	}

	public function getId() {
		return $this->id;
	}

	public function getDatas() {
		return array_merge(array(
			'id' => $this->id
		), $this->datas);
	}

	public function hasAuth($a, $correctVal = true) {
		if (isset($this->autorisations[$a]) === true && $this->autorisations[$a] === $correctVal) {
			return true;
		}
		return false;
	}

	protected function logLogin() {
		Context::getInstance()->getDb()->stats_logs()
				->insert(
						array(
								'personnes_id' => $this->id,
								'DateLog' => Db_Sql::getNowString(),
								'DateDernier' => Db_Sql::getNowString(),
								'SessionId' => session_id(),
								'Ip' => $_SERVER['REMOTE_ADDR'],
								'Hote' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
						));
	}

	public function getLastVisitDate() {
		return $this->lastVisitDate;
	}

	protected static function nameSort($a, $b) {
		$res = strcasecmp(trim($a['Nom']), trim($b['Nom']));
		if ($res === 0) {
			return $res = strcasecmp(trim($a['Prenom']), trim($b['Prenom']));
		}
		return $res;
	}

	public static function getNames($users, $withLinks = true, $sortNames = true) {

		$tab = array();
		foreach ($users AS $u) {
			$tab[] = array(
						'id' => $u['id'], 'Nom' => $u['Nom'], 'Prenom' => $u['Prenom']
					);
		}

		if ($sortNames === true) {
			usort($tab, array(
				'User', 'nameSort'
			));
		}

		$res = '';

		for ($cpt = 0; $cpt < sizeof($tab); $cpt++) {
			$elmt = '';

			$u = &$tab[$cpt];

			$elmt .= $u['Prenom'];

			if (isset($tab[$cpt + 1]) === false || strcasecmp($tab[$cpt + 1]['Nom'], $u['Nom']) !== 0) {
				$elmt .= ' ' . $u['Nom'];
			}

			if ($withLinks === true) {
				$elmt = '<a href="/famille/membre.php?id=' . $u['id'] . '">' . $elmt . '</a>';
			}

			if (isset($tab[$cpt + 1]) === true) {
				if (strcasecmp($tab[$cpt + 1]['Nom'], $u['Nom']) === 0
						&& (isset($tab[$cpt + 2]) === false || strcasecmp($tab[$cpt + 2]['Nom'], $u['Nom']) !== 0)) {
					$elmt .= ' et ';
				} else {
					$elmt .= ', ';
				}
			}

			$res .= $elmt;
		}

		return $res;
	}

}
