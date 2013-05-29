<?php
$context->addCssFile('/static/css/discussion.css');

$idDiscusssions = array(
			0
		);

$who = Context::getInstance()->getDb()->personnes();
foreach (explode(' ', trim($q)) AS $elmt) {
	$who->where('Prenom like ? Or Nom Like ? Or NomJF Like ?', array(
				'%' . $elmt . '%', '%' . $elmt . '%', '%' . $elmt . '%'
			));
}

if (count($who) > 0) {
	$participants = Context::getInstance()->getDb()->discussions_messages('personnes_id', $who)->select('DISTINCT discussions_id');
	foreach ($participants As $m) {
		$idDiscusssions[] = $m['discussions_id'];
	}
}

$messages = Context::getInstance()->getDb()->discussions_messages();
foreach (explode(' ', trim($q)) AS $elmt) {
	$messages->where('Message Like "%' . $elmt . '%"');
}

foreach ($messages As $m) {
	$idDiscusssions[] = $m['discussions_id'];
}

$res = Context::getInstance()->getDb()->discussions('id', $idDiscusssions);

$nbPerPage = 15;
$nbRes = $res->count('*');
$nbPages = ceil($nbRes / $nbPerPage);

if ($nbRes != 0) {
?>
<table class="table table-bordered table-striped table-hover">
	<thead>
		<tr>
			<th>Sujet</th>
			<th>Dernier message</th>
		</tr>
	</thead>
	<tbody>
		<?php
	foreach ($res->order("CreateDate desc")->limit($nbPerPage, ($_SESSION['search_page'] - 1) * $nbPerPage) As $d) {
		?>
		<tr>
			<td>
				<h5>
					<a href="/discussions/discussion.php?id=<?=$d['id']; ?>"><?=(strlen(trim($d['Sujet'])) > 0 ? ucfirst($d['Sujet']) : '[Pas de sujet]'); ?></a>
				</h5>
			</td>
			<td><?=Db_Sql::formatSqlDate($d->discussions_messages()->max('DateMessage'), '%d/%m/%Y %H:%M') ?></td>
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
		<li <?=($i === $_SESSION['search_page'] ? ' class="active"' : ''); ?>><a href="?page=<?=$i; ?>"><?=$i; ?></a></li>
		<?php
	}
		?>
	</ul>
</div>
<?php
} else {
?>
<div class="alert alert-info">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	<h4>Aucun résultat</h4>
	Aucune discussion ne correspond à votre recherche.
</div>
<?php
}
