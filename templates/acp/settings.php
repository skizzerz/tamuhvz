<?php if (!defined('HVZ')) die(-1); ?>
<h1>Settings</h1>
<?php
if (isset($_POST['submit'])) {
	//we haz post data! \o/
	$enablestarve = isset($_POST['enablestarve']) ? 1 : 0;
	$starve = $_POST['starve'];
	$feedpart = $_POST['feedpart'];
	$numozs = $_POST['numozs'];
	$ozhide = $_POST['ozhide'];
	$ozselect = $_POST['ozselect'];
	$inactivity = $_POST['inactivity'];
	$lateregisterhuman = $_POST['lateregisterhuman'];
	$lateregisterzombie = $_POST['lateregisterzombie'];
	$factions = isset($_POST['factions']) ? $_POST['factions'] : false;
	if ($settings['enable starvation'] != $enablestarve) {
		writeLog('settings', 'enablestarve', array('old' => $settings['enable starvation'], 'new' => $enablestarve));
		$db->query("UPDATE settings SET value='$enablestarve' WHERE name='enable starvation'");
		if ($enablestarve) {
			$db->query("UPDATE game SET fed=NOW() WHERE uin IN (SELECT uin FROM users WHERE faction < 0 AND faction > -3)");
		}
	}
	if (is_numeric($starve) && $starve > 0) {
		if ($settings['starve time'] != $starve) {
			writeLog('settings', 'starve', array('old' => $settings['starve time'], 'new' => $starve));
			$db->query("UPDATE settings SET value='$starve' WHERE name='starve time'");
			$settings['starve time'] = $starve;
		}
	} else {
		echo '<span class="error">Invalid starve time given</span><br />';
	}
	if (is_numeric($feedpart) && $feedpart >= 0 && $feedpart <= 10) {
		if ($settings['feed partners'] != $feedpart) {
			writeLog('settings', 'feedpart', array('old' => $settings['feed partners'], 'new' => $feedpart));
			$db->query("UPDATE settings SET value='$feedpart' WHERE name='feed partners'");
			$settings['feed partners'] = $feedpart;
		}
	} else {
		echo '<span class="error">Invalid number of feed partners given</span><br />';
	}
	if (preg_match('/^([1-9][0-9]*|[1-9][0-9]*\/[1-9][0-9]*)$/', $numozs)) {
		if ($settings['number ozs'] != $numozs) {
			writeLog('settings', 'numozs', array('old' => $settings['number ozs'], 'new' => $numozs));
			$db->query("UPDATE settings SET value='$numozs' WHERE name='number ozs'");
			$settings['number ozs'] = $numozs;
		}
	} else {
		echo '<span class="error">Invalid number of OZs given</span><br />';
	}
	if (is_numeric($ozhide) && $ozhide >= 0) {
		if ($settings['oz hide'] != $ozhide) {
			writeLog('settings', 'ozhide', array('old' => $settings['oz hide'], 'new' => $ozhide));
			$db->query("UPDATE settings SET value='$ozhide' WHERE name='oz hide'");
			$settings['oz hide'] = $ozhide;
		}
	} else {
		echo '<span class="error">Invalid OZ hide time given</span><br />';
	}
	if (is_numeric($inactivity) && $inactivity > 0) {
		if ($settings['inactivity time'] != $inactivity) {
			writeLog('settings', 'inactivity', array('old' => $settings['inactivity time'], 'new' => $inactivity));
			$db->query("UPDATE settings SET value='$inactivity' WHERE name='inactivity time'");
			$settings['inactivity time'] = $inactivity;
		}
	} else {
		echo '<span class="error">Invalid inactivity time given</span><br />';
	}
	if ($factions) {
		if ($settings['factions'] != $factions) {
			writeLog('settings', 'factions', array('old' => $settings['factions'], 'new' => $factions));
			$db->query("UPDATE settings SET value='$factions' WHERE name='factions'");
			$settings['factions'] = $factions;
		}
	}
	if ($settings['oz select'] != $ozselect) {
		writeLog('settings', 'ozselect', array('old' => $settings['oz select'], 'new' => $ozselect));
		$db->query("UPDATE settings SET value='$ozselect' WHERE name='oz select'");
		$settings['oz select'] = $ozselect;
	}
	if (is_numeric($lateregisterhuman) && $settings['late register human'] != $lateregisterhuman) {
		writeLog('settings', 'lateregisterhuman', array('old' => $settings['late register human'], 'new' => $lateregisterhuman));
		$db->query("UPDATE settings SET value='$lateregisterhuman' WHERE name='late register human'");
		$settings['late register human'] = $lateregisterhuman;
	}
	if (is_numeric($lateregisterzombie) && $settings['late register zombie'] != $lateregisterzombie) {
		writeLog('settings', 'lateregisterzombie', array('old' => $settings['late register zombie'], 'new' => $lateregisterzombie));
		$db->query("UPDATE settings SET value='$lateregisterzombie' WHERE name='late register zombie'");
		$settings['late register zombie'] = $lateregisterzombie;
	}
}
?>
<form method="post" action="?page=admin&section=settings">
<table class="admintable">
<tr><th>Setting</th><th style="min-width: 150px">Value</th><th>Description</th></tr>
<tr><td>Enable Starvation</td><td><input type="checkbox" name="enablestarve" <?= $settings['enable starvation'] ? 'checked' : '' ?> value="1" /></td><td>Whether to enable the starvation system. If disabled, Zombies will not starve and they will be unable to feed other Zombies. If enabled during a game, starve timers will be reset to when it is enabled</td></tr>
<tr><td>Starve Time</td><td><input type="text" name="starve" value="<?= $settings['starve time'] ?>" /></td><td>Time (in hours) it takes for a Zombie to starve after feeding</td></tr>
<tr><td>Feed Partners</td><td><input type="text" name="feedpart" value="<?= $settings['feed partners'] ?>" /></td><td>Number of other Zombies that can be fed from a kill</td></tr>
<tr><td>Number of OZs</td><td><input type="text" name="numozs" value="<?= $settings['number ozs'] ?>" /></td><td>Number of Original Zombies to select for a game. Can take one of two formats:<br />If this is just a number, it will select that many OZs<br />If this is a number slash number (e.g. 1/500), it will select that many OZs per that many people signed up (rounded up)</td></tr>
<tr><td>OZ Selection</td><td><input type="radio" name="ozselect" id="ozs0" value="0" <?= $settings['oz select'] == '0' ? 'checked="checked"' : '' ?> /><label for="ozs0">Automatic</label><br /><input type="radio" name="ozselect" id="ozs1" value="1" <?= $settings['oz select'] == '1' ? 'checked="checked"' : '' ?> /><label for="ozs1">Manual</label></td><td>Whether to select OZs automatically or manually</td></tr>
<tr><td>OZ Hide Time</td><td><input type="text" name="ozhide" value="<?= $settings['oz hide'] ?>" /></td><td>Time (in hours) the Original Zombies are hidden on the website (they show up as Resistance and with 0 kills in player lists)</td></tr>
<tr><td>Late Registration for Humans</td><td><input type="text" name="lateregisterhuman" value ="<?= $settings['late register human'] ?>" /></td><td>Time (in hours) users may register for the game as Humans after the game has already started. A time of 0 means users may always register as Humans, and -1 means no late registration as a Human is allowed</td></tr>
<tr><td>Late Registration for Zombies</td><td><input type="text" name="lateregisterzombie" value ="<?= $settings['late register zombie'] ?>" /></td><td>Time (in hours) users may register for the game as Zombies after the game has already started. A time of 0 means users may always register as Zombies, and -1 means no late registration as a Zombie is allowed. If a time is specified for Late Registration for Humans, this time starts after the Human registration ends</td></tr>
<tr><td>Inactivity Time</td><td><input type="text" name="inactivity" value="<?= $settings['inactivity time'] ?>" /></td><td>Time (in hours) after last visit before accounts are marked as inactive</td></tr>
<?php if($settings['factions'] > 0) { ?>
<tr><td>Faction Assignment</td><td><input type="radio" name="factions" id="fac1" value="1" <?= $settings['factions'] == '1' ? 'checked="checked"' : '' ?> /><label for="fac1">Manual</label><br /><input type="radio" name="factions" id="fac2" value="2" <?= $settings['factions'] == '2' ? 'checked="checked"' : '' ?> /><label for="fac2">Automatic (random)</label><br /><input type="radio" name="factions" id="fac3" value="3" <?= $settings['factions'] == '3' ? 'checked="checked"' : '' ?> /><label for="fac3">Player's choice</label><br /></td><td>How factions should be assigned when the user registers for the game. If set to "Manual", every user will be put in the "Resistance" group. If set to "Automatic (random)", every user will be assigned a random group when they register. If set to "Player's choice", every user will be given a dropdown to select a group from when they register</td></tr>
<?php } //end factions enabled check ?>
</table>
<br />
<input type="submit" name="submit" value="Update settings" />
</form>
