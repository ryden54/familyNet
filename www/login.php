<?php
$_NO_USER_NEEDED = true;
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/main.inc.php';
$errorMsg = false;
if ($_SESSION['TOKEN'] === Html::getRequestOrPost('token', false, HTML::TEXT)) {
	if (Html::getPost("login[user]", false) !== false && Html::getPost("login[pass]", false) !== false) {

		$md5Pass = md5(Html::getPost("login[pass]", false, HTML::ANY));
		$login = $context->loginUser(Html::getPost("login[user]", false), $md5Pass);
		if ($login instanceof User) {

			if (Html::getPost('login[remember]', false, HTml::BOOLEAN) === true) {
				$context->memorizeUser($login, $md5Pass);
			}

			header('Location: ' . Html::getPost('org', '/', Html::TEXT) . '#connected');
			exit;
		} elseif ($login instanceof Exception) {
			$errorMsg = $login->getMessage();
		}
	}
}
include $_SERVER['DOCUMENT_ROOT'] . '/../includes/start.inc.php';
?>
<style>
body {
	background-color: #f5f5f5;
}

.form-signin {
	max-width: 300px;
	padding: 19px 29px 29px;
	margin: 0 auto 20px;
	background-color: #fff;
	border: 1px solid #e5e5e5;
	-webkit-border-radius: 5px;
	-moz-border-radius: 5px;
	border-radius: 5px;
	-webkit-box-shadow: 0 1px 2px rgba(0, 0, 0, .05);
	-moz-box-shadow: 0 1px 2px rgba(0, 0, 0, .05);
	box-shadow: 0 1px 2px rgba(0, 0, 0, .05);
}

.form-signin .form-signin-heading,.form-signin .checkbox {
	margin-bottom: 10px;
}

.form-signin input[type="text"],.form-signin input[type="password"] {
	font-size: 16px;
	height: auto;
	margin-bottom: 15px;
	padding: 7px 9px;
}
</style>
<div class="container">
	<form class="form-signin" method="post" action="#connecting">
		<input type="hidden" name="token" value="<?=$_SESSION['NEXT_TOKEN']; ?>" />
		<h2 class="form-signin-heading">Identification</h2>
		<input type="text" class="input-block-level" placeholder="Prénom et nom" required name="login[user]">
		<input type="password" class="input-block-level" placeholder="Mot de passe" required name="login[pass]">
		<?php
if (Html::getRequestOrPost('org', false) !== false) {
		?>
		<input type="hidden" name="org" value="<?=Html::getRequestOrPost('org', '', Html::TEXT) ?>">
		<?php } ?>
		<label class="checkbox"> <input type="checkbox" value="1" name="login[remember]"> Se souvenir de moi
		</label>
		<button class="btn btn-large btn-primary" type="submit">Valider</button>
		<?php
if ($errorMsg !== false) {
		?>
		<div class="alert alert-error" style="margin-top: 20px;">
			<i class="icon-bell"></i>
			<?=$errorMsg; ?>
		</div>
		<?php
}
		?>
	</form>
</div>
<?php
include $_SERVER['DOCUMENT_ROOT'] . '/../includes/end.inc.php';
