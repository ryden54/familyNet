<?php
// ini_set('max_execution_time', 60);
$return_code = 0;
$_NO_TOKEN = true;
$_NO_USER_NEEDED = true;
require_once '../includes/main.inc.php';
require_once dirname(__FILE__) . '/../libs/PHPMailer/class.phpmailer.php';

$targetsQuery =
		'Select * FROM (Select Nom, Prenom, Email, id, lastLog, lastLetter, IF(lastLog IS NULL AND lastLetter IS NULL, NULL, IF(lastLetter IS NULL, lastLog, IF(lastLog IS NULL, lastLetter, IF(lastLog > lastLetter, lastLog, lastLetter)))) as last FROM ( SELECT personnes.id, Nom, Prenom, Max(DateLog) as lastLog, Max(sentDate) As lastLetter, personnes.Email FROM `personnes` Left Join stats_logs On (personnes.id = stats_logs.personnes_id) Left Join newsletters On (personnes.id = newsletters.personnes_id) WHERE personnes.DateMort IS NULL AND outOfFamily = 0 AND personnes.Email is not null GROUP BY personnes.id) lastEvents ) t2 WHERE last IS NULL OR  last < DATE_SUB(NOW(), INTERVAL 7 DAY) order by last asc limit 5';

$targets = Context::getInstance()->getSql()->fetchAll($targetsQuery, array());

echo 'Nombre de lettres a envoyer : ' . sizeof($targets) . "";
foreach ($targets As $t) {
	$failure = false;
	echo 'Target : ' . $t['Prenom'] . ' ' . $t['Nom'] . ' : ' . ($t['last'] === null ? 'jamais' : $t['last']) . ' (' . $t['Email'] . ')' . "<br/>";

	$newsletters_id = Context::getInstance()->getDb()->newsletters()->insert(array(
						'personnes_id' => $t['id'], 'email' => $t['Email']
					));

	echo 'Newsletter generated : "' . $newsletters_id . '"<br/>';

	$newsletter = Context::getInstance()->getDb()->newsletters[$newsletters_id];

	//Create a new PHPMailer instance
	$mail = new PHPMailer();
	$mail->CharSet = 'UTF-8';
	//Set who the message is to be sent from
	$mail->SetFrom(Config::get('SENDER_ADRESS', 'MAIL'), Config::get('SENDER_NAME', 'MAIL'));

	//Set who the message is to be sent to
	if (Config::get('BCC_ADRESS', 'MAIL') !== false) {
		$mail->AddBCC(Config::get('BCC_ADRESS', 'MAIL'), 'WebMaster');
	}

	if (Config::get('TEST_ADRESS', 'MAIL') !== false) {
		$mail->AddAddress(Config::get('TEST_ADRESS', 'MAIL'), $t['Prenom'] . ' ' . $t['Nom']);
	} else {
		$mail->AddAddress($t['Email'], $t['Prenom'] . ' ' . $t['Nom']);
	}

	//Set the subject line
	$mail->Subject = 'L\'actualité du site'/* . ' (' . $t['Email'] . ')'*/;

	$images = array();
	$content = getNewsAfter($t['last'], $t, $images, $newsletters_id);

	foreach ($images As $id => $hash) {
		if (Context::getInstance()->getDb()->newsletters_images()
				->insert(array(
					'hash' => $hash, 'photos_id' => $id, 'newsletters_id' => $newsletters_id
				)) == false) {
			echo 'Fail to update images for newsletter<br/>';
			$failure = true;
			break;
		}
	}
	if ($failure === true) {
		continue;
	}

	$newsletter['content'] = $content;
	if ($newsletter->update() == false) {
		echo 'Fail to update newsletter for content<br/>';
		$failure = true;
		continue;
	}

	//Read an HTML message body from an external file, convert referenced images to embedded, convert HTML into a basic plain-text alternative body
	$mail->MsgHTML($content);

	//Send the message, check for errors
	if (Config::get('SEND', 'MAIL') === false || $mail->Send() === false) {
		echo 'Mailer Error for ' . $t['Prenom'] . ' ' . $t['Nom'] . ' - "' . $t['Email'] . '" : ' . $mail->ErrorInfo . "<br/>";
		$newsletter['error'] = $mail->ErrorInfo;
	} else {
		echo "Message sent to " . $t['Email'] . "<br/>";
		$newsletter['sentDate'] = Db_Sql::getNowString();
		if ($newsletter->update() == false) {
			echo 'Fail to update newsletter for sending<br/>';
		}
	}
}

function getNewsAfter($date, $personne, &$images, $newsletters_id) {
	$context = Context::getInstance();
	ob_start();

	include dirname(__FILE__) . '/../includes/newsletter/news.inc.php';

	return ob_get_clean();
}

exit($return_code);
