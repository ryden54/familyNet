<?php
$defaultDate = false;
if ($date === null) {
	$date = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d') - 30, date('Y')));
	$defaultDate = true;
}

echo '<h2>Bonjour ' . $personne['Prenom'] . ',</h2>';

echo 'Voici l\'actualité du site que tu as manqué depuis le ' . Db_Sql::formatSqlDate($date, '%A %d %B') . ".<br/><br/>";

//	Photos du jour
$daysPics = $context->getDb()->photos_du_jour()->where('Jour > "' . $date . '"')->where('Jour >= DATE_SUB(NOW(), INTERVAL 7 DAY)')->order('Jour desc');

if (sizeof($daysPics) > 0) {
?>
<div class="photos"
	style="border: 1px solid #4a931f; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; -webkit-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.065); -moz-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.065); box-shadow: 0 1px 4px rgba(0, 0, 0, 0.065); margin: 0 5px 5px 0; padding-bottom: 10px;">
	<h4
		style="line-height: 30px; color: #c6ff10; background-color: #79da40; background-image: -moz-linear-gradient(top, #87e84f, #63c629); background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#87e84f), to(#63c629)); background-image: -webkit-linear-gradient(top, #87e84f, #63c629); background-image: -o-linear-gradient(top, #87e84f, #63c629); background-image: linear-gradient(to bottom, #87e84f, #63c629); background-repeat: repeat-x; filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff87e84f', endColorstr='#ff63c629', GradientType=0); margin: 0px; padding: 0px 10px; overflow: auto;">Dernières
		photos du jour</h4>
	<div style="margin-bottom: 10px; overflow: overlay;">
		<?php
	foreach ($daysPics AS $pdj) {
		$photo = $pdj->photos;

		$images[$pdj['photos_id']] = md5(rand());

		?>
		<div
			style="float: left; text-align: center; vertical-align: middle; display: block; padding: 4px; line-height: 20px; border: 1px solid #ddd; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; -webkit-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.055); -moz-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.055); box-shadow: 0 1px 3px rgba(0, 0, 0, 0.055); margin: 10px 0 0 10px;">
			<div style="display: inline-block;">
				<a href="http://<?=Config::getHost(); ?>/photos/photo.php?id=<?=$pdj['photos_id']; ?>" style="color: white;"> <img
					style="height: 150px;"
					src="http://<?=Config::getHost(); ?>/photos/photo_letter.jpg.php?letter_id=<?=$newsletters_id; ?>&hash=<?=$images[$pdj['photos_id']];
																														   ?>"
					alt="<?=$pdj->photos['Titre']; ?>" />
					<div style="text-align: center; background-color: rgba(77, 77, 77, 0.8); color: white; padding: 0 4px;">
						<?=ucfirst(Db_Sql::formatSqlDate($pdj['Jour'], '%A %d %B')); ?>
					</div>
				</a>
			</div>
		</div>
		<?php
	}
		?>
		<div style="clear: both;"></div>
	</div>
</div>
<br />
<?php
}

//	Derniers membres ajoutés
$lastMembres = $context->getDb()->personnes()->where('DateSaisie >= "' . $date . '"')->order('Date(DateSaisie) desc');
if (sizeof($lastMembres) > 0) {
?>
<div
	style="border: 1px solid #4a931f; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; -webkit-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.065); -moz-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.065); box-shadow: 0 1px 4px rgba(0, 0, 0, 0.065); margin: 0 5px 5px 0; padding-bottom: 10px;">
	<h4
		style="line-height: 30px; color: #c6ff10; background-color: #79da40; background-image: -moz-linear-gradient(top, #87e84f, #63c629); background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#87e84f), to(#63c629)); background-image: -webkit-linear-gradient(top, #87e84f, #63c629); background-image: -o-linear-gradient(top, #87e84f, #63c629); background-image: linear-gradient(to bottom, #87e84f, #63c629); background-repeat: repeat-x; filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff87e84f', endColorstr='#ff63c629', GradientType=0); margin: 0px; padding: 0px 10px; overflow: auto;">Nouveaux
		membres de la famille</h4>
	<div style="padding: 5px;">
		<ul style="list-style: none; padding: 0px; margin: 0px;">
			<?php
	foreach ($lastMembres As $p) {
			?>
			<li><?='<a href="http://' . Config::getHost() . '/famille/membre.php?id=' . $p['id'] . '">' . $p['Prenom'] . ' ' . $p['Nom'] . '</a> a été ajouté'
				. ($p['Sexe'] === 'F' ? 'e' : '') . ' le ' . Db_Sql::formatSqlDate($p['DateSaisie'], '%d/%m/%Y');
				?></li>
			<?php
	}
			?>
		</ul>
	</div>
</div>
<br />
<?php
}

