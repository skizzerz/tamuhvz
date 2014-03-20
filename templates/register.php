<?php if(!defined('HVZ')) die(-1); ?>
<h1>Register For Game</h1>
<?php
$late = false;
$lrhuman = false;
$lrzombie = false;

if($settings['game status'] == 4) {
	//don't allow late registration if the user has previously suicided
	$res = $db->select('game', 'id', array('uin' => $user->uin));
	if(!$res->numRows()) {
		//determine timers for late registration
		$res = $db->query('SELECT UNIX_TIMESTAMP(time) AS time FROM logging WHERE action="game/advance" AND description="i:4;" ORDER BY time DESC LIMIT 1');
		$row = $res->fetchRow();
		$gamestart = $row->time;
		$curtime = time();
		if($settings['late register human'] > 0) {
			if(($curtime - $gamestart)/3600 < $settings['late register human']) {
				$lrhuman = true;
			}
		} elseif($settings['late register human'] == 0) {
			$lrhuman = true;
		}
		if($settings['late register zombie'] > 0) {
			if($settings['late register human'] > 0) {
				if(($curtime - $gamestart)/3600 < $settings['late register human'] + $settings['late register zombie']) {
					$lrzombie = true;
				}
			} else {
				if(($curtime - $gamestart)/3600 < $settings['late register zombie']) {
					$lrzombie = true;
				}
			}
		} elseif($settings['late register zombie'] == 0) {
			$lrzombie = true;
		}
		$late = $lrhuman || $lrzombie;
	}
}

