<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';

$nbPerPage = 24;
$_SESSION['famille_page'] =
		Html::getRequestOrPost('page', (isset($_SESSION['famille_page']) ? $_SESSION['famille_page'] : 1), Html::INTEGER);

$nbPages = ceil($context->getDb()->personnes()->count('*') / $nbPerPage);

include $_SERVER['DOCUMENT_ROOT'] . '/../includes/start.inc.php';

$context->addCssFile('/static/css/membres.css');
?>
<div class="block">
	<div class="pagination pagination-right">
		<ul>
			<?php
for ($i = 1; $i <= $nbPages; $i++) {
			?>
			<li <?=($i === $_SESSION['famille_page'] ? ' class="active"' : ''); ?>><a href="?page=<?=$i; ?>"><?=$i; ?></a></li>
			<?php
}
			?>
		</ul>
	</div>
	<ul class="thumbnails famille-membres">
		<?php
foreach ($context->getDb()->personnes()->order('Nom, Prenom')->limit($nbPerPage, ($_SESSION['famille_page'] - 1) * $nbPerPage) As $p) {
		?>
		<li class="span2">
			<div class="thumbnail" style="text-align: center;">
				<a href="/famille/membre.php?id=<?=$p['id']; ?>"> <img src="/photos/portrait.jpg.php?personnes_id=<?=$p['id']; ?>"
					class="img-polaroid portrait" style="height: 100px;" alt="Portrait <?=$p['Prenom'] . ' ' . $p['Nom']; ?>" /><br /> <span
						title="<?=$p['Prenom'] . ' ' . $p['Nom']; ?>">
						<?=$p['Prenom'] . ' ' . $p['Nom']; ?>
					</span></a>
				<?php
	echo '<span class="dates">' . $context->getSql()->formatSqlDate($p['DateNaissance'], '%Y')
			. ($p['DateMort'] === null ? '' : ' - ' . $context->getSql()->formatSqlDate($p['DateMort'], '%Y')) . '</span>';
				?>
			</div>
		</li>
		<?php
}
		?>
	</ul>
	<div class="pagination pagination-right">
		<ul>
			<?php
for ($i = 1; $i <= $nbPages; $i++) {
			?>
			<li <?=($i === $_SESSION['famille_page'] ? ' class="active"' : ''); ?>><a href="?page=<?=$i; ?>"><?=$i; ?></a></li>
			<?php
}
			?>
		</ul>
	</div>
</div>
<?php
include $_SERVER['DOCUMENT_ROOT'] . '/../includes/end.inc.php';
