<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';

include $_SERVER['DOCUMENT_ROOT'] . '/../includes/start.inc.php';

$context->addCssFile('/static/css/discussion.css');

require_once $_SERVER['DOCUMENT_ROOT'] . '/../libs/NotORM/NotORM.php';

$db = $context->getDb();

$id = Html::getRequestOrPost('id', false, Html::INTEGER);

$_SESSION['discussion_page'] = Html::getRequestOrPost('page', (isset($_SESSION['discussion_page']) ? $_SESSION['discussion_page'] : 1), Html::INTEGER);

$edit = Html::getRequestOrPost('edit', false, Html::INTEGER);

$discussion = $db->discussions[$id];

$message = Html::getPost('message', false, Html::ANY);
$res = false;
if ($_SESSION['TOKEN'] === Html::getRequestOrPost('token', false, HTML::TEXT)) {

	if (is_array($message) === true && isset($message['discussions_id']) === true && Html::getPost('message[message]', false, Html::TEXT) !== false
			&& ($d_id = Html::getPost('message[discussions_id]', false, Html::INTEGER)) !== false) {
		$m_id = Html::getPost('message[id]', false, Html::INTEGER);

		if ($d_id === 0) {

			$discussion =
					array(
							'discussions_categories_id' => Html::getPost('message[discussions_categories_id]', false, Html::INTEGER),
							'Sujet' => Html::getPost('message[Sujet]', false, Html::HTML),
							'personnes_id' => $context->getUser()->getId(),
							'CreateDate' => Db_Sql::getNowString(true),
					);
			$discussion = $db->discussions()->insert($discussion);

			$id = $d_id = $discussion['id'];
			$_SERVER['REQUEST_URI'] = '/discussions/discussion.php?id=' . $id;
		}

		if ($m_id !== 0) {

			$lastMessage = $db->discussions_messages()->where('discussions_id', $d_id)->order("DateMessage desc")->limit(1)->fetch();

			$editable =
					(intval($lastMessage['id']) === $m_id && $lastMessage['personnes_id'] === $context->getUser()->getId())
							|| $context->getUser()->hasAuth('Gestion') === true;

			if ($editable === false) {
?>
<div class="alert alert-error">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	<h4>Action interdite</h4>
	Vous ne disposez pas des droits nécessaires pour modifier ce message.
</div>
<?php

			} else {
				$message = $db->discussions_messages()->where('id = "' . $m_id . '"')[$m_id];
				$message['Message'] = Html::getPost('message[message]', false, Html::HTML);
				$res = $message->update();
			}

		} else {

			$res =
					$db->discussions_messages()
							->insert(
									array(
											'discussions_id' => $d_id,
											'DateMessage' => Db_Sql::getNowString(),
											'personnes_id' => $context->getUser()->getId(),
											'Message' => Html::getPost('message[message]', false, Html::HTML),
									));

		}
		if ($res !== false) {
			header('Location: ' . $_SERVER['REQUEST_URI']);
			exit;
		}
	} elseif (($m_id = Html::getRequestOrPost('deleteMessage', false, HTML::INTEGER)) !== false) {

		$lastMessage = $db->discussions_messages()->where('discussions_id', $id)->order("DateMessage desc")->limit(1)->fetch();

		$editable =
				(intval($lastMessage['id']) === intval($m_id) && intval($lastMessage['personnes_id']) === intval($context->getUser()->getId()))
						|| $context->getUser()->hasAuth('Gestion') === true;

		if ($editable === false) {
?>
<div class="alert alert-error">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	<h4>Action interdite</h4>
	Vous ne disposez pas des droits nécessaires pour effacer ce message.
</div>
<?php

		} else {
			$res = $db->discussions_messages()->where('id = "' . $m_id . '"')[$m_id]->delete();

			if ($discussion->discussions_messages()->count() === 0) {
				$discussion->delete();
				header('Location: /discussions/');
				exit;
			}
		}
		if ($res !== false) {
			header('Location: ?id=' . $id);
			exit;
		}

	} elseif (Html::getRequestOrPost('sticky', null, Html::BOOLEAN) !== null && Context::getInstance()->getUser()->hasAuth('Gestion') === true) {
		$discussion['sticky'] = (Html::getRequestOrPost('sticky', null, Html::BOOLEAN) === true ? 1 : 0);
		$discussion->update();
	}
	$discussion = $db->discussions[$id];
}

