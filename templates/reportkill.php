<?php if (!defined('HVZ')) die(-1); ?>
<?php if ($settings['game status'] < 4) {
	//game hasn't started yet
?>
<div class="messagebox">
<div class="header">Game not started</div>
<div class="message">The game hasn't started yet!</div>
</div>
<?php
	return;
} elseif ($user->faction != -2 && $user->faction != -1 && !$user->isAllowed('mundo')) {
	//not a zombie
?>
<div class="messagebox">
<div class="header">Not a Zombie</div>
<div class="message">You aren't a Zombie, so you can't report kills.</div>
</div>
<?php
	return;
} elseif ($settings['game paused'] != 0) {
?>
<div class="messagebox">
<div class="header">Game Paused</div>
<div class="message">You cannot report kills while the game is paused.</div>
</div>
<?php
	return;
}
?>
<h1>Report a Kill</h1>
<?php
$idregex = '/^[0-9abcdef]{8}$/i';
if (isset($_POST['submit'])) {
	//kill be reported, check if it's a valid id
	$id = $_POST['id'];
	//do some common replacements
	$replace = array(
		'O' => '0',
		'S' => '5',
		'Z' => '2',
		'I' => '1'
	);
	$id = str_replace( array_keys( $replace ), array_values( $replace ), strtoupper( $id ) );
	$ret = checkValidId($id);
	if($ret === true) {
		//valid id, get variables
		$res = $db->query("SELECT * FROM game WHERE id='$id'");
		$row = $res->fetchRow();
		$vuin = $row->uin;
		$res->freeResult();
		$res = $db->query("SELECT * FROM users WHERE uin='$vuin'");
		$row = $res->fetchRow();
		//first get the list of who he's feeding and feed them, if applicable
		$partners = array();
		if ($settings['enable starvation'] && $settings['feed partners'] > 0) {
			for ($i = 0; $i < $settings['feed partners']; $i++) {
				if (!isset($_POST["feed$i"]) || isset($_POST["nofeed$i"])) {
					continue;
				}
				$puin = decodeString($_POST["feed$i"]);
				$partners[] = $puin;
				$db->query("UPDATE users SET feeds=feeds+1 WHERE uin='$puin'");
				$db->query("UPDATE game SET feeds=feeds+1, fed=NOW() WHERE uin='$puin'");
			}
		}
		//now update this user
		$uin = $user->uin;
		$db->query("UPDATE users SET feeds=feeds+1, kills=kills+1 WHERE uin='$uin'");
		$db->query("UPDATE game SET feeds=feeds+1, kills=kills+1, fed=NOW() WHERE uin='$uin'");
		$db->query("INSERT INTO feeds (zombie, victim, time, feeds) VALUES('$uin', '$id', NOW(), '$partners')");
		//and update the victim (we report time turned/fed as 1 hour from now, but just mark them as a zombie now)
		//but don't update a suicided victim
		if ($row->registered) {
			$db->query("UPDATE users SET faction=-1 WHERE uin='$vuin'");
			$db->query("UPDATE game SET fed=TIMESTAMPADD(HOUR, 1, NOW()), turned=TIMESTAMPADD(HOUR, 1, NOW()) WHERE uin='$vuin'");
		}
		//log attempt
		writeLog('kill', 'kill', $partners, $vuin, $id);
		//report success
		echo '<span class="error">Kill successful</span><br />';
	} else {
		//invalid, checkValidId() outputs error message but we need to log it
		writeLog('kill', $ret, $id);
	}
}

