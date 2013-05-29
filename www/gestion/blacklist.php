<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';

$context->addRequiredAuth('Gestion');

$_SESSION['blacklist_page'] = Html::getRequestOrPost('page', (isset($_SESSION['blacklist_page']) ? $_SESSION['blacklist_page'] : 1), Html::INTEGER);

if ($_SESSION['TOKEN'] === Html::getRequestOrPost('token', false, HTML::TEXT)) {
	$ip = Html::getRequestOrPost('forgive', false, Html::TEXT);
	if ($ip !== false) {
		$context->unsetBlacklist($ip);
	}
	header('Location: /gestion/blacklist.php');
	exit;
}

include $_SERVER['DOCUMENT_ROOT'] . '/../includes/start.inc.php';
?>
<div class="block">
	<h3>Blacklist des adresses</h3>
	<?php
$list = $context->getDb()->autorisations_blacklist()->order('lastDate desc');
if (count($list) === 0) {
	?>
	<div class="alert alert-info">Aucune adresse dans la blacklist</div>
	<?php
} else {
	?>
	<table class="table table-striped">
		<thead>
			<tr>
				<th>IP</th>
				<th>Nombre d'échecs</th>
				<th>Dernier echec</th>
				<th>Blocage</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php
	foreach ($list As $a) {
		$delay = $context->isBlackListed($a['ip']);
			?>
			<tr>
				<td><?=$a['ip']; ?></td>
				<td><?=$a['tries']; ?></td>
				<td><?=Db_Sql::formatSqlDate($a['lastDate'], '%d/%m/%Y %H:%M'); ?></td>
				<td><?=($delay === false ? '<span class="label">Aucun</span>' : '<span class="label">' . $delay . ' secondes</span>'); ?></td>
				<td><a class="btn btn-warning" href="?forgive=<?=urlencode($a['ip']); ?>&token=<?=$_SESSION['NEXT_TOKEN']; ?>">Pardonner</a></td>
			</tr>
			<?php
	}

			?>
		</tbody>
	</table>
	<?php

}
	?>
</div>
<?php

include $_SERVER['DOCUMENT_ROOT'] . '/../includes/end.inc.php';
