<?php
class Family_Tree_Node
{
	protected $elmts = array();

	protected $childs = array();
	protected $parent = null;

	public function __construct($e) {
		$this->addElement($e);
	}
	public function addElement($e) {
		$this->elmts[$e['id']] = $e;
	}

	public function getElements() {
		return $this->elmts;
	}

	public function addChild(Family_Tree_Node $n) {
		if (in_array($n, $this->childs) === false) {
			$this->childs[] = $n;
			$n->setParent($this);
		}
	}

	public function setParent(Family_Tree_Node $n) {
		$this->parent = $n;
	}

	public function getChilds() {
		return $this->childs;
	}

	public function getParent() {
		return $this->parent;
	}

	public function getNames() {
		$res = array();
		foreach ($this->elmts As $e) {
			$res[] = $e['Prenom'];
		}
		return implode(', ', $res);
	}
}