function processRegistration() {
	global $user, $db, $late, $lrhuman, $lrzombie, $settings;
	if(isset($_POST['submit'])) {
		$cont = true;
		if($late) {
			unset($_POST['ozpool']); //not that it really matters
		}
		if(isset($_POST['fname'])) {
			$name = $_POST['fname'] . ' ' . $_POST['lname'];
			if(preg_match('/^[A-Z][A-Z0-9]+ [A-Z][A-Z0-9]+$/i', $name)) {
				$user->updateName($name);
			} else {
				echo '<span class="error">Invalid name specified</span><br />';
				$cont = false;
			}
		}
		if(isset($_POST['email'])) {
			$email = $_POST['email'];
			if(preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $email)) {
				$user->updateEmail($email);
			} else {
				echo '<span class="error">Invalid email address specified</span><br />';
				$cont = false;
			}
		}
		$ozpool = array();
		if ( isset( $_POST['ozpool'] ) ) {
			$ozapp = $_POST['realname'] && $_POST['phone'] && $_POST['additional'];
			$cont = $cont && $ozapp;
			if ( !$ozapp ) {
				echo '<span class="error">You must specify your Real Name, Phone Number, and Additional Information if applying to be an OZ</span><br />';
			} else {
				$ozpool = array(
					'realname' => $_POST['realname'],
					'phone' => $_POST['phone'],
					'additional' => $_POST['additional'],
				);
			}
		}
		if($cont) {
			//register the user
			$user->register($user->name, $ozpool);
			//see if we should assign the faction they elected for if necessary
			if($late || $settings['factions'] == 3) {
				if(!$late || ($lrhuman && $settings['factions'] == 3)) {
					$faction = intval($_POST['faction']);
				} elseif(!$lrhuman && $lrzombie) {
					$faction = -1;
				} else {
					$faction = 0;
				}
				//don't allow a user to hack themselves into being an OZ or something...
				if($faction >= 0 || (!$lrhuman && $lrzombie)) {
					$db->query("UPDATE users SET faction=$faction WHERE uin={$user->uin}");
				} else {
					$faction = 0;
				}
			} else {
				$faction = 0;
			}
			if(!$late) {
				writeLog('register', 'register', array('ozpool' => isset($_POST['ozpool']), 'faction' => $faction), $user->uin, $user->id);
			} else {
				writeLog('register', 'lateregister', array('faction' => $faction), $user->uin, $user->id);
			}
?>
Registration successful! Your id is <b><?= $user->id ?></b>.<br />
An email has been sent to your email address with your ID. If you do not get it within an hour or two, ensure that your specified email address is correct and that emails from no-reply@tamuhvz.com do not get placed in your junk/bulk folder<br />
<a href="?page=main">Continue</a>
<?php
			$db->query("INSERT INTO emailqueue (`time`,`to`,`replyto`,`subject`,`message`) VALUES(NOW(), '{$user->email}', '', 'Registration confirmation - HvZ', 'You have registered for an upcoming game of Texas A&M HvZ\r\nYour ID is {$user->id}')");
			return true;
		}
	}
	return false;
}
?>
<?php if(!$user->loggedin) { ?>
<span class="error">You need to be logged in to register for the game</span>
<?php return; } //end if not logged in check ?>
<?php if($user->status == 2) { ?>
<span class="error">You are banned from being able to register for games</span>
<?php return; } elseif($late && $user->status == 1) { ?>
<span class="error">You have been kicked from the current game and are unable to rejoin</span>
<?php return; }  //end if banned check ?>
<?php if($user->registered) { ?>
<span class="error">You are already registered for the game</span>
<?php return; } //end if already registered ?>
<?php if($settings['game status'] != 1 && !$late) { ?>
<span class="error">Registration is currently closed! Please check the front page and Facebook for more information on when registration opens for the next game</span>
<?php } else {
	//this ensures other checks are accounted for, such as banned/game status/etc.
	if(processRegistration()) return;
?>
<form method="post" action="?page=main&tab=register">
<?php if($late) { ?>
Because you are registering late, you will be unable to enter the OZ pool. If you choose to register now, you will be registered as a <?= $lrhuman ? 'Human' : 'Zombie' ?>. Please try to register earlier next time!
<?php } //end if late registration check ?>
<br /><table>
<?php if(!$user->name || !$user->email) { ?>
<?= !$user->name ? '<tr><td>Name<br /><span class="label">First:</span><br /><span class="label">Last:</span></td><td><br /><input type="text" size="40" name="fname" /><br /><input type="text" size="40" name="fname" /></td></tr>' : '' ?>
<?= !$user->email ? '<tr><td>Email:</td><td><input type="text" size="40" name="email" /></td></tr>' : '' ?>
<?php } //end should we display the table check ?>
<?php if((!$late || $lrhuman) && $settings['factions'] == 3) { ?>
<tr><td>Faction:</td><td><select name="faction">
<?php
//get a list of every faction (which there should be at this point, if not... well then... the admins suck
//so let them pick "Resistance" to make them (and more importantly the db) happy
$res = $db->query("SELECT * FROM factions");
if($res->numRows()) {
	$row = $res->fetchRow();
	while($row && $row->id > 0) {
		?>
<option value="<?= $row->id ?>"><?= $row->name ?></option>
		<?php
		$row = $res->fetchRow();
	}
} else {
	$row = $res->fetchRow();
	while($row->id != 0) $row = $res->fetchRow();
	?>
<option value="0"><?= $row->name ?></option>
	<?php
}
?>
</select></td></tr>
<?php } //end if player gets to assign his/her own factions check ?>
<?php if(!$late) { ?>
<tr><td colspan="2"><input type="checkbox" id="ozpool" name="ozpool" value="1" /> <label for="ozpool">Enter into OZ pool</label></td></tr>
<tr class="ozapp hidden"><td>Real Name:</td><td><input type="text" size="40" name="realname" /> Please enter your real name so we know who you actually are if you are selected as an OZ.</td></tr>
<tr class="ozapp hidden"><td>Phone Number:</td><td><input type="text" size="40" name="phone" /> Please enter a contact phone number (like your cellphone) so we can reach you if you are selected as an OZ.</td></tr>
<tr class="ozapp hidden"><td>Additional Details:</td><td><textarea name="additional" rows="8" cols="40"></textarea><br />Please enter any additional information you would like considered for your OZ application. At a minimum, include what times you are able to hunt on OZ day.</td></tr>
<?php } //end if not late registration check ?>
</table><br />
<input type="submit" name="submit" value="Register" />
</form>
<script type="text/javascript">
$('input[name="ozpool"]').change(function () {
	if ($(this).prop('checked')) {
		$('.ozapp').removeClass('hidden');
	} else {
		$('.ozapp').addClass('hidden');
	}
});
</script>
<?php } //end if registration is open check ?>