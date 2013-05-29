<?php
$_NO_TOKEN = true;
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';

if ($_SESSION['TOKEN'] === Html::getRequestOrPost('token', false, HTML::TEXT)) {

	$link = Html::getRequestOrPost('link', false, HTML::TEXT);
	$linked_personnes_id = Html::getRequestOrPost('linked_personnes_id', false, Html::INTEGER);
	$id = Html::getRequestOrPost('id', false, HTML::INTEGER);

	$membre = $context->getDb()->personnes[$id];

	if ($link === 'Conjoint' && $linked_personnes_id !== false) {
		if ($context->getDb()->personnes_liens_couple('id', $linked_personnes_id)->delete() !== 1) {
			exit(1);
		}
	}

	if ($link === 'Enfant' && $linked_personnes_id !== false) {
		$enfant = $context->getDb()->personnes[$linked_personnes_id];
		if ($enfant['IdParent1'] == $id) {
			$enfant['IdParent1'] = null;
		}
		if ($enfant['IdParent2'] == $id) {
			$enfant['IdParent2'] = null;
		}
		$enfant->update();
		include $_SERVER['DOCUMENT_ROOT'] . '/../includes/end.inc.php';
		exit(1);
	}

	$IdPersonneCoordonnees = Html::getRequestOrPost('IdPersonneCoordonneesDelete', false, Html::INTEGER);
	if ($IdPersonneCoordonnees !== false) {
		if ($context->getDb()->personnes_coordonnees('id', $IdPersonneCoordonnees)->delete() !== 1) {
			exit(1);
		}
	}
}
