<?php if (!defined('HVZ')) die(-1); ?>
<h1>Leaderboard</h1>
<?php
// get faction list
$res = $db->select('factions', '*');
$factions = array();
while ($row = $res->fetchRow()) {
	if ($row->id < -1 || $row->flags & 1) continue;
	$factions[$row->id] = $row->name;
}

$showCodeEntry = ($settings['game status'] == 4 && $user->getFaction() > -3);

// check leaderboard type
$leaderboardType = 'all';
if ($settings['game status'] == 4 && isset($_GET['type']) && $_GET['type'] == 'game') {
	$leaderboardType = 'game';
}

// check post
if (isset($_POST['missionsubmit']) && $showCodeEntry) {
	$doneMissions = array();
	$allMissions = array();
	$res = $db->select('missions', '*', array('game' => $settings['current game'], 'uin' => $user->getUin()));
	while ($row = $res->fetchRow()) {
		$doneMissions[$row->mission] = true;
	}

	$res = $db->select('mission_info', '*', array('game' => $settings['current game']));
	while ($row = $res->fetchRow()) {
		if ($row->flags & 1) {
			unset($doneMissions[$row->mission]);
			continue;
		}
		$allMissions[$row->mission] = true;
	}

	$code = intval($_POST['missioncode']);
	$faction = intval($_POST['missionfaction']);
	$valid = true;
	if ($_POST['missioncode'] === '' || $code != $_POST['missioncode'] || $code < 0 || $code > 9999 || !isset($allMissions[$code])) {
		echo '<span class="error">Invalid Code</span><br />';
		$valid = false;
	}
	if (isset($doneMissions[$code])) {
		echo '<span class="error">Code Already Used</span><br />';
		$valid = false;
	}
	if ($faction < -1 || !isset($factions[$faction])) {
		echo '<span class="error">Invalid Faction</span><br />';
		$valid = false;
	}
	if ($valid) {
		$db->query("INSERT INTO missions (game, uin, mission, faction) VALUES ({$settings['current game']}, {$user->getUin()}, {$code}, {$faction})");
		writeLog('mission', 'redeem', array($code, $faction));
		echo '<span class="success">Code Redeemed</span><br />';
	}
}

// variables and stuff
$missionBase = $missionFaction = 0;
$res = $db->query("SELECT m.mission, mi.flags, mi.points partPoints, mr.points factPoints FROM missions m JOIN mission_info mi ON m.game = mi.game AND m.mission = mi.mission JOIN mission_results mr ON m.game = mr.game AND m.mission = mr.mission AND m.faction = mr.faction WHERE m.game = {$settings['current game']} AND m.uin = {$user->getUin()}");
while ($row = $res->fetchRow()) {
	if ($row->flags & 1) continue;
	$doneMissions[$row->mission] = true;
	$missionBase += $row->partPoints;
	$missionFaction += $row->factPoints;
}
if ($user->registered) {
	$totalGame = $settings['participation points'] + $missionBase + $missionFaction + ($settings['kill points'] * $user->getKills()) + $user->getPoints();
} else {
	$totalGame = 0;
}
$totalPoints = $user->getTotalPoints() + $totalGame;

// get leaderboard info
if ($leaderboardType == 'game') {
	$baseSelect = "SELECT u.uin, u.name, ({$settings['participation points']} + g.points + (g.kills * {$settings['kill points']}) + COALESCE(SUM(mi.points + mr.points), 0)) AS points FROM users u JOIN game g ON g.uin = u.uin AND g.game = {$settings['current game']} LEFT JOIN missions m ON m.uin = u.uin AND m.game = {$settings['current game']} LEFT JOIN mission_info mi ON m.game = mi.game AND m.mission = mi.mission LEFT JOIN mission_results mr ON m.game = mr.game AND m.mission = mr.mission AND m.faction = mr.faction GROUP BY u.uin ORDER BY points DESC";
} else {
	$baseSelect = "SELECT u.uin, u.name, CASE WHEN g.uin IS NOT NULL THEN (u.points + {$settings['participation points']} + g.points + (g.kills * {$settings['kill points']}) + COALESCE(SUM(mi.points + mr.points), 0)) ELSE u.points END AS points FROM users u LEFT JOIN game g ON g.uin = u.uin AND g.game = {$settings['current game']} LEFT JOIN missions m ON m.uin = u.uin AND m.game = {$settings['current game']} LEFT JOIN mission_info mi ON m.game = mi.game AND m.mission = mi.mission LEFT JOIN mission_results mr ON m.game = mr.game AND m.mission = mr.mission AND m.faction = mr.faction GROUP BY u.uin ORDER BY points DESC";
}
$leaderboard = array();
$res = $db->query($baseSelect);
$i = 0;
$t = 1;
$p = -1;
$n = 0;
$f = false;
while ($row = $res->fetchRow()) {
	if ($row->points != $p) {
		$i += $t;
		$t = 1;
	} else {
		$t++;
	}

	$p = $row->points;

	if ($n < 10 || $row->uin == $user->getUin()) {
		$leaderboard[] = array($i, $row->name, $row->points, $row->uin);
	}

	$n++;

	if ($row->uin == $user->getUin()) {
		$f = true;
	}

	if ($f && $n >= 10) {
		break;
	}
}
unset($res, $row, $i, $t, $p, $n, $f);