function checkValidId($id) {
	global $idregex, $db;
	if (!preg_match($idregex, $id)) {
		//invalid id (not 8 hex digits)
		echo '<span class="error">Invalid ID. If a player gave this ID to you, please contact a mod</span><br />';
		return 'invalid';
	}
	$res = $db->query("SELECT * FROM game WHERE id='$id'");
	if ($res->numRows()) {
		//id is in game table, let's make sure it belongs to a human
		$row = $res->fetchRow();
		$vuin = $row->uin;
		$res->freeResult();
		$res = $db->query("SELECT * FROM users WHERE uin='$vuin'");
		$row = $res->fetchRow();
		if ($row->faction >= 0) {
			//"human", now check if they're registered or not (aka suicided)
			if (!$row->registered) {
				//suicided, so see if they've already been fed on
				$res2 = $db->query("SELECT * FROM feeds WHERE victim='$id'");
				if ($res2->numRows()) {
					//already eaten
					echo '<span class="error">This ID has already been used. If a player gave this ID to you, please contact a mod</span><br />';
					return 'used';
				}
			}
			//if we get down here, we're good
			return true;
		} else {
			//not human
			echo '<span class="error">This ID has already been used. If a player gave this ID to you, please contact a mod</span><br />';
			return 'used';
		}
	} else {
		//id not found in game table, perhaps it is in feeds (e.g. player had that id, was killed, then cured)
		$res = $db->query("SELECT * FROM feeds WHERE victim='$id'");
		if ($res->numRows()) {
			//comment above is accurate
			echo '<span class="error">This ID has already been used. If a player gave this ID to you, please contact a mod</span><br />';
			return 'used';
		} else {
			//id doesn't exist
			echo '<span class="error">Invalid ID. If a player gave this ID to you, please contact a mod</span><br />';
			return 'unknown';
		}
	}
	return 'unknown';
}

function getValidIdFromGet() {
	if (isset($_GET['victimid'])) {
		if (($out = checkValidId($_GET['victimid'])) === true) { //checkValidId() will output error messages
			return $_GET['victimid'];
		} else {
			writeLog('kill', $out, $_GET['victimid']);
		} //else log it
	}
	return '';
}
$getid = getValidIdFromGet();
?>
<form method="post" action="?page=main&tab=reportkill">
<table cellspacing="10">
<tr><td>ID:</td><td><input type="text" name="id" value="<?= $getid; ?>" /></td></tr>
<?php if ($settings['enable starvation'] && $settings['feed partners'] > 0) { ?>
<tr><td style="vertical-align: top">Feed partners:</td><td><?php
//get all zmobies
$res = $db->query("SELECT users.uin,users.name,users.registered,users.feedpref,game.fed,game.kills FROM users LEFT JOIN game ON users.uin = game.uin WHERE users.faction=-1 OR users.faction=-2 ORDER BY game.fed");
//oh hey, mysql sorted it for us, ain't that nifty
$options = '';
while($row = $res->fetchRow()) {
	if($row->uin == $user->uin || !$row->registered) {
		continue;
	}
	$st = strtotime($row->fed) + ($settings['starve time'] * 60 * 60);
	$tl = $st - time();
	$tlh = $tl / 3600;
	if($row->feedpref != -1 && $tlh > $row->feedpref) {
		continue;
	}
	$th = 0;
	$tm = 0;
	while($tl > 3599) {
		$th += 1;
		$tl -= 3600;
	}
	while($tl > 59) {
		$tm += 1;
		$tl -= 60;
	}
	$th = str_pad($th, 2, '0', STR_PAD_LEFT);
	$tm = str_pad($tm, 2, '0', STR_PAD_LEFT);
	$options .= '<option value="' . encodeString($row->uin) . '">' . $row->name . ' (' . $th . ':' . $tm . ', ' . $row->kills . ' kill' . ($row->kills == 1 ? '' : 's') . ')</option>';
}
for($i = 0; $i < $settings['feed partners']; $i++) {
	echo "<select name='feed$i' id='feed$i' style='margin-bottom: 5px'>$options</select> <input type='checkbox' name='nofeed$i' value='1' id='nofeed$i' onchange='javascript:toggleSelect($i);' /> <label for='nofeed$i'>Do not use this feed</label><br />";
}
?></td></tr>
<?php } //end if feed partners are greater than 0 ?>
</table>
<input type="submit" name="submit" value="Submit" />
</form>
<script type="text/javascript">
function toggleSelect(i) {
	e = document.getElementById('nofeed' + i);
	if(!e) return;
	if(e.checked) {
		document.getElementById('feed' + i).disabled = 'disabled';
	} else {
		document.getElementById('feed' + i).disabled = '';
	}
}
</script>
