<!DOCTYPE html>
<html>
<head>
<title>Contact Webmaster &mdash; Fightin' Texas Aggie Humans vs. Zombies</title>
</head>
<body>
<h1>Contact Webmaster</h1>
<?php
define('HVZ', true);
require 'includes/db.php';
require 'settings.php';
if (isset($_POST['message'])) {
	$replyto = ($_POST['from'] != '') ? $_POST['from'] : 'no-reply@tamuhvz.com';
	$to = 'ryan-schmidt@tamu.edu';
	$subject = mysql_real_escape_string('tamuhvz.com contact form message: ' . $_POST['subject']);
	if(isset($_SERVER['HTTP_X_FORWARD_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARD_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	$message = mysql_real_escape_string(wordwrap(htmlspecialchars($_POST['message'] . "\r\n\r\n----\r\nMessage originated from $ip"), 70));
	$db->query("INSERT INTO emailqueue (`time`,`to`,`replyto`,`subject`,`message`) VALUES(NOW(), 'ryan-schmidt@tamu.edu', '$replyto', '$subject', '$message')");
	echo 'Your email is being processed. If your message needs a reply, you should receive one in 48 hours.';
	echo '<br /><a href="http://tamuhvz.com">Back to tamuhvz.com</a>';
} else {
?>
<form method="post" action="">
<table>
<tr><td>Your email:</td><td><input type="text" name="from" size="80" value="" /></td></tr>
<tr><td>Subject:</td><td><input type="text" name="subject" size="80" value="" /></td></tr>
</table>
Message:<br />
<textarea cols="70" rows="15" name="message"></textarea>
<br />
<input type="submit" name="submit" value="Send message" />
</form><br /><br />
<a href="http://tamuhvz.com">Back to tamuhvz.com</a>
<?php } ?>
</body>
</html>