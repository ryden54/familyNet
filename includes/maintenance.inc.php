<?php
$pics = array(
			'/static/i/wip.0.png', '/static/i/wip.1.png', '/static/i/wip.2.png'
		);

?>
<h1 style="text-align:center;margin-top: 15%;">
	<img src="<?=$pics[array_rand($pics)]; ?>" alt="Work in progress..." /><br />Maintenance en cours
</h1>