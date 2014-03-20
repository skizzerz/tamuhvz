<?php if(!defined('HVZ')) die(-1); ?>
<?php
//force SSL
if($proto == 'http') {
	$sslurl = str_replace('http://', 'https://', $url);
	header("Location: $sslurl?page=login");
	exit;
}
$error = '';
if(isset($_POST['login'])) {
	$user = mysql_real_escape_string(strtolower($_POST['username']));
	$pass = $_POST['password'];
	//does this user exist?
	$res = $db->query("SELECT * FROM users WHERE username='$user'");
	if($res->numRows()) {
		//is the password valid
		$row = $res->fetchRow();
		if(Password::compare($row->password, $pass, $row->uin)) {
			$db->query("DELETE FROM temp_pw WHERE uin={$row->uin}"); //remove any temp pws
			$_SESSION['username'] = $row->username;
			$_SESSION['uin'] = $row->uin;
			$_SESSION['logout_epoch'] = $logout_epoch;
			if(isset($_POST['rememberme'])) {
				//make a cookie
				$time = time() + 60 * 60 * 24 * 30; //30 days
				setcookie('hvz', rib64_encode($time . '|' . rib64_encode($row->uin) . '|' . rib64_encode($row->username) . '|' . $logout_epoch), $time, '/');
			}
?>
<h1>Logging In...</h1>
<script type="text/javascript">
setTimeout("gotoMain()", 2500);
function gotoMain() {
	var loc = window.location.href.split('?')[0];
	window.location = loc + '?page=main';
}
</script>
<noscript>
<a href="?page=main">Click here</a> to continue.
</noscript>
<?php
			return;
		} else {
			//temp password?
			$r2 = $db->query("SELECT * FROM temp_pw WHERE uin={$row->uin}");
			if($r2->numRows() && ($pw = $r2->fetchRow())) {
				if(md5($pass) == $pw->password) {
					//check expiry
					$tst = strtotime($pw->time);
					$tsn = time();
					if($tsn > $tst + 3600) {
						$error = '<span class="error">Invalid username/password</span><br />';
						$db->query("DELETE FROM temp_pw WHERE uin={$row->uin}");
					} else {
						changeTemp($row->uin);
						return;
					}
				} else {
					$error = '<span class="error">Invalid username/password</span><br />';
				}
			} else {
				$error = '<span class="error">Invalid username/password</span><br />';
			}
		}
	} else {
		$error = '<span class="error">Invalid username/password</span><br />';
	}
} elseif(isset($_POST['temp'])) {
	$uin = decodeString($_POST['token']);
	$pass = $_POST['newpass'];
	$conf = $_POST['retype'];
	if($pass != $conf) {
		$error = '<span class="error">Passwords don\'t match</span><br />';
		changeTemp($uin);
		return;
	}
	$tuser = new User($uin);
	$db->query("DELETE FROM temp_pw WHERE uin={$uin}");
	$tuser->updatePassword($pass);
	$_SESSION['username'] = $tuser->username;
	$_SESSION['uin'] = $tuser->uin;
?>
<div class="messagebox">
<div class="header">Password changed successfully</div>
<div class="message">You will be redirected to the login page in a few seconds. If you do not want to wait, <a href="?page=login">click here</a></div>
</div>
<script type="text/javascript">
setTimeout("gotoMain()", 2500);
function gotoMain() {
	var loc = window.location.href.split('?')[0];
	window.location = loc + '?page=login';
}
</script>
<?php
	return;
} elseif(isset($_GET['action']) && $_GET['action'] == 'resetpass' && isset($_POST['submit'])) {
	//check the email given
	$email = mysql_real_escape_string($_POST['email']);
	$res = $db->query("SELECT * FROM users WHERE email='$email'");
	if(!$res->numRows()) {
		echo '<span class="error">No account associated with this email</span><br />';
		resetPass();
		return;
	} else {
		//send temp password
		$row = $res->fetchRow();
		$temp = '';
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		for($i = 0; $i<12; $i++) {
			$temp .= substr($chars, rand(0, 61), 1);
		}
		$db->query("REPLACE INTO temp_pw (uin,password,time) VALUES({$row->uin}, MD5('$temp'), NOW())");
		$token = sha1($row->uin . '-' . md5($temp) . $secretsalt) . str_pad(dechex($row->uin), 4, '0', STR_PAD_LEFT);
		$message = 'Someone, probably you, requested a password reset on the tamuhvz.com website.
The password for your account '.$row->username.' has been reset to:
'.$temp.'. This temporary password will expire in 1 hour.

You may either log in with the temporary password above, or follow the link below to reset your password:
https://tamuhvz.com/?page=login&action=resetpass&token='.$token.'

If you did not request this password reset, you may safely ignore this email. Your old password will still work.';
		$from = 'From: no-reply@tamuhvz.com';
		$to = $_POST['email'];
		$message = str_replace("'", "\\'", wordwrap($message, 70));
		$db->query("INSERT INTO emailqueue (`time`,`to`,`replyto`,`subject`,`message`) VALUES(NOW(), '$to', '$from', 'Password reset on tamuhvz.com', '$message')");
		//$success = mail($to, 'Password reset', $message, $from);
?>
<div class="messagebox">
<div class="header">Password reset successfully</div>
<div class="message">Check your email for the temporary password, then <a href="?page=login">log in</a></div>
</div>
<?php
		return;
	}
} elseif(isset($_GET['action']) && $_GET['action'] == 'resetpass' && isset($_GET['token'])) {
	$id = hexdec(substr($_GET['token'], -4));
	$token = substr($_GET['token'], 0, -4);
	global $db;
	$res = $db->query("SELECT * FROM temp_pw WHERE uin={$id}");
	if($res->numRows() && ($pw = $res->fetchRow())) {
		if($token == sha1($id . '-' . $pw->password . $secretsalt)) {
			//check expiry
			$tst = strtotime($pw->time);
			$tsn = time();
			if($tsn > $tst + 3600) {
				echo '<span class="error">Temporary password has expired.</span><br />';
				$db->query("DELETE FROM temp_pw WHERE uin={$id}");
			} else {
				changeTemp($id);
				return;
			}
		} else {
			echo '<span class="error">Invalid token</span><br />';
		}
	} else {
		echo '<span class="error">Invalid token</span><br />';
	}
	return;
} elseif(isset($_GET['action']) && $_GET['action'] == 'resetpass') {
	resetPass();
	return;
}
?>

