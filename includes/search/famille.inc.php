<?php
$context->addCssFile('/static/css/membres.css');
?>
<?php
$res = Context::getInstance()->getDb()->personnes();
foreach (explode(' ', trim($q)) AS $elmt) {
	$res
			->where(
					'Prenom like ? Or Nom Like ? Or NomJF Like ? Or Email Like ?',
					array(
						'%' . $elmt . '%', '%' . $elmt . '%', '%' . $elmt . '%', '%' . $elmt . '%'
					));
}
$nbRes = $res->count('*');
if ($nbRes == 1) {
	$membre = $res->fetch();
	header('Location: /famille/membre.php?id=' . $membre['id']);
	exit();
} elseif ($nbRes > 0) {
	echo '<ul class="thumbnails famille-membres">';
	foreach ($res->order('Nom, Prenom') As $p) {
?>
<li class="span2">
	<div class="thumbnail" style="text-align: center;">
		<a href="/famille/membre.php?id=<?=$p['id']; ?>"> <img src="/photos/portrait.jpg.php?personnes_id=<?=$p['id']; ?>"
			class="img-polaroid portrait" style="height: 100px;" alt="Portrait <?=$p['Prenom'] . ' ' . $p['Nom']; ?>" /><br /> <span
			title="<?=$p['Prenom'] . ' ' . $p['Nom']; ?>"><?=$p['Prenom'] . ' ' . $p['Nom']; ?></span></a>
		<?php
		echo '(' . $context->getSql()->formatSqlDate($p['DateNaissance'], '%Y')
				. ($p['DateMort'] === null ? '' : ' - ' . $context->getSql()->formatSqlDate($p['DateMort'], '%Y')) . ')';
		?>
	</div>
</li>
<?php
	}
	echo '</ul>';
} else {
?>
<div class="alert alert-info">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	<h4>Aucun résultat</h4>
	Aucun membre de la famille ne correspond à votre recherche.
</div>
<?php
}
