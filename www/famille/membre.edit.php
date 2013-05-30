<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';

$id = Html::getRequestOrPost('id', false, HTML::INTEGER);
$membre = $context->getDb()->personnes[$id];

if ($_SESSION['TOKEN'] === Html::getRequestOrPost('token', false, HTML::TEXT)) {
	$membreEdit = Html::getPost('membre', false, Html::ANY);
	$coordEdit = Html::getPost('coord', false, Html::ANY);
	$linked_personnes_id = Html::getRequestOrPost('linked_personnes_id', false, Html::INTEGER);
	$link = Html::getRequestOrPost('link', false, HTML::TEXT);
	if ($membreEdit !== false) {
		foreach ($membreEdit AS $key => $val) {
			if (in_array(
					$key,
					array(
							'Prenom',
							'Nom',
							'NomJF',
							'Sexe',
							'DateNaissance',
							'DateMort',
							'IdParent1',
							'IdParent2',
							'Email',
							'TelPortable',
							'outOfFamily',
					)) === true) {
				$val = trim($val);
				if ($key === 'TelPortable') {
					$val = str_replace(array(
								' ', '-', ','
							), '', $val);
				}
				if (strlen($val) === 0) {
					$val = null;
				}
				$membre[$key] = $val;
			}
		}
		if ($id === 0) {
			$membre = $context->getDb()->personnes()->insert($membre);
			$id = $membre['id'];

			if ($linked_personnes_id !== false) {
				$linkedMembre = $context->getDb()->personnes[$linked_personnes_id];

				if ($link === 'Conjoint' && $linkedMembre !== null) {
					$context->getDb()->personnes_liens_couple()
							->insert(array(
								'IdPersonne1' => $id, 'IdPersonne2' => $linkedMembre['id']
							));
				} elseif ($link === 'Enfant' && $linkedMembre !== null) {
					if ($membre['IdParent1'] === null && $membre['IdParent2'] != $id) {
						$membre['IdParent1'] = $linkedMembre['id'];
					} elseif ($membre['IdParent2'] === null && $membre['IdParent1'] != $id) {
						$membre['IdParent2'] = $linkedMembre['id'];
					}
					$membre->update();
				}
			}

		} else {
			$membre->update();
		}

		if (isset($membreEdit['couple']) === true) {
			foreach ($membreEdit['couple'] As $idCouple => $cpl) {
				$c = $context->getDb()->personnes_liens_couple('id', $idCouple)[$idCouple];
				$c['DateMariage'] = (strlen($cpl['DateMariage']) > 0 ? $cpl['DateMariage'] : null);
				$c->update();
			}
		}

		header('Location: /famille/membre.php?id=' . $membre['id']);
		exit();
	}

	if (($new_coordonnees_id = Html::getRequestOrPost('new_coordonnees_id', false, HTML::INTEGER)) !== false) {
		$context->getDb()->personnes_coordonnees()
				->insert(array(
					'personnes_id' => $id, 'coordonnees_id' => $new_coordonnees_id
				));
		header('Location: /famille/membre.edit.php?id=' . $membre['id']);
		exit();
	}

	if ($coordEdit !== false) {
		$newAddr = array();
		foreach ($coordEdit AS $key => $val) {
			if (in_array($key, array(
				'Adresse', 'CodePostal', 'Ville', 'Pays', 'Tel', 'Fax'
			)) === true) {
				$val = trim($val);
				if (strlen($val) === 0) {
					$val = null;
				}
				$newAddr[$key] = $val;
			}
		}
		$new_coordonnees_id = $context->getDb()->coordonnees()->insert($newAddr);
		$context->getDb()->personnes_coordonnees()
				->insert(array(
					'personnes_id' => $id, 'coordonnees_id' => $new_coordonnees_id
				));
		header('Location: /famille/membre.edit.php?id=' . $membre['id']);
		exit();
	}
}

include $_SERVER['DOCUMENT_ROOT'] . '/../includes/start.inc.php';

$parents = $context->getDb()->personnes('id', array(
					$membre['IdParent1'], $membre['IdParent2']
				));

$enfants =
		$context->getDb()->personnes()->select('*, Year(DateNaissance) As AnneeNaissance')
				->where('IdParent1 = ? OR IdParent2 = ?', $membre['id'], $membre['id'])->order('DateNaissance');