//	Photos
$lastPhotos =
		$context->getSql()
				->fetchAll(
						'Select Count(photos.id) As Nb, Date(DateUpload) As DateUpload, personnes_id, personnes.* FROM photos Left Join personnes ON(personnes.id = photos.personnes_id) WHERE DateUpload > "'
								. $date . '" GROUP BY Date(DateUpload), personnes_id ORDER BY Date(DateUpload) desc, personnes_id');

if (sizeof($lastPhotos) > 0) {
?>
<div class="photos"
	style="border: 1px solid #4a931f; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; -webkit-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.065); -moz-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.065); box-shadow: 0 1px 4px rgba(0, 0, 0, 0.065); margin: 0 5px 5px 0; padding-bottom: 10px;">
	<h4
		style="line-height: 30px; color: #c6ff10; background-color: #79da40; background-image: -moz-linear-gradient(top, #87e84f, #63c629); background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#87e84f), to(#63c629)); background-image: -webkit-linear-gradient(top, #87e84f, #63c629); background-image: -o-linear-gradient(top, #87e84f, #63c629); background-image: linear-gradient(to bottom, #87e84f, #63c629); background-repeat: repeat-x; filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff87e84f', endColorstr='#ff63c629', GradientType=0); margin: 0px; padding: 0px 10px; overflow: auto;">Partage
		de photos</h4>
	<div style="padding: 5px;">
		<ul style="padding-left: 20px; margin: 0px;">
			<?php
	foreach ($lastPhotos As $p) {
			?>
			<li><a
				href="http://<?=Config::getHost();?>/photos/?datetype=DateUpload&annee=<?=substr($p['DateUpload'], 0, 4); ?>&mois=<?=substr($p['DateUpload'], 5, 2); ?>&jour=<?=substr(
				$p['DateUpload'],
				8,
				2);
																																			   ?>&sender=<?=$p['personnes_id']; ?>"><?=$p['Nb']
				. ' photo' . ($p['Nb'] > 1 ? 's' : '') . '</a> envoyées par ' . $p['Prenom'] . ' ' . $p['Nom'] . ' le '
				. Db_Sql::formatSqlDate($p['DateUpload'], '%d/%m/%Y');
																																													?>
			</a></li>
			<?php
	}
			?>
		</ul>
	</div>
</div>
<br />
<?php
}

//	Derniers messages
$lastMessages = $context->getDb()->discussions_messages()->where('DateMessage >= "' . $date . '"')->order("discussions.sticky desc, DateMessage desc");
$last = array();
foreach ($lastMessages As $m) {
	if (isset($last[$m['discussions_id']]) === false) {
		$last[$m['discussions_id']] = array();
		$last[$m['discussions_id']]['discussion'] = $m->discussions;
		$last[$m['discussions_id']]['DateLastMessage'] = $m['DateMessage'];
		$last[$m['discussions_id']]['messages'] = array();
	}
	$last[$m['discussions_id']]['messages'][$m['id']] = $m;
	$last[$m['discussions_id']]['posters'][$m->personnes['id']] = $m->personnes;
}

