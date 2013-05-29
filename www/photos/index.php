<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';

$context->addCssFile('/static/css/photos.css');

$defaultFilters =
		array(
			'annee' => date('Y'), 'mois' => '', 'jour' => 0, 'dateType' => 'DateUpload', 'page' => 1, 'jour' => false, 'sender' => false
		);

if (isset($_SESSION['photos_filters']) === false) {
	$_SESSION['photos_filters'] = array();
}

$_SESSION['photos_filters'] = array_merge($defaultFilters, $_SESSION['photos_filters']);

if (($mois = Html::getRequestOrPost('mois', false, HTML::INTEGER)) !== false) {
	$_SESSION['photos_filters']['mois'] = $mois;
	$_SESSION['photos_filters']['page'] = 1;
	$_SESSION['photos_filters']['jour'] = 0;
}
if (($sender = Html::getRequestOrPost('sender', false, HTML::INTEGER)) !== false) {
	$_SESSION['photos_filters']['personnes_id'] = $sender;
	$_SESSION['photos_filters']['page'] = 1;
	$_SESSION['photos_filters']['jour'] = 0;
}
if (($type = Html::getRequestOrPost('datetype', false, HTML::TEXT)) !== false
		&& in_array($type, array(
			'DateCliche', 'DateUpload'
		)) === true) {
	$_SESSION['photos_filters']['dateType'] = $type;
	$_SESSION['photos_filters']['page'] = 1;
	$_SESSION['photos_filters']['jour'] = 0;
}
if (($annee = Html::getRequestOrPost('annee', false, HTML::INTEGER)) !== false) {
	$_SESSION['photos_filters']['annee'] = $annee;
	$_SESSION['photos_filters']['page'] = 1;
	$_SESSION['photos_filters']['jour'] = 0;
}

if (($jour = Html::getRequestOrPost('jour', false, HTML::INTEGER)) !== false) {
	$_SESSION['photos_filters']['jour'] = $jour;
	$_SESSION['photos_filters']['page'] = 1;
}
if (($newPage = Html::getRequestOrPost('page', false, HTML::INTEGER)) !== false) {
	$_SESSION['photos_filters']['page'] = $newPage;
}

$periods =
		$context->getDb()->photos()->select('Distinct Year(' . $_SESSION['photos_filters']['dateType'] . ') As Annee')
				->order($_SESSION['photos_filters']['dateType'] . ' desc');

if ($_SESSION['photos_filters']['annee'] > $periods[0]['Annee']) {
	$_SESSION['photos_filters']['annee'] = $periods[0]['Annee'];
}

// $nbPerPage = 36;

$photos =
		$context->getDb()->photos()->where('Year(' . $_SESSION['photos_filters']['dateType'] . ')', $_SESSION['photos_filters']['annee']);
if ($_SESSION['photos_filters']['mois'] > 0) {
	$photos->where('Month(' . $_SESSION['photos_filters']['dateType'] . ')', $_SESSION['photos_filters']['mois']);
}
if ($_SESSION['photos_filters']['jour'] > 0) {
	$photos->where('Day(' . $_SESSION['photos_filters']['dateType'] . ')', $_SESSION['photos_filters']['jour']);
}
if ($_SESSION['photos_filters']['sender'] > 0) {
	$photos->where('personnes_id', $_SESSION['photos_filters'][sender]);
}

$nbPages = 1;//ceil($photos->count('*') / $nbPerPage);

$photos->order($_SESSION['photos_filters']['dateType'] . " desc");
// $photos->limit($nbPerPage, ($_SESSION['photos_filters']['page'] - 1) * $nbPerPage);

include $_SERVER['DOCUMENT_ROOT'] . '/../includes/start.inc.php';
?>
<h3>
	<a href="/photos/upload.php" class="btn btn-primary pull-right"><i class="icon-plus-sign icon-white"></i>&nbsp;Envoyer de nouvelles
		photos</a>
	<form method="GET" class="form-inline">
		<fieldset>
			Date
			<select name="datetype" onChange="javascript:this.form.submit();">
				<option value="DateCliche" <?=($_SESSION['photos_filters']['dateType'] === 'DateCliche' ? ' selected="selected"' : ''); ?>>du
					cliché</option>
				<option value="DateUpload" <?=($_SESSION['photos_filters']['dateType'] === 'DateUpload' ? ' selected="selected"' : ''); ?>>d'envoi
					sur le site</option>
			</select>
			<select name="annee" onChange="javascript:this.form.submit();">
				<?php
foreach ($periods As $p) {
	echo '<option value="' . $p['Annee'] . '"'
			. (intval($p['Annee']) === $_SESSION['photos_filters']['annee'] ? ' selected="selected"' : '') . '>' . $p['Annee']
			. '</option>';
}
				?>
			</select>
			<select name="mois" onChange="javascript:this.form.submit();">
				<option value="">Tous</option>
				<?php
for ($i = 1; $i <= 12; $i++) {
	echo '<option value="' . $i . '"' . ($i === $_SESSION['photos_filters']['mois'] ? ' selected="selected"' : '') . '>'
			. Db_Sql::formatSqlDate('2000-' . $i . '-01 00:00:00', '%B') . '</option>';
}
				?>
			</select>
		</fieldset>
	</form>
</h3>
<div class="block">
	<div class="bd">
		<?php
if ($nbPages > 1) {
		?>
		<div class="pagination pagination-right">
			<ul>
				<?php
	for ($i = 1; $i <= $nbPages; $i++) {
				?>
				<li <?=($i === $_SESSION['photos_filters']['page'] ? ' class="active"' : ''); ?>><a href="?page=<?=$i; ?>"><?=$i; ?></a></li>
				<?php
	}
				?>
			</ul>
		</div>
		<?php } ?>
		<ul class="thumbnails">
			<?php
$monthPrev = false;
foreach ($photos As $p) {
	if ($monthPrev !== substr($p[$_SESSION['photos_filters']['dateType']], 0, 7)) {
		$monthPrev = substr($p[$_SESSION['photos_filters']['dateType']], 0, 7);
			?>
		</ul>
		<ul class="thumbnails">
			<h4>
				<?=ucfirst(Db_Sql::formatSqlDate($p[$_SESSION['photos_filters']['dateType']], '%B %Y')); ?>
			</h4>
			<?php
	}
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
	for ($i = 1; $i <= $nbPages; $i++) {
				?>
				<li <?=($i === $_SESSION['photos_filters']['page'] ? ' class="active"' : ''); ?>><a href="?page=<?=$i; ?>"><?=$i; ?></a></li>
				<?php
	}
				?>
			</ul>
		</div>
		<?php }
		?>
	</div>
</div>
<?php
include $_SERVER['DOCUMENT_ROOT'] . '/../includes/end.inc.php';