<?php
function changeTemp($uin) {
	global $error;
	$token = encodeString($uin);
?>
<h1>Change Password</h1>
You have logged in with a temporary password, it must be changed now.<br />
<?= $error ?><br />
<form method="post" action="?page=login">
<table>
<tr><td>New Password:</td><td><input type="password" name="newpass" /></td></tr>
<tr><td>Retype Password:</td><td><input type="password" name="retype" /></td></tr>
</table>
<br />
<input type="hidden" name="token" value="<?= $token ?>" /><input type="submit" name="temp" value="Change password" />
</form>
<?php
}

function resetPass() {
	global $error;
?>
<h1>Reset Password</h1>
Don't remember your password? Enter your email below to have it reset!<br />
If you do remember your password, <a href="?page=login">login here</a>!<br />
<?= $error ?><br />
<form method="post" action="?page=login&action=resetpass">
Email: <input type="text" size="30" name="email" /><br /><br />
<input type="submit" name="submit" value="Reset password" />
</form>
<?php
}
?>
<h1>Login</h1>
<?= $error ?>
<a href="?page=login&action=resetpass">Forgot your password?</a><br /><br />
<form method="post" action="?page=login">
<table>
<tr><td>Username:</td><td><input type="text" name="username" /></td></tr>
<tr><td>Password:</td><td><input type="password" name="password" /></td><tr>
</table>
<input type="checkbox" name="rememberme" id="rememberme" value="1" /> <label for="rememberme">Remember me for 30 days</label>
<br /><br /><input type="submit" name="login" value="Login" />
</form>