function showForm($d, $m = null) {

	Context::getInstance()->addCssFile('/static/libs/bootstrap-wysihtml5/dist/bootstrap-wysihtml5-0.0.2.css', 'F');
	Context::getInstance()->addJsFile('/static/libs/bootstrap-wysihtml5/lib/js/wysihtml5-0.3.0.min.js', 'G');
	Context::getInstance()->addJsFile('/static/libs/bootstrap-wysihtml5/dist/bootstrap-wysihtml5-0.0.2.min.js', 'G');

?>
<form method="post" class="clearfix" action="/discussions/discussion.php?id=<?=$d['id']; ?>">
	<input type="hidden" name="token" value="<?=$_SESSION['NEXT_TOKEN']; ?>" />
	<input type="hidden" name="message[discussions_id]" value="<?=$d['id'] ?>" />
	<input type="hidden" name="message[id]" value="<?=($m === null ? 0 : $m['id']); ?>" />
	<div class="formMessageArea clearfix">
		<div class="toolbar">
			<a class="btn btn-inverse" href="?id=<?=$d['id']; ?>">Annuler</a>
			<input type="submit" value="Valider" class="btn btn-success" />
		</div>
		<textarea id="messageEditArea<?=($m === null ? 0 : $m['id']); ?>" name="message[message]" rows="10" required="required"><?php
	echo '' . ($m === null ? '' : $m['Message']) . '';
																																?></textarea>
	</div>
	<?php
	Context::getInstance()->addJsInline("$('#messageEditArea" . ($m === null ? 0 : $m['id']) . "').wysihtml5();");
	?>
</form>
<?php

}

