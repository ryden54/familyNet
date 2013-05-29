<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';
$context->addCssFile('/static/css/home.css');
$context->addCssFile("/static/css/photo.css");

$daysPics = $context->getDb()->photos_du_jour()->order('Jour desc')->limit(7);

$birthdays =
		$context->getDb()->personnes()
				->select(
						'*, DATE_ADD(DateNaissance, INTERVAL (RIGHT(CURDATE(),5)>RIGHT(DateNaissance,5)) + YEAR(CURDATE()) - YEAR(DateNaissance) YEAR) as nextBirthday, (YEAR(CURDATE())-YEAR(DateNaissance))
    - (RIGHT(CURDATE(),5)<=RIGHT(DateNaissance,5)) +1
     AS nextAge')->where('Not DateNaissance', null)->where('DateMort', null)->where('outOfFamily', 0)->order('nextBirthday asc')->limit(12);

$missingBirthdates = $context->getDb()->personnes('DateNaissance', null);

$lastDiscussions =
		$context->getDb()->discussions_messages()
				->select(
						'discussions.*, discussions_id, Max(DateMessage) As DateLastMessage, IF(Max(DateMessage) > "' . $context->getUser()->getLastVisitDate()
								. '", 1, 0) As new')->group('discussions.id')->order("sticky desc, DateLastMessage desc")->limit(14);

$lastPhotos =
		$context->getSql()
				->fetchAll(
						'Select Count(photos.id) As Nb, Date(DateUpload) As DateUpload, personnes_id, personnes.*, IF(Max(DateUpload) >= "'
								. $context->getUser()->getLastVisitDate()
								. '", 1, 0) As new FROM photos Left Join personnes ON(personnes.id = photos.personnes_id) GROUP BY Date(DateUpload), personnes_id ORDER BY Date(DateUpload) desc, personnes_id LIMIT 7');

$lastMembres =
		$context->getDb()->personnes()->select('*, IF(DateSaisie >= "' . $context->getUser()->getLastVisitDate() . '", 1, 0) As new')
				->order('Date(DateSaisie) desc')->limit(5);

$incompleteEmails =
		$context->getDb()->personnes()->where('Email', null)->where('DateMort', null)->where('outOfFamily', 0)
				->where(
						"DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(DateNaissance, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(DateNaissance, '00-%m-%d')) >= 16")
				->order('rand()')->limit(5);

$incompleteAdresses =
		$context->getDb()->personnes()->where('NOT id', $context->getDb()->personnes_coordonnees()->select('personnes_id'))->where('DateMort', null)
				->where('outOfFamily', 0)->order('rand()')->limit(5);

include $_SERVER['DOCUMENT_ROOT'] . '/../includes/start.inc.php';
?>
<div class="row-fluid">
	<div class="span4 block" style="min-height: 140px;">
		<h3>
			<a href="/photos/">Photos</a>
		</h3>
		<div class="bd clearfix">
			<ul class="aunstyled">
				<?php
foreach ($lastPhotos As $p) {
				?>
				<li><a
					href="/photos/?datetype=DateUpload&annee=<?=substr($p['DateUpload'], 0, 4); ?>&mois=<?=substr($p['DateUpload'], 5, 2); ?>&jour=<?=substr(
			$p['DateUpload'],
			8,
			2);
																																				   ?>&sender=<?=$p['personnes_id']; ?>"><?=$p['Nb']
			. ' photo' . ($p['Nb'] > 1 ? 's' : '') . '</a> envoyées par ' . $p['Prenom'] . ' ' . $p['Nom'] . ' le '
			. Db_Sql::formatSqlDate($p['DateUpload'], '%d/%m/%Y');
																																														?>
				</a> <?php
	if ($p['new']) {
					 ?> <span class="badge badge-info">Nouveau</span> <?php
	}
																	  ?></li>
				<?php
}
				?>
			</ul>
			<?php
if (sizeof($lastPhotos) === 0) {
			?>
			<div class="alert alert-info">Aucune photo...</div>
			<?php } ?>
			<a href="/photos/upload.php" class="btn btn-success pull-right">Partager de nouvelles photos...</a>
		</div>
	</div>
	<div class="span4 block" style="min-height: 140px;">
		<h3>Famille</h3>
		<div class="bd">
			<ul class="aunstyled">
				<?php
foreach ($lastMembres As $m) {
				?>
				<li><?='<a href="/famille/membre.php?id=' . $m['id'] . '">' . $m['Prenom'] . ' ' . $m['Nom'] . '</a> a été ajouté'
			. ($m['Sexe'] === 'F' ? 'e' : '') . ' le ' . Db_Sql::formatSqlDate($m['DateSaisie'], '%d/%m/%Y');
					?>
					<?php
	if ($m['new']) {
					?> <span class="badge badge-info">Nouveau</span> <?php
	}
																	 ?></li>
				<?php
}
				?>
			</ul>
			<?php
if (sizeof($lastMembres) === 0) {
			?>
			<div class="alert alert-info">Famille vide...</div>
			<?php }

if (sizeof($incompleteAdresses) > 0) {
			?>
			<p style="color: red;">
				Adresses postales manquantes :
				<?=User::getNames($incompleteAdresses); ?>
			</p>
			<?php
}
if (sizeof($incompleteEmails) > 0) {
			?>
			<p style="color: red;">
				Les adresses email manquantes :
				<?=User::getNames($incompleteEmails); ?>
			</p>
			<?php
}
			?>
		</div>
	</div>
	<?php
