<?php
$context->addCssFile('/static/css/photos.css');

$who = Context::getInstance()->getDb()->personnes();
foreach (explode(' ', trim($q)) AS $elmt) {
	$who
			->where(
					'Prenom like ? Or Nom Like ? Or NomJF Like ?',
					array(
						'%' . $elmt . '%', '%' . $elmt . '%', '%' . $elmt . '%'
					));
}

$presences = Context::getInstance()->getDb()->photos_presences('personnes_id', $who)->select('DISTINCT photos_id');
$idPres = array(0);
foreach ($presences As $p) {
	$idPres[] = $p['photos_id'];
}

$res = Context::getInstance()->getDb()->photos();
foreach (explode(' ', trim($q)) AS $elmt) {
	$res->where('id IN (' . implode(', ', $idPres) . ') OR Titre like ? or Lieu like ?', '%' . $elmt . '%', '%' . $elmt . '%');
}
$nbPerPage = 24;
$nbRes = $res->count('DISTINCT photos.id');
$nbPages = ceil($nbRes / $nbPerPage);
$page = Html::getRequestOrPost('page', 1, HTML::INTEGER);

if ($nbRes == 0) {
?>
<div class="alert alert-info">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	<h4>Aucun résultat</h4>
	Aucune photo ne correspond à votre recherche.
</div>
<?php
} else {
?>
<div class="block">
	<?php
	if ($nbPages > 1) {
	?>
	<div class="pagination pagination-right">
		<ul>
			<?php
		$params = array(
					'q' => $q, 't' => $t, 'page' => 1
				);
		for ($i = 1; $i <= $nbPages; $i++) {
			$params['page'] = $i;
			?>
			<li <?=($i === $page ? ' class="active"' : ''); ?>><a href="?<?=http_build_query($params); ?>"><?=$i; ?></a></li>
			<?php
		}
			?>
		</ul>
	</div>
	<?php } ?>
	<ul class="thumbnails">
		<?php

	foreach ($res->order("DateCliche desc")->limit($nbPerPage, ($page - 1) * $nbPerPage) As $p) {
		?>
		<li class="span2">
			<div class="thumbnail">
				<a href="/photos/photo.php?id=<?=$p['id']; ?>"> <img alt="<?=$p['Titre']; ?>" src="/photos/photo.jpg.php?id=<?=$p['id']; ?>&f=mini">
				</a>
			</div>
		</li>
		<?php
	}
		?>
	</ul>
	<?php
	if ($nbPages > 1) {
	?>
	<div class="pagination pagination-right">
		<ul>
			<?php
		$params = array(
					'q' => $q, 't' => $t, 'page' => 1
				);
		for ($i = 1; $i <= $nbPages; $i++) {
			$params['page'] = $i;
			?>
			<li <?=($i === $page ? ' class="active"' : ''); ?>><a href="?<?=http_build_query($params); ?>"><?=$i; ?></a></li>
			<?php
		}
			?>
		</ul>
	</div>
	<?php }
}
	?>
</div>
