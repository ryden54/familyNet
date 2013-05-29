<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';

$context->addCssFile("/static/css/photo.css");

$id = Html::getRequest('id', false, Html::INTEGER);
include $_SERVER['DOCUMENT_ROOT'] . '/../includes/start.inc.php';

if ($id !== false) {

	$photo = $context->getDb()->photos()->where('id', $id)->fetch();

?>
<div class="photo-bloc">
	<a href="/photos/photo.jpg.php?id=<?=$id; ?>"><img src="/photos/photo.jpg.php?id=<?=$id; ?>" alt="" /></a>
	<div class="legend">
		<span class="title">
			<?=$photo['Titre'] ?>
		</span>
		<span class="sender">
			Envoyée par
			<?=$photo->personnes['Prenom'] . ' ' . $photo->personnes['Nom'] . ' le '
			. Db_Sql::formatSqlDate($photo['DateUpload'], '%d/%m/%Y');
			?>
		</span>
		<span class="date">
			<?=Db_Sql::formatSqlDate($photo['DateCliche'], '%d/%m/%Y') ?>
		</span>
		<span class="place">
			<a href="https://maps.google.fr/maps?q=<?=urlencode($photo['Lieu']); ?>" target="_blank"><?=$photo['Lieu'] ?></a>
		</span>
		<span class="actions">
			<a href="/photos/photo.edit.php?id=<?=$id; ?>"><button class="btn btn-primary">Modifier</button></a>
		</span>
		<span class="presences">
			<?php

	$participants = array();
	foreach ($photo->photos_presences() As $m) {
		$participants[] = $m->personnes;
	}
	echo User::getNames($participants, true, false);
			?>
		</span>
	</div>
</div>
<?php
}
include $_SERVER['DOCUMENT_ROOT'] . '/../includes/end.inc.php';
