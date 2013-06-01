<div class="alert alert-info presence" id="presence-<?=$pres['id']; ?>">
	<button type="button" class="close"
		onClick="javascript:removePres(<?=$pres['id']; ?>, <?=$pres['photos_id']; ?>, <?=$pres['personnes_id']; ?>);">&times;</button>
	<button class="btn" type="button" onClick="javascript:tracePortrait(<?=$pres['photos_id']; ?>, <?=$pres['id']; ?>);">
		<img src="/photos/portrait.jpg.php?id=<?=$pres['id']; ?>&rnd=<?=rand(1, 99999);?>" alt="<?=$pres->personnes['Prenom'] . ' ' . $pres->personnes['Nom'];
																	   ?>" />
	</button>
	<strong><?=$pres->personnes['Prenom'] . ' ' . $pres->personnes['Nom']; ?></strong>
</div>