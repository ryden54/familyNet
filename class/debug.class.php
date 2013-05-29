<?php
class Debug
{
	const SQL = 'SQL';

	protected $datas = array();
	public function log($type, $subtype, $data, $result, $status = false) {
		$this->datas[] =
				array(
						'type' => $type,
						'subtype' => $subtype,
						'data' => $data,
						'result' => $result,
						'time' => substr((string) microtime(), 1, 8),
						'status' => $status
				);
	}

	public function getDatas() {
		return $this->datas;
	}
}
