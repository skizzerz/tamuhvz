<?php
//emailqueue system
//cron job to send at most 20 emails every minute from the queue
define('HVZ', 1);
define('NOSETUP', 1);
require('settings.php');
$db = mysql_connect($dbserver, $dbuser, $dbpass);
mysql_select_db($dbname, $db);
if(isset($_SERVER) && array_key_exists('REQUEST_METHOD', $_SERVER)) {
	//web
	//show access denied message
	echo "This script must be run from the command line";
	die(1);
} else {
	//cron
	//get next job from the queue
	$res = mysql_query("SELECT * FROM emailqueue ORDER BY time LIMIT 1", $db);
	if(mysql_num_rows($res) == 0) {
		//no emails
		die(0);
	}
	$row = mysql_fetch_object($res);
	$to = explode(',', $row->to);
	if(count($to) > 50) {
		$bcc = array_splice($to, 0, 50);
	} else {
		$bcc = $to;
		$to = false;
	}
	$replyto = ($row->replyto != '') ? "\r\nReply-To: {$row->replyto}" : "";
	if(count($bcc) == 1) {
		//normal email
		mysql_query("DELETE FROM emailqueue WHERE time='{$row->time}'", $db);
		mail($bcc[0], $row->subject, $row->message, "From: no-reply@tamuhvz.com$replyto", '-fno-reply@tamuhvz.com');
	} else {
		//mass bcc email
		if(!$to) {
			//queue item is done
			mysql_query("DELETE FROM emailqueue WHERE time='{$row->time}'", $db);
		} else {
			//queue item isn't done
			$to = ltrim(implode(',', $to), ',');
			mysql_query("UPDATE emailqueue SET `to`='$to' WHERE time='{$row->time}'", $db);
		}
		$to = 'Undisclosed Recipients <no-reply@tamuhvz.com>';
		$bcc = implode(',', $bcc);
		mail($to, $row->subject, $row->message, "From: no-reply@tamuhvz.com$replyto\r\nBcc: $bcc", '-f "no-reply@tamuhvz.com"');
	}
}
die(0);