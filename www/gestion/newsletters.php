<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';
$context->addRequiredAuth('Gestion');

$nbPerPage = 50;
$_SESSION['newsletters_page'] =
		Html::getRequestOrPost('page', (isset($_SESSION['newsletters_page']) ? $_SESSION['newsletters_page'] : 1), Html::INTEGER);

$nbPages = ceil($context->getDb()->newsletters()->count('*') / $nbPerPage);

include $_SERVER['DOCUMENT_ROOT'] . '/../includes/start.inc.php';

// $context->addCssFile('/static/css/membres.css');
?>
<div class="block">
	<div class="pagination pagination-right">
		<ul>
			<?php
for ($i = 1; $i <= $nbPages; $i++) {
			?>
			<li <?=($i === $_SESSION['newsletters_page'] ? ' class="active"' : ''); ?>><a href="?page=<?=$i; ?>"><?=$i; ?></a></li>
			<?php
}
			?>
		</ul>
	</div>
	<table class="table table-condensed table-hover table-striped">
		<thead>
			<tr>
				<th>Date</th>
				<th>Destinataire</th>
				<th>Email</th>
				<th>Lecture</th>
			</tr>
		</thead>
		<tbody>
			<?php
foreach ($context->getDb()->newsletters()->order('sentDate Desc')->limit($nbPerPage, ($_SESSION['newsletters_page'] - 1) * $nbPerPage) As $l) {
			?>
			<tr <?=($l['readDate'] === null ? ($l['error'] !== null ? 'class="error"' : 'class="warning"') : ''); ?>>
				<td><a href="/gestion/newsletter.php?id=<?=$l['id']; ?>"><?=Db_Sql::formatSqlDate($l['sentDate'], '%d/%m/%Y %H:%M:%S'); ?></a></td>
				<td><a href="/gestion/newsletter.php?id=<?=$l['id']; ?>"><?=$l['email']; ?></a></td>
				<td><a href="/famille/membre.php?id=<?=$l['personnes_id']; ?>"><?=$l->personnes['Prenom'] . ' ' . $l->personnes['Nom']; ?></a></td>
				<td><?php
	if ($l['readDate'] !== null) {
		echo Db_Sql::formatSqlDate($l['readDate'], '%d/%m/%Y %H:%M:%S');
	} else {
		echo $l['error'];
	}
					?></td>
			</tr>
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
			<li <?=($i === $_SESSION['newsletters_page'] ? ' class="active"' : ''); ?>><a href="?page=<?=$i; ?>"><?=$i; ?></a></li>
			<?php
}
			?>
		</ul>
	</div>
</div>
<?php

include $_SERVER['DOCUMENT_ROOT'] . '/../includes/end.inc.php';