if (sizeof($last) > 0) {
?>
<div
	style="border: 1px solid #4a931f; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; -webkit-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.065); -moz-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.065); box-shadow: 0 1px 4px rgba(0, 0, 0, 0.065); margin: 0 5px 5px 0; padding-bottom: 10px;">
	<h4
		style="line-height: 30px; color: #c6ff10; background-color: #79da40; background-image: -moz-linear-gradient(top, #87e84f, #63c629); background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#87e84f), to(#63c629)); background-image: -webkit-linear-gradient(top, #87e84f, #63c629); background-image: -o-linear-gradient(top, #87e84f, #63c629); background-image: linear-gradient(to bottom, #87e84f, #63c629); background-repeat: repeat-x; filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff87e84f', endColorstr='#ff63c629', GradientType=0); margin: 0px; padding: 0px 10px; overflow: auto;">Dernières
		discussions</h4>
	<div style="padding: 5px;">
		<ul style="padding-left: 20px; margin: 0px;">
			<?php
	$prevY = false;
	foreach ($last As $d) {
			?>
			<li><a href="http://<?=Config::getHost(); ?>/discussions/discussion.php?id=<?=$d['discussion']['id']; ?>"> <?php
		if ($d['discussion']['sticky'] == 1) {
			echo '<span style="background-color:#b94a48;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;display: inline-block;padding: 2px 4px;font-size: 11.844px;font-weight: bold;line-height: 14px;color: #ffffff;vertical-align: baseline;white-space: nowrap;text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);">Important</span> ';
		}
		echo ucfirst((strlen($d['discussion']['Sujet']) > 0 ? stripslashes($d['discussion']['Sujet']) : '[Pas de sujet]'));
																													   ?></a>
				: <?php

		$lastMessages = $d['messages'];

		if (count($lastMessages) > 1) {
			echo count($lastMessages) . ' nouveaux messages';
		} else {
			echo '1 nouveau message';
		}
				  ?>
				de <?php

		echo User::getNames($d['posters'], false, false);
				   ?></li>
			<?php
	}
			?>
		</ul>
		<?php
}
		?>
	</div>
</div>
<br />
<?php

//	Anniversaires
$birthdays =
		$context->getDb()->personnes()
				->select(
						'*, DATE_ADD(DateNaissance, INTERVAL (RIGHT(CURDATE(),5)>RIGHT(DateNaissance,5)) + YEAR(CURDATE()) - YEAR(DateNaissance) YEAR) as nextBirthday, (YEAR(CURDATE())-YEAR(DateNaissance))
    - (RIGHT(CURDATE(),5)<=RIGHT(DateNaissance,5)) +1
     AS nextAge')->where('Not DateNaissance', null)->where('DateMort', null)->where('outOfFamily', 0)->order('nextBirthday asc')->limit(15);

if (sizeof($birthdays) > 0) {
?>
<div
	style="border: 1px solid #4a931f; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; -webkit-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.065); -moz-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.065); box-shadow: 0 1px 4px rgba(0, 0, 0, 0.065); margin: 0 5px 5px 0; padding-bottom: 10px;">
	<h4
		style="line-height: 30px; color: #c6ff10; background-color: #79da40; background-image: -moz-linear-gradient(top, #87e84f, #63c629); background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#87e84f), to(#63c629)); background-image: -webkit-linear-gradient(top, #87e84f, #63c629); background-image: -o-linear-gradient(top, #87e84f, #63c629); background-image: linear-gradient(to bottom, #87e84f, #63c629); background-repeat: repeat-x; filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff87e84f', endColorstr='#ff63c629', GradientType=0); margin: 0px; padding: 0px 10px; overflow: auto;">Anniversaires
		à venir</h4>
	<div style="padding: 5px;">
		<ul style="list-style: none; padding: 0px; margin: 0px;">
			<?php
	foreach ($birthdays As $p) {
			?>
			<li><a href="http://<?=Config::getHost(); ?>/famille/membre.php?id=<?=$p['id']; ?>"><?=$p['Prenom'] . ' ' . $p['Nom'] ?></a> aura <?=$p['nextAge'] ?>
				ans le <?=Db_Sql::formatSqlDate($p['nextBirthday'], "%A %d %B %Y");
					   ?></li>
			<?php
	}
			?>
		</ul>
	</div>
</div>
<br />
<?php
}