?>
<?php
if ($discussion === null) {
	Context::getInstance()->addCssFile('/static/libs/bootstrap-wysihtml5/bootstrap-wysihtml5-0.0.2.css', 'F');
	Context::getInstance()->addJsFile('/static/libs/bootstrap-wysihtml5/libs/js/wysihtml5-0.3.0_rc2.min.js', 'G');
	Context::getInstance()->addJsFile('/static/libs/bootstrap-wysihtml5/bootstrap-wysihtml5-0.0.2.js', 'G');

?>
<form method="post" class="clearfix" action="/discussions/discussion.php?id=0">
	<input type="hidden" name="token" value="<?=$_SESSION['NEXT_TOKEN']; ?>" />
	<input type="hidden" name="message[discussions_id]" value="0" />
	<input type="hidden" name="message[id]" value="0" />
	<div class="discussion block">
		<h3>
			<input type="text" name="message[Sujet]" value="" required="required" placeholder="Titre de la discussion..." class="input-xxlarge" />
		</h3>
		Indiquer une catégorie
		<select name="message[discussions_categories_id]" required="required">
			<option value=""></option>
			<?php
	foreach ($context->getDb()->discussions_categories()->order('categorie') As $cat) {
			?>
			<option value="<?=$cat['id']; ?>">
				<?=$cat['categorie']; ?>
			</option>
			<?php
	}
			?>
		</select>
		<table class="table table-bordered table-hover">
			<tbody>
				<tr>
					<td class="message clearfix">
						<div class="meta">
							<span class="date">
								<?=date('d/m/Y H:i') ?>
							</span>
							<br />
							<span>
								<?=$context->getUser()->getDatas()['Prenom'] . ' ' . $context->getUser()->getDatas()['Nom'] . '</a>';
								?>
								</a>
							</span>
						</div>
						<div class="formMessageArea clearfix">
							<div class="toolbar">
								<a class="btn btn-inverse" href="/discussions/">Annuler</a>
								<input type="submit" value="Valider" class="btn btn-success" />
							</div>
							<textarea id="messageEditArea0" name="message[message]" rows="10" required="required"></textarea>
							<?php
	Context::getInstance()->addJsInline("$('#messageEditArea0').wysihtml5();");
							?>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</form>
<?php

} else {
?>
<div class="discussion block">
	<h3>
		<i><?=$discussion->discussions_categories['categorie']; ?></i> /
		<?=ucfirst(nl2br($discussion['Sujet'])); ?>
		<div class="toolbar">
			<a href="?id=<?=$id ?>&edit=0" class="btn btn-success">Répondre</a>
		</div>
	</h3>
	<?php
	$nbPerPage = 20;
	$messages = $db->discussions_messages()->where('discussions_id', $id);
	$nbPages = ceil($messages->count('*') / $nbPerPage);

	if ($nbPages > 1) {
	?>
	<div class="pagination pagination-right">
		<ul>
			<?php
		for ($i = 1; $i <= $nbPages; $i++) {
			?>
			<li <?=($i === $_SESSION['discussion_page'] ? ' class="active"' : ''); ?>><a href="?id=<?=$id; ?>&page=<?=$i; ?>"><?=$i; ?></a></li>
			<?php
		}
			?>
		</ul>
	</div>
	<?php
	}

	$lastMessage = $messages->order("DateMessage desc")->limit(1)->fetch();

	if (Context::getInstance()->getUser()->hasAuth('Gestion') === true) {
	?>
	<div class="alert">
		Importance de la discussion : <a
			href="?id=<?=$id ?>&sticky=<?=($discussion['sticky'] == 1 ? '0' : '1'); ?>&token=<?=$_SESSION['NEXT_TOKEN']; ?>"><span
				class="label <?=($discussion['sticky'] == 1 ? 'label-important' : 'label.default'); ?>">
				<?=($discussion['sticky'] == 1 ? 'Important' : 'Normal'); ?>
			</span></a>
	</div>
	<?php } ?>
	<table class="table table-bordered table-hover">
		<tbody>
			<?php

	if ($edit === 0) {

			?>
			<tr>
				<td class="message clearfix">
					<div class="meta">
						<span class="date">
							<?=date('d/m/Y H:i') ?>
						</span>
						<br />
						<span>
							<?=$context->getUser()->getDatas()['Prenom'] . ' ' . $context->getUser()->getDatas()['Nom'] . '</a>';
							?>
							</a>
						</span>
					</div> <?php
		showForm($discussion, null);
						   ?>
				</td>
			</tr>
			<?php
	}
	foreach ($messages->order("DateMessage desc")->limit($nbPerPage, ($_SESSION['discussion_page'] - 1) * $nbPerPage) As $m) {
		$m['id'] = intval($m['id']);
		$editable =
				(intval($lastMessage['id']) === intval($m['id']) && intval($m['personnes_id']) === intval($context->getUser()->getId()))
						|| $context->getUser()->hasAuth('Gestion') === true;

		$editing = ($editable === true && $m['id'] === $edit);
			?>
			<tr>
				<td class="message clearfix">
					<div class="meta">
						<span class="date">
							<?=Db_Sql::formatSqlDate($m['DateMessage'], '%d/%m/%Y %H:%M'); ?>
						</span>
						<br /> <a href="/famille/membre.php?id=<?=$m['personnes_id']; ?>"> <?php
		if ($editing === false) {
																						   ?>
							<img class="img-polaroid portrait" src="/photos/portrait.jpg.php?personnes_id=<?=$m['personnes_id']; ?>" style="width:100px;"
							alt="Portrait <?=$m->personnes['Prenom'] . ' ' . $m->personnes['Nom']; ?>" /><br /> <?php
		}
																												?> <span>
								<?=$m->personnes['Prenom'] . ' ' . $m->personnes['Nom'] . '</a>'; ?></a>
						</span>
					</div>
					<div class="toolbar">
						<?php
		if ($editing === false && $editable === true) {
						?>
						<div id="deleteMessage<?=$m['id']; ?>Modal" class="modal hide fade" tabindex="-1" role="dialog">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">�</button>
								<h3 id="myModalLabel">Confirmer l'effacement</h3>
							</div>
							<div class="modal-body">
								<p>
									Effacer le message en date du
									<?=Db_Sql::formatSqlDate($m['DateMessage'], '%d/%m/%Y %H:%M'); ?>
									?
								</p>
							</div>
							<div class="modal-footer">
								<button class="btn" data-dismiss="modal" aria-hidden="true">Annuler</button>
								<a href="?id=<?=$id; ?>&deleteMessage=<?=$m['id']; ?>&token=<?=$_SESSION['NEXT_TOKEN']; ?>" class="btn btn-danger">Confirmer</a>
							</div>
						</div>
						<a class="btn btn-danger" data-toggle="modal" role="button" href="#deleteMessage<?=$m['id']; ?>Modal">Supprimer</a> <a
							href="?id=<?=$id; ?>&edit=<?=$m['id']; ?>" class="btn">Modifier</a>
						<?php
		}
						?>
					</div> <?php
		if ($editing === true) {
			showForm($discussion, $m);
		} else {
						   ?>
					<p align="justify">
						<?=nl2br(stripslashes($m['Message'])); ?>
					</p> <?php
		}
						 ?>
				</td>
			</tr>
			<?php
	}
			?>
		</tbody>
	</table>
	<?php
	if ($nbPages > 1) {
	?>
	<div class="pagination pagination-right">
		<ul>
			<?php
		for ($i = 1; $i <= $nbPages; $i++) {
			?>
			<li <?=($i === $_SESSION['discussion_page'] ? ' class="active"' : ''); ?>><a href="?id=<?=$id; ?>&page=<?=$i; ?>"><?=$i; ?></a></li>
			<?php
		}
			?>
		</ul>
	</div>
	<?php
	}
	?>
</div>
<?php
}
?>
<?php
include $_SERVER['DOCUMENT_ROOT'] . '/../includes/end.inc.php';
