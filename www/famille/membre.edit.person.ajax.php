<?php
$_NO_TOKEN = true;
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';
$context->setHasNavBar(false);

$id = Html::getRequestOrPost('personnes_id', false, HTML::INTEGER);
$link = Html::getRequestOrPost('link', false, HTML::TEXT);
$linked_personnes_id = Html::getRequestOrPost('linked_personnes_id', false, Html::INTEGER);

$membre = $context->getDb()->personnes[$id];

// $membre = $context->getDb()->personnes[$id];

if ($_SESSION['TOKEN'] === Html::getRequestOrPost('token', false, HTML::TEXT)) {

    if ($linked_personnes_id !== false && $id !== false && $id > 0 && $link !== false) {
        switch ($link) {
            case 'IdParent1':
            case 'IdParent2':
                $membre[$link] = ($linked_personnes_id === - 1 ? null : $linked_personnes_id);
                $membre->update();
                exit();
            case 'Conjoint':
                if ($context->getDb()->personnes[$linked_personnes_id] !== null) {
                    $context->getDb()
                        ->personnes_liens_couple()
                        ->insert(array(
                        'IdPersonne1' => $id,
                        'IdPersonne2' => $linked_personnes_id
                    ));
                }
                exit();
            case 'Enfant':
                $enfant = $context->getDb()->personnes[$linked_personnes_id];
                $membreCouples = $context->getDb()->personnes_liens_couple('IdPersonne1 = ' . $id . ' OR IdPersonne2 = ' . $id);
                if (sizeof($membreCouples) === 1) {
                    $couple = $membreCouples->fetch();
                    $membreConjoint = $context->getDb()->personnes[($couple['IdPersonne1'] != $id ? $couple['IdPersonne1'] : $couple['IdPersonne2'])];
                }
                if ($enfant != null) {
                    if ($enfant['IdParent1'] === null && $enfant['IdParent2'] != $id) {
                        $enfant['IdParent1'] = $id;
                        if ($enfant['IdParent2'] === null) {
                            $enfant['IdParent2'] = $membreConjoint['id'];
                        }
                    } elseif ($enfant['IdParent2'] === null && $enfant['IdParent1'] != $id) {
                        $enfant['IdParent2'] = $id;
                        if ($enfant['IdParent1'] === null) {
                            $enfant['IdParent1'] = $membreConjoint['id'];
                        }
                    }
                    $enfant->update();
                }
                exit();
        }
    }

    include $_SERVER['DOCUMENT_ROOT'] . '/../includes/start.inc.php';

    $others = $context->getDb()
        ->personnes('NOT id', $id)
        ->select('*, YEAR(DateNaissance) As AnneeNaissance, YEAR(DateMort) As AnneeMort');

    if (in_array($link, array(
        'IdParent1',
        'IdParent2'
    )) === true) {
        $tabParents = array();
        if ($membre['IdParent1'] !== null) {
            $tabParents[] = $membre['IdParent1'];
        }
        if ($membre['IdParent2'] !== null) {
            $tabParents[] = $membre['IdParent2'];
        }

        $others->where('NOT id', $tabParents);
    }
    if ($link == 'Enfant') {
        $others->where('NOT id', $context->getDb()
            ->personnes('IdParent1 = ? Or IdParent2 = ?', $id, $id));
    }

    $others->order('Prenom, Nom, DateNaissance');

    ?>
<input type="hidden" name="link" value="<?=$link; ?>" />
<ul>
	<?php
    foreach ($others as $o) {
        ?>
	<li><a href="javascript:setRelativePerson(null, null, null, '<?=$o['id']; ?>');"><?=$o['Prenom'] . ' ' . $o['Nom'] . (strlen($o['NomJF']) > 0 ? ' ' . $o['NomJF'] : '') . ' <i>(' . $o['AnneeNaissance'] . (strlen($o['DateMort']) > 0 ? '-' . $o['AnneeMort'] : '') . ')</i>';?>
	</a></li>
	<?php
    }
    ?>
</ul>
<?php
    include $_SERVER['DOCUMENT_ROOT'] . '/../includes/end.inc.php';
}
