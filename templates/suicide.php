<?php if(!defined('HVZ')) die(-1); ?>
<h1>Leave the Game</h1>
<?php
if(isset($_POST['submit'])) {
	if(isset($_POST['agree'])) {
		//unregister but keep the ID valid
		$db->query("UPDATE users SET registered=0,faction=0 WHERE uin={$user->uin}");
		writeLog('register', 'suicide', array($user->faction), false, $user->id);
		echo '<span class="error">Unregistration successful.</span><br />';
		return;
	} else {
		echo '<span class="error">You must check the box stating "I agree"</span><br />';
	}
}
?>
Please read the following and check the "I agree" box to affirm that you agree with what is stated here.
<ul>
<li>Once you use this form to leave the game, you <b>cannot</b> rejoin at a later point. Pretending to still be in the game after you leave is a d-bag move, so don't do that either.</li>
<li>Your ID will still be valid after you leave the game, so don't give it away to anybody. This also means that you can't leave to prevent a Zombie from reporting a kill on you after you give out your ID.</li>
<li>This will only make you leave the game currently in progress, it will not remove your account from the website.</li>
</ul>
<form method="post" action="?page=main&tab=suicide">
<input type="checkbox" name="agree" value="1" id="agree" /> <label for="agree">I agree</label><br /><br />
<input type="submit" name="submit" value="Submit" id="submit" />
</form>
