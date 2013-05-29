<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';

$foyers = $context->getDb()->coordonnees()->order('Pays', 'CodePostal', 'Ville', 'Adresse');
$personnes =
		$context->getDb()->personnes()
				->select(
						"*, DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(DateNaissance, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(DateNaissance, '00-%m-%d')) AS age")
				->where('DateMort', null)->where('outOfFamily', 0)->order('Nom', 'Prenom');

include $_SERVER['DOCUMENT_ROOT'] . '/../includes/start.inc.php';

// $context->addCssFile('/static/css/membres.css');

?>
<div class="block">
	<ul class="nav nav-tabs" id="listings">
		<li class="active"><a href="#paper" data-toggle="tab">Par foyer</a></li>
		<li><a href="#email" data-toggle="tab">Par personne</a></li>
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="paper">
			<table class="table table-striped table-hover">
				<thead>
					<tr>
						<th>Foyer</th>
						<th>Adresse</th>
						<th>Code postal</th>
						<th>Ville</th>
						<th>Pays</th>
						<th>Tel</th>
						<th>Fax</th>
					</tr>
				</thead>
				<tbody>
					<?php
foreach ($foyers As $a) {
	$habitants = array();
	foreach ($a->personnes_coordonnees() As $h) {
		if ($h->personnes['id'] !== null && $h->personnes['DateMort'] === null && $h->personnes['outOfFamily'] == 0) {
			$habitants[$h->personnes['id']] = $h->personnes;
		}
	}
	if (sizeof($habitants) > 0) {
					?>
					<tr>
						<td><?php
		echo User::getNames($habitants, true, true);
							?></td>
						<td><?=nl2br($a['Adresse']); ?></td>
						<td nowrap><?=$a['CodePostal']; ?></td>
						<td><?=$a['Ville']; ?></td>
						<td><?=$a['Pays']; ?></td>
						<td nowrap><?=Db_Sql::formatSqlTel($a['Tel']); ?></td>
						<td nowrap><?=Db_Sql::formatSqlTel($a['Fax']); ?></td>
					</tr>
					<?php
	}
}
					?>
				</tbody>
			</table>
		</div>
		<div class="tab-pane" id="email">
			<table class="table table-striped table-hover">
				<thead>
					<tr>
						<th>Prénom et nom</th>
						<th>Age</th>
						<th>Email</th>
						<th>Adresse</th>
						<th>Code postal</th>
						<th>Ville</th>
						<th>Pays</th>
					</tr>
				</thead>
				<tbody>
					<?php
foreach ($personnes As $e) {
	$link = $e->personnes_coordonnees()->fetch();
	$a = null;
	if (is_object($link) === true) {
		$a = $link->coordonnees;
	}
					?>
					<tr>
						<td><a href="/famille/membre.php?id=<?=$e['id']; ?>"><?=$e['Prenom'] . ' ' . $e['Nom']; ?></a></td>
						<td><?=$e['age']; ?></td>
						<td><?php
	if ($e['Email'] !== null) {
							?> <a href="mailto:<?=$e['Email']; ?>"><?=$e['Email']; ?></a> <?php }
																						  ?></td>
						<td><?=nl2br($a['Adresse']); ?></td>
						<td nowrap><?=$a['CodePostal']; ?></td>
						<td><?=$a['Ville']; ?></td>
						<td><?=$a['Pays']; ?></td>
					</tr>
					<?php
}
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<?php
$context->addJsInline("
  $(function () {
    $('#listings.nav-tabs li.active a').tab('show');
  })
");

include $_SERVER['DOCUMENT_ROOT'] . '/../includes/end.inc.php';
