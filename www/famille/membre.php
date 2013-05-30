<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';

include $_SERVER['DOCUMENT_ROOT'] . '/../includes/start.inc.php';

$id = Html::getRequest('id', false, HTML::INTEGER);

$membre = $context->getDb()->personnes[$id];

$parents = $context->getDb()->personnes('id', array(
					$membre['IdParent1'], $membre['IdParent2']
				));

$enfants = $context->getDb()->personnes()->where('IdParent1 = ? OR IdParent2 = ?', $membre['id'], $membre['id'])->order('DateNaissance');

$conjoints = array();
foreach ($context->getDb()->personnes_liens_couple('IdPersonne1 = ? OR IdPersonne2 = ?', array(
			$membre['id'], $membre['id']
		))->select('*, IF(IdPersonne1 = ' . $membre['id'] . ', IdPersonne2, IdPersonne1) AS personnes_id')->order('id') As $c) {
	$conjoints[] = $c->personnes;
}

if ($membre === null) {
?>
<div class="alert alert-error">
	<h4>Membre introuvable</h4>
	Aucun membre de la famille correspondant à cet identifiant n'a été trouvé.
</div>
<?php

} else {
?>
<div class="page-header">
	<h2>
		<?=$membre['Prenom'] . ' ' . $membre['Nom']; ?>
		<small><?=($membre['NomJF'] !== null ? '(' . $membre['NomJF'] . ') ' : ''); ?> <?=$context->getSql()
			->formatSqlDate($membre['DateNaissance'], '%d/%m/%Y')
			. ($membre['DateMort'] === null ? '' : ' - ' . $context->getSql()->formatSqlDate($membre['DateMort'], '%d/%m/%Y'));
																					   ?></small>
		<a href="/famille/membre.edit.php?id=<?=$id; ?>" class="pull-right"><button class="btn btn-primary">Modifier</button></a>
	</h2>
</div>
<div class="row">
	<div class="span6">
		<i class="icon-envelope"></i>:
		<?php
	if (strlen(trim($membre['Email'])) > 0) {
		?>
		<a href="mailto:<?=$membre['Email']; ?>" target="_mail"> <?=$membre['Email']; ?></a>
		<?php
	} else {
		echo '<i>Inconnue</i>';
	}
		?>
		<br /> Portable:
		<?php
	if (strlen(trim($membre['TelPortable'])) > 0) {
		echo Db_Sql::formatSqlTel($membre['TelPortable']);
	} else {
		echo '<i>Inconnu</i>';
	}

	if ($membre['outOfFamily'] == 1) {
		?>
		<div class="alert" style="margin-top: 20px;">
			<strong>Hors famille</strong>
		</div>
		<?php
	}
		?>
	</div>
	<div class="span6">
		<?php
	if (count($membre->personnes_coordonnees()) === 0) {
		?>
		<div class="alert ">
			<h4>Information manquante</h4>
			Aucune adresse connue
		</div>
		<?php
	} else {
		foreach ($membre->personnes_coordonnees() As $pc) {
			$c = $pc->coordonnees;
		?>
		<address class="alert alert-info" id="addr_<?=$c['id']; ?>">
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
	}
		?>
	</div>
</div>
<ul class="nav nav-tabs" id="memberDetails">
	<li class="active"><a href="#tree" data-toggle="tab">Généalogie</a></li>
	<li><a href="#portrait" data-toggle="tab">Portaits</a></li>
	<!-- 	<li><a href="#pyramid" data-toggle="tab">Pyramide</a></li> -->
</ul>
<div class="tab-content">
	<div class="tab-pane active" id="tree">
		<?php

	$fratrie1 = $context->getDb()->personnes()->where('IdParent1', $parents);
	$fratrie2 = $context->getDb()->personnes()->where('IdParent2', $parents);

	$tree = new Family_Tree($membre, $conjoints, $enfants, $parents, $fratrie1, $fratrie2);

	$context->addCssFile('/static/css/tree.css');

	function displayTree(Family_Tree_Node $node) {
		$res = '<li>';
		$res .= '<div class="node">';
		foreach ($node->getElements() As $e) {
			$res .=
					'<a class="member" href="/famille/membre.php?id=' . $e['id']
							. '"><img src="/photos/portrait.jpg.php?f=mini&personnes_id=' . $e['id'] . '" alt="" /><br/>' . $e['Prenom']
							. ' ' . $e['Nom'] . '</a>';
		}
		$res .= '</div>';

		if (sizeof($node->getChilds()) > 0) {
			$res .= '<ul>';
			foreach ($node->getChilds() As $c) {
				$res .= displayTree($c);
			}
			$res .= '</ul>';
		}
		$res .= '</li>';
		return $res;
	}
		?>
		<!--
We will create a family tree using just CSS(3)
The markup will be simple nested lists
-->
		<div class="tree">
			<ul>
				<?php
	echo displayTree($tree->getRootNode());
				?>
			</ul>
		</div>
	</div>
	<div class="tab-pane" id="portrait" style="padding: 0px;">
		<ul class="thumbnails" style="padding: 0px;">
			<?php
	foreach ($context->getDb()->photos_presences()->where('portrait', 1)->where('photos_presences.personnes_id', $id)
			->order('photos.DateCliche Desc') AS $p) {
			?>
			<li class="span2">
				<div class="thumbnail" style="text-align: center;">
					<a href="/photos/photo.php?id=<?=$p['photos_id']; ?>"> <img src="/photos/portrait.jpg.php?id=<?=$p['id']; ?>"
						style="height: 150px;" alt="<?=$membre['Prenom'] . ' ' . $membre['Nom']; ?>">
					</a>
				</div>
			</li>
			<?php
	}
			?>
		</ul>
	</div>
	<div class="tab-pane" id="pyramid">Pyramide des âges</div>
</div>
<script>
  $(function () {
    $('#memberDetails a:last').tab('show');
  })
</script>
<?php
}

include $_SERVER['DOCUMENT_ROOT'] . '/../includes/end.inc.php';
