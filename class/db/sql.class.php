<?php
class Db_Sql Extends Pdo
{
	// 	public function query($RSQL, $params = false) {
	// 		if ($params === false) {
	// 			$stmt = $this->query($RSQL);
	// 		} else {
	// 			$stmt = $this->prepare($RSQL);
	// 			$stmt->execute($params);
	// 		}
	// 		Context::getInstance()->getDebug()
	// 				->log(
	// 						Debug::SQL,
	// 						'mysql://' . Config::get('HOST', 'SQL') . '@' . Config::get('USER', 'SQL') . ':' . Config::get('PASS', 'SQL') . '/'
	// 								. Config::get('DATABASE', 'SQL') . '<br/>' . $this->getAttribute(PDO::ATTR_CONNECTION_STATUS),
	// 						$RSQL,
	// 						$stmt->rowCount());
	// 		return $stmt;
	// 	}

	public function query($statement) {
		$stmt = parent::query($statement);

		Context::getInstance()->getDebug()
				->log(
						Debug::SQL,
						'mysql://' . Config::get('HOST', 'SQL') . '@' . Config::get('USER', 'SQL') . '/' . Config::get('DATABASE', 'SQL') . '<br/>'
								. $this->getAttribute(PDO::ATTR_CONNECTION_STATUS),
						$statement,
						$stmt->rowCount());

		return $stmt;
	}

	public function prepare($statement, $options = null) {
		if ($options !== null) {
			$res = parent::prepare($statement, $options);
		} else {
			$res = parent::prepare($statement);
		}

		Context::getInstance()->getDebug()
				->log(
						Debug::SQL . ' Prepare',
						'mysql://' . Config::get('HOST', 'SQL') . '@' . Config::get('USER', 'SQL') . '/' . Config::get('DATABASE', 'SQL') . '<br/>'
								. $this->getAttribute(PDO::ATTR_CONNECTION_STATUS),
						$statement,
						false);

		return $res;
	}

	public function fetchAll($query, $parameters = array()) {
		$read_stmt = $this->prepareAndExecute($query, $parameters);

		$fetched_rows = $read_stmt->fetchAll(PDO::FETCH_ASSOC);
		$read_stmt->closeCursor();

		unset($read_stmt);

		return ($fetched_rows);
	}

	public function fetchOne($query, $parameters = array()) {
		$read_stmt = $this->prepareAndExecute($query, $parameters);

		$fetched_row = $read_stmt->fetch();
		if (!is_array($fetched_row)) {
			$fetched_row = false;
		}

		$read_stmt->closeCursor();
		unset($read_stmt);
		return ($fetched_row);
	}

	public function fetchColumn($query, $parameters = array(), $column = 0) {
		$column = abs((int) $column);

		$read_stmt = $this->prepareAndExecute($query, $parameters);
		$fetched_column = $read_stmt->fetchColumn($column);

		$read_stmt->closeCursor();
		unset($read_stmt);
		return ($fetched_column);
	}

	public function modify($query, $parameters) {
		$modify_stmt = $this->prepareAndExecute($query, $parameters);
		return ($modify_stmt->rowCount());
	}

	private function prepareAndExecute($query, $parameters = array()) {
		$prep_stmt = $this->prepare($query);
		$prep_stmt->execute($parameters);
		return ($prep_stmt);
	}

	public static function formatSqlDate($d, $f) {
		$date = false;
		if ($date === false && strlen($d) >= 14) {
			$date = DateTime::createFromFormat('Y-m-d H:i:s', $d);
		}
		if ($date === false && strlen($d) >= 6) {
			$date = DateTime::createFromFormat('Y-m-d', $d);
		}
		if ($date instanceof DateTime) {
			return strftime($f, intval($date->format('U')));
		}
		return false;
	}

	public static function formatSqlTel($t) {
		$tel = '';
		for ($cptChar = 0; $cptChar < strlen($t); $cptChar++) {
			if ($cptChar % 2 == 0 && $cptChar != 0)
				$tel .= " ";
			$tel .= $t[$cptChar];
		}
		return $tel;

	}

	public static function getNowString($dateTime = true) {
		return date('Y-m-d' . ($dateTime === true ? ' H:i:s' : ''));
	}
}
