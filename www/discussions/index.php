<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';

$context->addCssFile('/static/css/discussion.css');

include $_SERVER['DOCUMENT_ROOT'] . '/../includes/start.inc.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/../libs/NotORM/NotORM.php';

$_SESSION['discussions_page'] = Html::getRequestOrPost('page', (isset($_SESSION['discussions_page']) ? $_SESSION['discussions_page'] : 1), Html::INTEGER);

$nbPerPage = 15;

$db = $context->getDb();

$nbPages = ceil($db->discussions()->count('*') / $nbPerPage);
?>
<div class="block discussions">
	<a href="/discussions/discussion.php?id=0" class="btn btn-success pull-left">Nouvelle discussion</a>
	<div class="pagination pagination-right">
		<ul>
			<?php
for ($i = 1; $i <= $nbPages; $i++) {
			?>
			<li <?=($i === $_SESSION['discussions_page'] ? ' class="active"' : ''); ?>><a href="?page=<?=$i; ?>"><?=$i; ?></a></li>
			<?php
}
			?>
		</ul>
	</div>
	<table class="table table-bordered table-striped table-hover">
		<thead>
			<tr>
				<th>Sujet</th>
				<th>Dernier message</th>
			</tr>
		</thead>
		<tbody>
			<?php
foreach ($db->discussions_messages()->select('discussions.*, discussions_id, Max(DateMessage) As DateLastMessage')->group('discussions.id')
		->order("sticky desc, DateLastMessage desc")->limit($nbPerPage, ($_SESSION['discussions_page'] - 1) * $nbPerPage) As $d) {
			?>
			<tr>
				<td>
					<h5>
						<a href="/discussions/discussion.php?id=<?=$d['id']; ?>"><?=(strlen(trim($d['Sujet'])) > 0 ? ucfirst(stripslashes($d['Sujet'])) : '<i>[Aucun sujet]</i>');
																				 ?></a>
					</h5> <?php
	if ($d['sticky'] == 1) {
						  ?>
					<span class="label label-important">Important</span> <?php } ?> <span class="label cat<?=$d['discussions_categories_id']; ?>">
						<?=$d->discussions_categories['categorie']; ?>
					</span> <?php
	$participants = array();
	foreach ($d->discussions->discussions_messages()->select('personnes_id')->group('discussions_id, personnes_id')->order('min(DateMessage)') As $m) {
		$participants[] = $m->personnes;
	}
	echo User::getNames($participants, true, false);
							?>
				</td>
				<td><?=Db_Sql::formatSqlDate($d['DateLastMessage'], '%d/%m/%Y %H:%M') ?></td>
			</tr>
			<!-- 	echo $d['Sujet'] . ' - ' . $d['CreateDate'] . ' - ' . $d->personnes['Prenom'] . ' ' . $d->personnes['Nom'] . '<br/>'; -->
			<!-- 	foreach ($d->discussions_messages()->select('distinct personnes_id') AS $m) { -->
			<!-- 		echo $m->personnes['Prenom'] . '<br/>'; -->
			<!-- 	} -->
			<?php
}
			?>
		</tbody>
	</table>
	<div class="pagination pagination-right">
		<ul>
			<?php
for ($i = 1; $i <= $nbPages; $i++) {
			?>
			<li <?=($i === $_SESSION['discussions_page'] ? ' class="active"' : ''); ?>><a href="?page=<?=$i; ?>"><?=$i; ?></a></li>
			<?php
}
			?>
		</ul>
	</div>
</div>
<?php

include $_SERVER['DOCUMENT_ROOT'] . '/../includes/end.inc.php';