$homeMessageFile = $_SERVER['DOCUMENT_ROOT'] . '/../includes/home/welcome.php';
if (file_exists($homeMessageFile) === true) {
	include $homeMessageFile;
}
	?>
	<div class="span4 pull-right">
		<div class=" block" style="min-height: 140px;">
			<h3>
				<a href="/discussions/">Derniers messages</a>
			</h3>
			<div class="bd">
				<ul class="unstyled">
					<?php
$prevY = false;
foreach ($lastDiscussions As $d) {
					?>
					<li><a href="/discussions/discussion.php?id=<?=$d['id']; ?>"> <?php
	if ($d['sticky'] == 1) {
		echo '<span class="label label-important">Important</span> ';
	}
	echo ucfirst((strlen($d['Sujet']) > 0 ? $d['Sujet'] : '[Pas de sujet]'));
																				  ?></a>
						: <?php
	if ($d['new']) {
						  ?> <span class="badge badge-info">
							<?php

		$lastMessages =
				$context->getDb()->discussions_messages('discussions_id', $d['id'])->where('DateMessage >= "' . $context->getUser()->getLastVisitDate() . '"');

		if (count($lastMessages) > 1) {
			echo count($lastMessages) . ' nouveaux messages';
		} else {
			echo '1 nouveau message';
		}
							?>
						</span> de <?php

		$posters = array();
		foreach ($lastMessages As $m) {
			$posters[$m->personnes['id']] = $m->personnes;
		}

		echo User::getNames($posters, false, false);

	} else {
		echo Db_Sql::formatSqlDate($d['DateLastMessage'], utf8_decode('%d %B %Y'));
	}
								   ?></li>
					<?php
}
					?>
				</ul>
				<?php
if (sizeof($lastDiscussions) === 0) {
				?>
				<div class="alert alert-info">Aucune discussion...</div>
				<?php } ?>
			</div>
		</div>
		<div class=" block">
			<h3>Anniversaires</h3>
			<div class="bd">
				<ul class="unstyled">
					<?php
foreach ($birthdays As $p) {
					?>
					<li><a href="/famille/membre.php?id=<?=$p['id']; ?>"><?=$p['Prenom'] . ' ' . $p['Nom'] ?></a> aura <?=$p['nextAge'] ?> ans le <?=Db_Sql::formatSqlDate(
			$p['nextBirthday'],
			"%A %d %B %Y");
																																				  ?></li>
					<?php
}
if (sizeof($missingBirthdates) > 0) {
	echo "Les dates de naissance des personnes suivantes sont manquantes :" . User::getNames($missingBirthdates);
}
					?>
				</ul>
				<?php
if (sizeof($birthdays) === 0) {
				?>
				<div class="alert alert-info">Aucune anniversaire à venir...</div>
				<?php } ?>
			</div>
		</div>
	</div>
	<div class="block span8 fit dayPhotos">
		<h3>Photo du jour</h3>
		<div class="bd">
			<?php
if (sizeof($daysPics) === 0) {
			?>
			<div class="alert alert-info">Aucune photo du jour</div>
			<?php } else { ?>
			<div id="homeCarousel" class="carousel slide">
				<ol class="carousel-indicators">
					<?php
	for ($i = 0; $i < sizeof($daysPics); $i++) {
					?>
					<li data-target="#homeCarousel" data-slide-to="<?=$i; ?>" <?=($i === 0 ? 'class="active"' : ''); ?>></li>
					<?php
	}
					?>
				</ol>
				<!-- Carousel items -->
				<div class="carousel-inner">
					<?php

	$active = false;
	foreach ($daysPics As $pdj) {
		$class = array(
					'item'
				);
		if ($active === false) {
			$active = true;
			$class[] = " active";
		}
		$photo = $pdj->photos;
					?>
					<div class="<?=implode(' ', $class); ?>">
						<div class="photo-bloc">
							<img src="/photos/photo.jpg.php?f=medium&id=<?=$pdj->photos['id']; ?>" alt="<?=$pdj->photos['Titre']; ?>" />
							<div class="legend visible top">
								<span class="title">
									<a href="/photos/photo.php?id=<?=$pdj->photos['id']; ?>"> <?=ucfirst(Db_Sql::formatSqlDate($pdj['Jour'], '%A %d %B'));
																							  ?>
									</a>
								</span>
							</div>
							<div class="legend visible bottom">
								<span class="title">
									<a href="/photos/photo.php?id=<?=$pdj->photos['id']; ?>"> <?=$photo['Titre'] ?>
									</a>
								</span>
								<span class="date">
									<?=Db_Sql::formatSqlDate($photo['DateCliche'], '%d/%m/%Y')
									?>
								</span>
								<span class="place">
									<a href="https://maps.google.fr/maps?q=<?=urlencode($photo['Lieu']); ?>" target="_blank"><?=$photo['Lieu'] ?></a>
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
					</div>
					<?php
	}
					?>
				</div>
				<!-- Carousel nav -->
				<a class="carousel-control left" href="#homeCarousel" data-slide="prev">&lsaquo;</a> <a class="carousel-control right"
					href="#homeCarousel" data-slide="next">&rsaquo;</a>
			</div>
			<?php } ?>
		</div>
	</div>
</div>
<?php
include $_SERVER['DOCUMENT_ROOT'] . '/../includes/end.inc.php';
