<?php
class Family_Tree
{
	protected $elmts = array();
	protected $nodes = array();
	protected $rootNode = null;

	protected $conjoints = array();
	protected $enfants = array();

	public function __construct() {
		foreach (func_get_args() As $a) {
			$this->add($a);
		}

		$this->digest();
	}

	protected function add($elmt) {
		if (is_array($elmt) === true || ($elmt instanceof Traversable && !($elmt instanceof NotORM_Row))) {
			foreach ($elmt As $e) {
				$this->add($e);
			}
		} else {
			$this->elmts[$elmt['id']] = $elmt;
		}
	}

	protected function digest() {
		$elmtsId = array();
		foreach ($this->elmts As $e) {
			$elmtsId[$e['id']] = $e['id'];
		}

		// Reaching conjoints
		foreach (Context::getInstance()->getSql()
				->fetchAll(
						'SELECT personnes.id, IF(IdPersonne1 = personnes.id, IdPersonne2, IdPersonne1) AS conjoint_id FROM personnes Left Join personnes_liens_couple On (personnes.id = personnes_liens_couple.IdPersonne1 OR personnes.id = personnes_liens_couple.IdPersonne2) where personnes.id IN ('
								. implode(',', $elmtsId) . ') ORDER BY personnes_liens_couple.DateMariage Asc') AS $c) {
			if (isset($this->conjoints[$c['id']]) === false) {
				$this->conjoints[$c['id']] = array();
			}
			$this->conjoints[$c['id']][] = $c['conjoint_id'];
		}

		// Reaching childs
		foreach (Context::getInstance()->getSql()
				->fetchAll(
						'SELECT personnes.id, enfants.id AS enfant_id FROM personnes Left Join personnes enfants On (personnes.id = enfants.IdParent1 OR personnes.id = enfants.IdParent2) where personnes.id IN ('
								. implode(',', $elmtsId) . ') ORDER BY enfants.DateNaissance Asc') AS $c) {
			if (isset($this->enfants[$c['id']]) === false) {
				$this->enfants[$c['id']] = array();
			}
			$this->enfants[$c['id']][] = $c['enfant_id'];
		}

		foreach ($this->elmts As $e) {
			if (isset($this->nodes[$e['id']]) === false) {
				$this->nodes[$e['id']] = new Family_Tree_Node($e);
			}
			if (isset($this->conjoints[$e['id']])) {
				foreach ($this->conjoints[$e['id']] As $c_id) {
					if (isset($this->elmts[$c_id])) {
						if (isset($this->nodes[$c_id]) === false) {
							$this->nodes[$e['id']]->addElement($this->elmts[$c_id]);
							$this->nodes[$c_id] = &$this->nodes[$e['id']];
						}
					}
				}
			}
		}

		foreach ($this->enfants As $id => $es) {
			foreach ($es As $e) {
				if (isset($this->elmts[$id]) === true && isset($this->elmts[$e]) === true) {
					$this->nodes[$id]->addChild($this->nodes[$e]);
				}

			}
		}

		$node = &$this->nodes[array_keys($this->nodes)[0]];
		while ($node->getParent() !== null) {
			$node = $node->getParent();
		}
		$this->rootNode = &$node;
	}

	public function setRootNodeElementId($id) {
		if (isset($this->nodes[intval($id)]) === true) {
			$this->rootNode = $this->nodes[intval($id)];
		}
	}

	public function getRootNode() {
		return $this->rootNode;
	}
}
