<?php if(!defined('HVZ')) die(-1); ?>
<h1>Send Emails</h1>
<?php
if($settings['email'] == 0) {
	echo '<span class="error">Email has been disabled</span>';
	return;
}
if(isset($_POST['submit']) && ($settings['email'] == 1 || $settings['email'] == 3)) {
	if($_POST['subject'] == '' || $_POST['message'] == '') {
		echo "<a href='?page=admin&section=email'>&larr; Back to emails</a><br />Could not send emails: No subject or message specified.";
		return;
	}
	$faction = is_numeric($_POST['faction']) ? $_POST['faction'] : -4;
	$to = getEmails($faction);
	$subject = str_replace("'", "\\'", $_POST['subject']);
	$message = str_replace("'", "\\'", wordwrap(htmlspecialchars($_POST['message']), 70));
	writeLog('email', 'send', array('faction' => $faction, 'subject' => $_POST['subject'], 'message' => $_POST['message']));
	$db->query("INSERT INTO emailqueue (`time`,`to`,`replyto`,`subject`,`message`) VALUES(NOW(), '$to', '', '$subject', '$message')");
	echo 'The email is being processed. It may take over an hour to be delivered fully.<br />';
} elseif(isset($_POST['getemails']) && ($settings['email'] == 2 || $settings['email'] == 3)) {
	$faction = is_numeric($_POST['faction']) ? $_POST['faction'] : -4;
	$to = getEmails($faction);
	writeLog('email', 'get', $faction);
	echo "<a href='?page=admin&section=email'>&larr; Back to emails</a><br /><textarea style='width: 600px; height: 250px;'>\n$to\n</textarea>";
	return;
}

function getEmails($faction = false, $count = false) {
	global $db, $settings;
	if($faction === false) {
		$faction = -4;
	}
	switch($faction) {
		case -6:
			if($settings['emailall'] == 0) {
				echo '<span class="error">You are not allowed to email this faction</span><br />';
				return;
			}
			$where = '';
			if($settings['emailall'] == 1) {
				$where = " WHERE TIMEDIFF(TIMESTAMPADD(HOUR, {$settings['inactivity time']},loggedin), NOW()) >= 0";
			}
			$res = $db->query("SELECT email FROM users$where");
			break;
		case -5:
			if($settings['emailall'] == 0) {
				echo '<span class="error">You are not allowed to email this faction</span><br />';
				return;
			}
			$where = '';
			if($settings['emailall'] == 1) {
				$where = " AND TIMEDIFF(TIMESTAMPADD(HOUR, {$settings['inactivity time']},loggedin), NOW()) >= 0";
			}
			$res = $db->query("SELECT email FROM users WHERE registered=0$where");
			break;
		case -4:
			$res = $db->query("SELECT email FROM users WHERE registered=1");
			break;
		case -1:
			$res = $db->query("SELECT email FROM users WHERE registered=1 AND (faction=-1 OR faction=-2)");
			break;
		case 0:
			$res = $db->query("SELECT email FROM users WHERE registered=1 AND faction>-1");
			break;
		default:
			$res = $db->query("SELECT email FROM users WHERE registered=1 AND faction=$faction");
			break;
	}
	if($count) {
		return $res->numRows();
	}
	if(!$res->numRows()) {
		echo '<span class="error">Nobody belongs to the specified faction</span><br />';
		return;
	}
	$to = '';
	while($row = $res->fetchRow()) {
		$to .= $row->email . ',';
	}
	$to = rtrim($to, ',');
	return $to;
}
?>
<?php if($settings['emailall'] == 1) { ?>
<p>Emails sent to "All unregistered players" and "Everyone" will only send out emails to players who have logged into the site in the past <?= $settings['inactivity time'] ?> hours.</p>
<?php } ?>
<form method="post" action="?page=admin&section=email">
<table>
<tr><td>To:</td><td>
<select name="faction">
<option value="-4">All registered players (<?= getEmails(-4, true); ?> players)</option>
<option value="0">All Resistance members (<?= getEmails(0, true); ?> players)</option>
<option value="-1">All Horde members (<?= getEmails(-1, true); ?> players)</option>
<?php
$res = $db->query("SELECT * FROM factions");
while($row = $res->fetchRow()) {
	if($row->id == 0 || $row->id == -1) continue;
	?>
<option value="<?= $row->id ?>"><?= $row->name ?> (<?= getEmails($row->id, true); ?> players)</option>
	<?php
}
?>
<?php if($settings['emailall'] > 0) { ?>
<option value="-5">All unregistered players (<?= getEmails(-5, true); ?> players)</option>
<option value="-6">Everyone (<?= getEmails(-6, true); ?> players)</option>
<?php } ?>
</select>
</td></tr>
<?php if($settings['email'] == 1 || $settings['email'] == 3) { ?>
<tr><td>Subject:</td><td><input type="text" name="subject" style="width: 500px" /></td></tr>
<tr><td style="vertical-align: top">Message:</td><td>
<textarea name="message" style="width: 500px; height: 250px">
</textarea>
</td></tr>
<?php } ?>
</table><br />
<?php if($settings['email'] == 1 || $settings['email'] == 3) { ?>
<input type="submit" name="submit" value="Submit" />&nbsp;
<?php } ?>
<?php if($settings['email'] == 2 || $settings['email'] == 3) { ?>
<input type="submit" name="getemails" value="Get Emails" />
<?php } ?>
</form>