?>
<?php if ($showCodeEntry) { ?>
<fieldset>
<legend>Redeem Mission Code</legend>
<p>You can redeem a mission code using this form to prove that you participated in a mission. A moderator should tell you the code to use in the mission debriefing. For the "Faction" dropdown, please choose what faction you were in at the <em>end</em> of the mission.</p>
<form method="POST" action="?page=main&tab=leaderboard">
<span class="label2">Code:</span> <input type="text" name="missioncode" size=4 /><br />
<span class="label2">Faction:</span> <select name="missionfaction">
	<option value="-3"></option>
<?php foreach ($factions as $fid => $name) { ?>
	<option value="<?= $fid ?>"><?= $name ?></option>
<?php } // factions ?>
</select><br />
<input type="submit" name="missionsubmit" value="Redeem" />
</form>
</fieldset>
<?php } // show code entry ?>
<fieldset>
<legend>My Points</legend>
<p>This details how many points you currently have. Please note that any point values shown for the game currently in progress are not final and are subject to change.</p>
<p>
<span class="label2 wide">Previous Games:</span> <?= $user->getTotalPoints() ?><br />
<span class="label2 wide">Game Participation:</span> <?= $user->registered ? $settings['participation points'] : 0 ?><br />
<span class="label2 wide">Mission Participation:</span> <?= $missionBase ?><br />
<span class="label2 wide">Mission Objectives:</span> <?= $missionFaction ?><br />
<span class="label2 wide">Kills:</span> <?= $settings['kill points'] * $user->getKills() ?><br />
<span class="label2 wide">Miscellaneous:</span> <?= intval($user->getPoints()) ?><hr />
<span class="label2 wide">Total Game Points:</span> <?= $totalGame ?><br />
<span class="label2 wide">Total Points:</span> <?= $totalPoints ?><br />
</p>
</fieldset>
<fieldset>
<legend>Leaderboard</legend>
<?php if ($settings['game status'] == 4) { ?>
<form method="GET">
<input type="hidden" name="page" value="main" />
<input type="hidden" name="tab" value="leaderboard" />
<label><input type="radio" name="type" value="all" <?= $leaderboardType == 'all' ? 'checked' : '' ?> /> Overall</label>
<label><input type="radio" name="type" value="game" <?= $leaderboardType == 'game' ? 'checked' : '' ?> /> Current game</label>
<input type="submit" value="Update" />
</form>
<?php } // game in progress ?>
<table class="prettytable">
	<tr><th>Position</th><th>Name</th><th>Points</th></tr>
<?php $n = 0; foreach ($leaderboard as $entry) {
	$us = $entry[3] == $user->getUin();
	$pre = $us ? '<b>' : '';
	$post = $us ? '</b>' : '';
	++$n;
	if ($us && $n == 11 && $entry[0] != $leaderboard[9][0]) { echo '<tr><td colspan="3" style="text-align: center">...</td></tr>'; } ?>
	<tr><td><?= $pre . $entry[0] . $post ?></td><td><?= $pre . $entry[1] . $post ?></td><td><?= $pre . $entry[2] . $post ?></td>
<?php } // leaderboard loop ?>
</table>
</fieldset>
