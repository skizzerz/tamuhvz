<?php if(!defined('HVZ')) die(-1); ?>
<h1>Register for Account</h1>
<?php
//force SSL
if($proto == 'http') {
	$sslurl = str_replace('http://', 'https://', $url);
	header("Location: $sslurl?page=acctregister");
	exit;
}
if(isset($_POST['submit'])) {
	//handle registration
	$errors = false;
	//email
	$email = $_POST['email'];
	if(preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $email)) {
		//valid email
		//check if it's already in the db
		$res = $db->query("SELECT * FROM users WHERE email='$email'");
		if($res->numRows()) {
			//it is
			$errors = true;
			echo '<span class="error">Specified email address is already in use by another account</span><br />';
		} else {
			//valid email
			//do nothing
		}
	} else {
		$errors = true;
		echo '<span class="error">Invalid email address specified</span><br />';
	}
	//UIN
	$uin = $settings['nextid'];
	//username
	$username = strtolower($_POST['username']); //username, forced into lowercase (thus making it case-insensitive)
	if(preg_match('/[a-z][a-z0-9. -]+[a-z0-9]/', $username)) {
		//valid, check if it exists in db
		$res = $db->query("SELECT * FROM users WHERE username='$username'");
		if($res->numRows()) {
			//it is
			$errors = true;
			echo '<span class="error">Specified username is already in use by another account</span><br />';
		} else {
			//valid username
			//do nothing
		}
	} else {
		$errors = true;
		echo '<span class="error">Invalid username specified</span><br />';
	}
	//name
	$name = $_POST['fname'] . ' ' . $_POST['lname'];
	if(preg_match('/^[A-Z][A-Z0-9]+ [A-Z][A-Z0-9]+$/i', $name)) {
		$res = $db->query("SELECT * FROM users WHERE name='$name'");
		if($res->numRows()) {
			$errors = true;
			echo '<span class="error">Specified name is already in use by another account</span><br />';
		} else {
			//valid
		}
	} else {
		$errors = true;
		echo '<span class="error">Invalid first and/or last name</span><br />';
	}
	//password
	$pass = $_POST['pass'];
	$retype = $_POST['retype'];
	if($pass == $retype) {
		//valid
	} elseif($pass == '') {
		$errors = true;
		echo '<span class="error">Password cannot be blank</span><br />';
	} else {
		$errors = true;
		echo '<span class="error">Passwords don\'t match</span><br />';
	}
	//process if no errors
	if(!$errors) {
		//update nextid for the next person to sign up
		$settings['nextid']++;
		$db->query("UPDATE settings SET value='{$settings['nextid']}' WHERE name='nextid'");
		$pass = mysql_real_escape_string($pass);
		$user = User::createUser($uin, $username, $email);
		$user->updatePassword($pass);
		$user->updateName($name);
		$user->loggedin = false;
?>
<div class="messagebox">
<div class="header">Account created</div>
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
	}
}
?>
<?php if($settings['game status'] == 1) { ?>
<div>Important! Registering for an account on the site will <em>not</em> register you for the HvZ game itself!
After you create your account you must additionally register for the upcoming game if you wish to play.</div>
<?php } //end if game registration is enabled ?>
<form method="post" action="?page=acctregister">
<table>
<tr><td>Username:</td><td><input type="text" name="username" /></td><td><i>(Not case sensitive)</i></td></tr>
<tr><td>Password:</td><td><input type="password" name="pass" /></td><td><i>(Case sensitive)</i></td></tr>
<tr><td>Retype password:</td><td><input type="password" name="retype" /></td><td>&nbsp;</td></tr>
<tr><td>First name:</td><td><input type="text" name="fname" /></td><td>&nbsp;</td></tr>
<tr><td>Last name:</td><td><input type="text" name="lname" /></td><td>&nbsp;</td></tr>
<tr><td>Email:</td><td><input type="text" name="email" /></td><td>&nbsp;</td></tr>
</table>
<br />
<input type="submit" name="submit" value="Register" />
</form>
