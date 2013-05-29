<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';

$tree = new Family_Tree($context->getDb()->personnes());

$tree->setRootNodeElementId(Config::get('ROOT_FAMILY_MEMBER_ID'));

$context->addCssFile('/static/css/tree.css');

function displayTree(Family_Tree_Node $node) {
	$res = '<li>';
	$res .= '<div class="node">';
	foreach ($node->getElements() As $e) {
		$res .=
				'<a class="member" href="/famille/membre.php?id=' . $e['id'] . '"><img src="/photos/portrait.jpg.php?f=mini&personnes_id='
						. $e['id'] . '" alt="" /><br/>' . $e['Prenom'] . ' ' . $e['Nom'] . '</a>';
	}
	$res .= '</div>';

	if (sizeof($node->getChilds()) > 0) {
		$res .= '<ul>';
		foreach ($node->getChilds() As $c) {
			$res .= displayTree($c);
		}
		$res .= '</ul>';
	}
	$res .= '</li>';
	return $res;
}

include $_SERVER['DOCUMENT_ROOT'] . '/../includes/start.inc.php';
?>
<!--
We will create a family tree using just CSS(3)
The markup will be simple nested lists
-->
<div class="tree">
	<ul>
		<?php
echo displayTree($tree->getRootNode());
		?>
	</ul>
</div>
<?php
include $_SERVER['DOCUMENT_ROOT'] . '/../includes/end.inc.php';
