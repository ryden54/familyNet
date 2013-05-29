<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';

$q = Html::getRequestOrPost('q', false, Html::TEXT);
$t = Html::getRequestOrPost('t', false, Html::TEXT);
$nbMinChars = 3;

$_SESSION['search_page'] = Html::getRequestOrPost('page', (isset($_SESSION['search_page']) ? $_SESSION['search_page'] : 1), Html::INTEGER);

if (strlen(trim($t)) === 0) {
	$t = Context_Universe::FAMILLE;
	$_SESSION['search_page'] = 1;
}

if ($q === false) {
	header('Location: /');
	exit();
}

include $_SERVER['DOCUMENT_ROOT'] . '/../includes/start.inc.php';

?>
<div class="page-header">
	<h2>
		Recherche de
		<?='"' . $q . '"'; ?>
	</h2>
</div>
<div class="block">
	<ul class="nav nav-tabs" id="memberDetails">
		<li <?=($t === Context_Universe::FAMILLE ? 'class="active"' : ''); ?>><a
			href="/search.php?q=<?=urlencode($q) ?>&t=<?=Context_Universe::FAMILLE ?>">Famille</a></li>
		<li <?=($t === Context_Universe::PHOTOS ? 'class="active"' : ''); ?>><a
			href="/search.php?q=<?=urlencode($q) ?>&t=<?=Context_Universe::PHOTOS ?>">Photos</a></li>
		<li <?=($t === Context_Universe::DISCUSSIONS ? 'class="active"' : ''); ?>><a
			href="/search.php?q=<?=urlencode($q) ?>&t=<?=Context_Universe::DISCUSSIONS ?>">Discussions</a></li>
	</ul>
	<div class="tab-content">
		<div class="tab-pane active">
			<?php
if (strlen(trim($q)) <= $nbMinChars) {
?>
<div class="alert alert-error">Merci de saisir au moins <?=$nbMinChars;?> caractères.</div>
<?php
} else {

	switch ($t)
		{
		case Context_Universe::DISCUSSIONS:
			include $_SERVER['DOCUMENT_ROOT'] . '/../includes/search/discussions.inc.php';
			break;
		case Context_Universe::PHOTOS:
			include $_SERVER['DOCUMENT_ROOT'] . '/../includes/search/photos.inc.php';
			break;
		default:
		case Context_Universe::FAMILLE:
			include $_SERVER['DOCUMENT_ROOT'] . '/../includes/search/famille.inc.php';
			break;
		}
}
			?>
		</div>
	</div>
</div>
<?php

include $_SERVER['DOCUMENT_ROOT'] . '/../includes/end.inc.php';