if ($membre === null && $id !== 0) {
?>
<div class="alert alert-error">
	<h4>Membre introuvable</h4>
	Aucun membre de la famille correspondant é cet identifiant n'a été trouvé.
</div>
<?php

} else {
?>
<div class="modal hide fade" id="pickPersonModal">
	<form method="post" id="pickPersonForm">
		<input type="hidden" name="id" value="<?=$id; ?>" />
		<input type="hidden" name="token" value="<?=$_SESSION['NEXT_TOKEN']; ?>" />
		<input type="hidden" name="link" value="" />
		<input type="hidden" name="linked_personnes_id" value="" />
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Sélectionner une personne</h3>
		</div>
		<div class="modal-body">
			<div class="progress progress-striped active">
				<div class="bar" style="width: 100%;"></div>
			</div>
		</div>
		<div class="modal-footer">
			<a href="javascript:setRelativePerson(null, null, null, 0);" class="btn btn-primary new"> <i class="icon-plus-sign icon-white"></i>
				Nouveau
			</a> <a href="javascript:setRelativePerson(null, null, null, null);" class="btn btn-inverse none" style="clear: right;"> <i
				class="icon-minus-sign icon-white"></i> Aucun
			</a> <a href="#" class="btn cancel" data-dismiss="modal">Annuler</a>
		</div>
	</form>
</div>
<form method="POST" action="?id=<?=$id; ?>">
	<input type="hidden" name="token" value="<?=$_SESSION['NEXT_TOKEN']; ?>" />
	<?php
	if ($id === 0) {
	?>
	<input type="hidden" name="link" value="<?=Html::getRequestOrPost('link', false, HTML::TEXT); ?>" />
	<input type="hidden" name="linked_personnes_id" value="<?=Html::getRequestOrPost('linked_personnes_id', false, HTML::INTEGER); ?>" />
	<?php
	}
	?>
	<div class="page-header">
		<div class="pull-right">
			<button type="submit" class="btn btn-primary">Enregistrer</button>
			<a <?php
	if ($id !== 0) {
			   ?> href="/famille/membre.php?id=<?=$membre['id']; ?>" <?php
	} else {
																	 ?>
				href="/famille/membre.edit.php?id=<?=Html::getRequestOrPost('linked_personnes_id', false, HTML::INTEGER);
												  ?>"
				<?php
	}
				?> class="btn">Annuler</a>
		</div>
		<h2>
			<?=$membre['Prenom'] . ' ' . $membre['Nom']; ?>
			<small><?=($membre['NomJF'] !== null ? '(' . $membre['NomJF'] . ') ' : ''); ?> <?=$context->getSql()
			->formatSqlDate($membre['DateNaissance'], '%d/%m/%Y')
			. ($membre['DateMort'] === null ? '' : ' - ' . $context->getSql()->formatSqlDate($membre['DateMort'], '%d/%m/%Y'));
																						   ?></small>
		</h2>
	</div>
	<div class="row">
		<div class="span6">
			<input type="text" name="membre[Prenom]" value="<?=$membre['Prenom']; ?>" placeholder="Prénom" size="" required="required" />
			<input type="text" name="membre[Nom]" value="<?=$membre['Nom']; ?>" required="required" placeholder="Nom" />
			<input type="text" name="membre[NomJF]" value="<?=$membre['NomJF']; ?>" placeholder="Nom de jeune fille" />
		</div>
		<div class="span6 pull-right">
			<h4>Coordonnées</h4>
			Email:
			<input type="email" name="membre[Email]" value="<?=$membre['Email']; ?>" />
			<br /> Portable:
			<input type="tel" name="membre[TelPortable]" value="<?=$membre['TelPortable']; ?>" />
			<br /> <br />
			<?php
	if ($id !== 0) {
		$membre_coordonnees = array();
		foreach ($membre->personnes_coordonnees() As $pc) {
			$c = $pc->coordonnees;
			$membre_coordonnees[] = $c['id'];
			?>
			<address class="alert alert-info" id="addr_<?=$c['id']; ?>">
				<button type="button" class="close" data-dismiss="alert"
					onClick="javascript:removeAddr('<?=$pc['id']; ?>', '<?=$membre['id']; ?>', '<?=$_SESSION['NEXT_TOKEN']; ?>');">&times;</button>
				<?php
			echo nl2br($c['Adresse']);
			echo '<br>' . $c['CodePostal'] . ' ' . $c['Ville'];
			echo ($c['Pays'] != 'France' ? '<br/>' . $c['Pays'] : '');
			if (strlen(trim($c['Tel'])) > 0) {
				echo '<br/>' . Db_Sql::formatSqlTel($c['Tel']);
			}
				?>
			</address>
			<?php
		}
			?>
			<div class="alert alert-success">
				<?php
		$proches =
				$context->getDb()->personnes()
						->where(
								'id IN (?, ?) OR IdParent1 IN (?, ?, ?) OR IdParent2 IN (?, ?, ?) OR id IN (SELECT IdPersonne1 FROM personnes_liens_couple WHERE IdPersonne2 = ?)OR id IN (SELECT IdPersonne2 FROM personnes_liens_couple WHERE IdPersonne1 = ?)',
								array(
										$membre['IdParent1'],
										$membre['IdParent2'],
										$membre['IdParent1'],
										$membre['IdParent2'],
										$membre['id'],
										$membre['IdParent1'],
										$membre['IdParent2'],
										$membre['id'],
										$membre['id'],
										$membre['id'],
								))->where('NOT id', $membre['id']);

		$coordsProches =
				$context->getDb()->personnes_coordonnees('personnes_id', $proches)->select('*, coordonnees.*')
						->where('NOT coordonnees_id', $membre_coordonnees)->group('coordonnees_id');
		foreach ($coordsProches AS $coord) {
				?>
				<a
					href="/famille/membre.edit.php?id=<?=$membre['id']; ?>&new_coordonnees_id=<?=$coord['coordonnees_id']; ?>&token=<?=$_SESSION['NEXT_TOKEN']; ?>">Rattacher
					au "<?=$coord['Adresse'] . ', ' . $coord['Ville']; ?>"...
				</a><br />
				<?php
		}
				?>
				<fieldset>
					<h5>Rattacher à une autre adresse</h5>
					<div class="input-append">
						<input type="text" placeholder="Code postal" size="7" maxlength="7" id="newCoordsZip" class="input-small" />
						<button class="btn" type="button" onClick="javascript:addAdress('<?=$_SESSION['NEXT_TOKEN']; ?>', '<?=$membre['id']; ?>');">
							<i class="icon-search"></i>
						</button>
					</div>
				</fieldset>
				<div class="modal hide fade" id="newAdressModal">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h3>Autre adresse</h3>
					</div>
					<div class="modal-body">
						<div class="progress progress-striped active">
							<div class="bar" style="width: 100%;"></div>
						</div>
					</div>
					<div class="modal-footer">
						<a href="#" class="btn" data-dismiss="modal">Annuler</a>
					</div>
				</div>
			</div>
			<?php
	}
			?>
		</div>
		<div class="span6 block">
			<ul class="nav nav-tabs" id="memberDetails">
				<li class="active"><a href="#nature" data-toggle="tab">Nature</a></li>
				<?php
	if ($id !== 0) {
				?>
				<li><a href="#parents" data-toggle="tab">Parents</a></li>
				<li><a href="#conjoints" data-toggle="tab">Conjoints</a></li>
				<li><a href="#enfants" data-toggle="tab">Enfants</a></li>
				<?php } ?>
			</ul>
			<div class="tab-content">
				<div class="tab-pane active" id="nature">
					Genre
					<select name="membre[Sexe]" required="required">
						<option value=""></option>
						<option value="M" <?=($membre['Sexe'] === 'M' ? ' selected="selected"' : ''); ?>>Homme</option>
						<option value="F" <?=($membre['Sexe'] === 'F' ? ' selected="selected"' : ''); ?>>Femme</option>
					</select>
					<br />Date de naissance
					<input type="date" name="membre[DateNaissance]" value="<?=$membre['DateNaissance']; ?>" required="required" />
					<br />Date de décès
					<input type="date" name="membre[DateMort]" value="<?=$membre['DateMort']; ?>" />
					<br />Hors famille
					<input type="hidden" name="membre[outOfFamily]" value="0" />
					<input type="checkbox" name="membre[outOfFamily]" value="1" <?=($membre['outOfFamily'] == 1 ? 'checked="checked"' : ''); ?> />
					<span class="help-block">Pour ne pas inclure dans les emails, les listings et les rappels d'infos incomplètes</span>
				</div>
				<div class="tab-pane" id="parents">
					<?php

	$parents = $context->getDb()->personnes()->where('id', array(
						$membre['IdParent1'], $membre['IdParent2']
					));

	for ($i = 1; $i <= 2; $i++) {
					?>
					<div class="alert alert-info">
						<a href="javascript:setRelativePerson('<?='IdParent' . $i; ?>', '<?=$membre['id']; ?>', '<?=$_SESSION['NEXT_TOKEN']; ?>');"> <i
							class="icon-pencil"></i> <?php

		if (isset($parents[$membre['IdParent' . $i]]) === true) {
			$p = $parents[$membre['IdParent' . $i]];
			echo $p['Nom'] . (strlen($p['NomJF']) > 0 ? ' ' . $p['NomJF'] : '') . ' ' . $p['Prenom'];
		} else {
			echo "Inconnu";
		}
													 ?>
						</a>
					</div>
					<?php
	}
					?>
				</div>
				<div class="tab-pane" id="conjoints">
					<?php
	$context->addJsFile('/static/js/member.edit.js');
	$conjoints =
			$context->getDb()
					->personnes_liens_couple('IdPersonne1 = ? OR IdPersonne2 = ?', array(
						$membre['id'], $membre['id']
					))->select('*, IF(IdPersonne1 = ' . $membre['id'] . ', IdPersonne2, IdPersonne1) AS personnes_id')->order('id');
	foreach ($conjoints As $c) {
					?>
					<div class="alert alert-info">
						<button type="button" class="close" data-dismiss="alert"
							onClick="javascript:removeRelativePerson('Conjoint', '<?=$membre['id']; ?>', '<?=$_SESSION['NEXT_TOKEN']; ?>', '<?=$c['id']; ?>');">&times;</button>
						<a href="/famille/membre.php?id=<?=$c->personnes['id']; ?>"><?=$c->personnes['Prenom'] . ' ' . $c->personnes['Nom'];
																					?></a>
						<br />Date du mariage :
						<input type="date" name="membre[couple][<?=$c['id']; ?>][DateMariage]" value="<?=$c['DateMariage']; ?>" />
					</div>
					<?php
	}
					?>
					<div class="alert alert-success">
						<a href="javascript:setRelativePerson('Conjoint', '<?=$membre['id']; ?>', '<?=$_SESSION['NEXT_TOKEN']; ?>');">Ajouter un
							conjoint...</a>
					</div>
				</div>
				<div class="tab-pane" id="enfants">
					<?php
	foreach ($enfants As $e) {
					?>
					<div class="alert alert-info">
						<button type="button" class="close" data-dismiss="alert"
							onClick="javascript:removeRelativePerson('Enfant', '<?=$membre['id']; ?>', '<?=$_SESSION['NEXT_TOKEN']; ?>', '<?=$e['id']; ?>');">&times;</button>
						<a href="/famille/membre.php?id=<?=$e['id']; ?>"><?=$e['Prenom'] . ' ' . $e['Nom']; ?></a> (
						<?=$e['AnneeNaissance']; ?>
						)
					</div>
					<?php
	}
	foreach ($conjoints As $c) {
		foreach ($context->getDb()->personnes()
				->where(
						'(IdParent1 = ? AND IdParent2 IS NULL) OR (IdParent2 = ? AND IdParent1 IS NULL)',
						$c['personnes_id'],
						$c['personnes_id']) As $e) {
					?>
					<div class="alert alert-success">
						<a href="javascript:setRelativePerson('Enfant', '<?=$membre['id']; ?>', '<?=$_SESSION['NEXT_TOKEN']; ?>', '<?=$e['id']; ?>');">Rattacher
							<?=$e['Prenom'] . ' ' . $e['Nom']; ?>
						</a>
					</div>
					<?php
		}
	}

					?>
					<div class="alert alert-success">
						<a href="javascript:setRelativePerson('Enfant', '<?=$membre['id']; ?>', '<?=$_SESSION['NEXT_TOKEN']; ?>');">Ajouter un enfant...</a>
					</div>
				</div>
			</div>
			<br>
		</div>
	</div>
</form>
<?php
}

include $_SERVER['DOCUMENT_ROOT'] . '/../includes/end.inc.php';
