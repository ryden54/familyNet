<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';

$context->addRequiredAuth('Gestion');

include $_SERVER['DOCUMENT_ROOT'] . '/../includes/start.inc.php';

if ($_SESSION['TOKEN'] === Html::getRequestOrPost('token', false, HTML::TEXT)) {
	$newPasses = Html::getPost('mdp', false, Html::TEXT);
	if (is_array($newPasses) === true) {
		$auths = $context->getDb()->autorisations();
		foreach ($newPasses As $id => $newPass) {
			if (strlen(trim($newPass)) > 0) {
				$auth = $auths[$id];
				if ($auth !== null) {
					$auth['MotDePasse'] = md5($newPass);
					$auth->update();
				}
			}
		}
	}
}
?>
<div class="block">
	<h3>Modifier les mots de passe</h3>
	<form class="form-horizontal bd" method="post">
		<input type="hidden" name="token" value="<?=$_SESSION['NEXT_TOKEN']; ?>" />
		<?php
foreach ($context->getDb()->autorisations() As $a) {
		?>
		<div class="control-group">
			<label class="control-label" for="inputEmail"><?=$a['label'] ?></label>
			<div class="controls">
				<input type="text" size="20" maxlength="255" name="mdp[<?=$a['id']; ?>]" value="" placeholder="Nouveau mot de passe" />
			</div>
		</div>
		<?php
}
		?>
		<div class="form-actions">
			<button type="submit" class="btn btn-primary">Enregistrer</button>
		</div>
	</form>
</div>
<?php

include $_SERVER['DOCUMENT_ROOT'] . '/../includes/end.inc.php';
