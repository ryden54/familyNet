<?php
$_NO_TOKEN = true;
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';
$context->setHasNavBar(false);

$id = Html::getRequest('personnes_id', false, HTML::INTEGER);

$membre = $context->getDb()->personnes[$id];

if ($_SESSION['TOKEN'] === Html::getRequestOrPost('token', false, HTML::TEXT)) {

    $data = array_merge(array(
        'zip' => false,
        'city' => false,
        'addr' => false,
        'tel' => false,
        'fax' => false,
        'country' => false
    ), json_decode(Html::getRequestOrPost('data', false, Html::ANY), true));
    if (strlen($data['zip']) >= 4) {

        $matchinsCoords = $context->getDb()
            ->coordonnees('CodePostal', $data['zip'])
            ->where('id NOT IN (SELECT coordonnees_id FROM personnes_coordonnees WHERE personnes_id = ?)', $id);

        if (count($matchinsCoords) <= 0 && strlen($data['zip']) >= 4 && strlen($data['city']) >= 3 && strlen($data['addr']) >= 3) {
        	?>
        	<div class="progress progress-striped active"><div class="bar" style="width: 100%;"></div></div>
            <script>
            $('#newAddrForm').submit();
            </script>
            <?php
        } else {

            include $_SERVER['DOCUMENT_ROOT'] . '/../includes/start.inc.php';
            foreach ($matchinsCoords as $a) {
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
		<textarea name="coord[Adresse]" rows=2 placeholder="Adresse" required="required" class="input-xlarge"><?=$data['addr']; ?></textarea> <br />
		<input type="text" name="coord[CodePostal]" value="<?=$data['zip']; ?>" placeholder="Code postal" class="input-small" readonly="readonly" />
		<input type="text" name="coord[Ville]" value="<?=$data['city']; ?>" placeholder="Ville" required="required" class="input-large" />
		<br />
		<input type="text" name="coord[Pays]" value="<?=$data['country']; ?>" placeholder="Pays" required="required" class="input-large" />
		<br />
		<input type="tel" name="coord[Tel]" value="<?=$data['tel']; ?>" placeholder="Téléphone" class="input-medium" />
		<br />
		<input type="tel" name="coord[Fax]" value="<?=$data['fax']; ?>" placeholder="Fax" class="input-medium" />
		<button type="submit" class="btn btn-primary pull-right">Enregistrer</button>
	</fieldset>
</form>
<?php
            include $_SERVER['DOCUMENT_ROOT'] . '/../includes/end.inc.php';
        }
    }
}

