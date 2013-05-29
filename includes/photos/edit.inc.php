<?php
if ($id !== false) {
	$context
			->addJsInline(
					'window.photos[' . $id
							. '] = {presences:{available: [], present: [], searching:null, drawing: null, selection: null}};');

	if ($_SESSION['TOKEN'] === Html::getRequestOrPost('token', false, HTML::TEXT)) {
		$photosEdit = Html::getPost('photos', false, Html::ANY);
		foreach ($photosEdit As $idEdit => $photoEdit) {
			$p = $context->getDb()->photos[$id];

			if (is_array($photoEdit) === true) {
				foreach ($photoEdit AS $key => $val) {
					if (in_array($key, array(
						'Titre', 'Lieu', 'Commentaire', 'DateCliche'
					)) === true) {
						$val = trim($val);
						if (strlen($val) === 0) {
							$val = null;
						}
						$p[$key] = $val;
					}
				}

				$p->update();
			}
		}
	}

	$p = $context->getDb()->photos[$id];
	if ($p !== null) {
		$context->addJsInline('window.editedPhoto.push(' . $id . ');');

?>
<div class="row-fluid clearfix photo-edit" id="photo-edit-<?=$id; ?>">
	<div class="span6 photo" id="photo-<?=$id; ?>">
		<img src="/photos/photo.jpg.php?id=<?=$id; ?>" alt="" class="img-polaroid" />
	</div>
	<div class="span3">
		<h5>Présences</h5>
		<div class="presences">
			<div id="photo-edit-<?=$id; ?>-newpres"></div>
			<?php
		foreach ($p->photos_presences() AS $pres) {
			include $_SERVER['DOCUMENT_ROOT'] . '/../includes/photos/presence.inc.php';
			$context->addJsInline('window.photos[' . $id . '].presences.present.push(' . $pres['personnes_id'] . ');');
		}
			?>
		</div>
		<div class="alert clearfix noPresenceAlert">
			<h5>Aucune présence mentionnée sur cette photo.</h5>
			Tracez des rectangles avec la souris autour des visages sur la photo ou renseignez dans le champ ci-dessus les présences.
			<!-- 				<button class="btn btn-info" type="button" onClick="javascript:detectFaces(<?=$p['id']; ?>);">Détecter les visages</button>  -->
		</div>
		<?php

		$available = array();
		foreach ($context->getDb()->personnes() As $personne) {
			$available[] = array(
						'id' => $personne['id'], 'name' => ($personne['Prenom'] . ' ' . $personne['Nom'])
					);
		}

		$context
				->addJsInline(
						"window.photos[" . $id . "].presences.available = JSON.parse('"
								. json_encode($available, JSON_HEX_QUOT | JSON_HEX_APOS | JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE)
								. "');
");
		$context
				->addJsInline(
						"
		            window.photos[" . $id . "].presences.box =  $('#photo-edit-" . $id
								. "-newpres').magicSuggest({
		                data: window.photos[" . $id
								. "].presences.available,
						allowFreeEntries: false,
						minChars: 1,
						cls: 'magicbox',
		                sortOrder: 'name',
		                maxResults: false,
		                selectionPosition: 'inner',
						strictSuggest: true,
						emptyTextstring: 'Ajouter une présence...',
						typeDelay: 50,
						hideTrigger: true,
						highlight: false,
						maxSelection: 1,
						renderer: function(pos){
                    return '<div class=\"proposition\"><img src=\"/photos/portrait.jpg.php?personnes_id='+ pos.id +'\" alt=\"\" /><span>' +
                            pos.name
                           '</span></div>';
                },
		                maxDropHeight: 200
		            });");

		$context
				->addJsInline(
						"
						$(window.photos[" . $id
								. "].presences.box).bind('selectionchange', function(event, box, selection) {
								console.log('selectionchange');
								console.log(selection, selection.length, (selection.length > 0));
								if(selection.length > 0 && selection[0] !== undefined) {
									console.log(selection[0]);
									addPresence(" . $id
								. ", selection[0].id);
									box.clear();
									$(box).focus();
								}
						});
								checkPresences(" . $id . ");
		");

		$context
				->addJsInline(
						"
watchRect($('#photo-" . $id . "'), " . $id . ");

$('#photo-" . $id . " img').on('faceTraced', function(event, data) {
window.photos[" . $id . "].presences.drawing = data;
console.log('Face traced : ', window.photos[" . $id . "].presences.drawing);
tracePortrait(" . $id . ");
	});
");
		?>
		<script>
		</script>
	</div>
	<div class="span3 photo-infos">
		<h5>Informations</h5>
		<input type="text" placeholder="Titre" value="<?=$p['Titre']; ?>" name="photos[<?=$p['id']; ?>][Titre]" />
		<input type="date" value="<?=$p['DateCliche']; ?>" name="photos[<?=$p['id']; ?>][DateCliche]" />
		<input type="text" placeholder="Lieu" value="<?=$p['Lieu']; ?>" name="photos[<?=$p['id']; ?>][Lieu]" />
		<textarea name="photos[<?=$p['id']; ?>][Commentaire]" rows="2" placeholder="Commentaires"><?=Html::escape($p['Commentaire']);
																								  ?></textarea>
		<?php
		if ($p['personnes_id'] == $context->getUser()->getId() || $context->getUser()->hasAuth('Gestion') === true) {
		?>
		<hr />
		<a class="btn btn-danger pull-right" onclick="javascript:deletePicture(<?=$p['id']; ?>);">Retirer cette photo</a>
		<?php }
		?>
	</div>
</div>
<?php
	}
}
