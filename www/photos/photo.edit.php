<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';

include $_SERVER['DOCUMENT_ROOT'] . '/../includes/photos/actions.inc.php';

$context->addCssFile("/static/css/photo.edit.css");
$context->addJsFile("/static/js/photo.edit.js");

$ids = Html::getRequest('id', false, Html::ANY);

if ($ids === false) {
	header('Location: /photos/');
	exit();
}

include $_SERVER['DOCUMENT_ROOT'] . '/../includes/start.inc.php';
$context->addJsInline('window.editedPhoto = [];');

$canGoBack = true;
if (is_array($ids) === false) {
	$ids = array(
				$ids
			);
} else {
	$canGoBack = false;
}

?>
<form method="POST" id="photo_form" class=" photoMetaForm form-horizontal">
	<div class="pull-right clearfix">
		<?php
if ($canGoBack === true) {
		?>
		<a class="btn"
			<?=($canGoBack === true ? 'onclick="javascript:window.history.go(-1);"' : 'href="/photos/photo.php?id=' . $ids[0] . '"');
			?>>Annuler</a>
		<?php
}
		?>
		<button class="btn btn-success" type="submit">Enregistrer</button>
	</div>
	<h3>Informations sur les photos</h3>
	<input type="hidden" name="token" value="<?=$_SESSION['NEXT_TOKEN']; ?>" />
	<input type="hidden" name="action" value="update" />
	<div class="bd">
		<?php
$context->addJsInline('window.photos = {};');
foreach ($ids As $id) {
	include $_SERVER['DOCUMENT_ROOT'] . '/../includes/photos/edit.inc.php';
	echo '<hr />';
}
		?>
	</div>
</form>
<?php
$context->addJsInline('checkNoPhotos();
');

include $_SERVER['DOCUMENT_ROOT'] . '/../includes/end.inc.php';

