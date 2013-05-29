<?php
$_NO_TOKEN = true;
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';
$context->setHasNavBar(false);
$context->addCssFile('/static/css/membre.css');

$id = Html::getRequest('personnes_id', false, HTML::INTEGER);

$membre = $context->getDb()->personnes[$id];

if ($_SESSION['TOKEN'] === Html::getRequestOrPost('token', false, HTML::TEXT)) {

	$zip = Html::getRequestOrPost('zip', false, Html::TEXT);
	if ($zip !== false) {
		include $_SERVER['DOCUMENT_ROOT'] . '/../includes/start.inc.php';

		$matchinsCoords =
				$context->getDb()->coordonnees('CodePostal', $zip)
						->where('id NOT IN (SELECT coordonnees_id FROM personnes_coordonnees WHERE personnes_id = ?)', $id);

		foreach ($matchinsCoords As $a) {
?>
<address class="alert alert-info" style="cursor: pointer;"
	onClick="javascript:addAdress('<?=$_SESSION['NEXT_TOKEN']; ?>', '<?=$id; ?>', '<?=$a['id']; ?>');">
	<?php
			echo nl2br($a['Adresse']);
			echo '<br>' . $a['CodePostal'] . ' ' . $a['Ville'];
			echo ($a['Pays'] != 'France' ? '<br/>' . $a['Pays'] : '');
			if (strlen(trim($a['Tel'])) > 0) {
				echo '<br/>' . $a['Tel'];
			}
	?>
</address>
<?php
		}
?>
<form method="POST">
	<input type="hidden" name="token" value="<?=$_SESSION['NEXT_TOKEN']; ?>" />
	<fieldset>
		<textarea name="coord[Adresse]" rows=2 placeholder="Adresse" required="required" class="input-xlarge"></textarea> <br />
		<input type="text" name="coord[CodePostal]" value="<?=$zip ?>" placeholder="Code postal" class="input-small" readonly="readonly" />
		<input type="text" name="coord[Ville]" value="" placeholder="Ville" required="required" class="input-large" />
		<br />
		<input type="text" name="coord[Pays]" value="France" placeholder="Pays" required="required" class="input-large" />
		<br />
		<input type="tel" name="coord[Tel]" value="" placeholder="Téléphone" class="input-medium" />
		<br />
		<input type="tel" name="coord[Fax]" value="" placeholder="Fax" class="input-medium" />
		<button type="submit" class="btn btn-primary pull-right">Enregistrer</button>
	</fieldset>
</form>
<?php
		include $_SERVER['DOCUMENT_ROOT'] . '/../includes/end.inc.php';

	}

}

