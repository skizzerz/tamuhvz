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
$totalGame = $settings['participation points'] + $missionBase + $missionFaction + ($settings['kill points'] * $user->getKills()) + $user->getPoints();
$totalPoints = $user->getTotalPoints() + $totalGame;
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
<?php } // game in progress ?>
<fieldset>
<legend>My Points</legend>
<p>This details how many points you currently have. Please note that any point values shown for the game currently in progress are not final and are subject to change.</p>
<p>
<span class="label2 wide">Previous Games:</span> <?= $user->getTotalPoints() ?><br />
<span class="label2 wide">Game Participation:</span> <?= $settings['participation points'] ?><br />
<span class="label2 wide">Mission Participation:</span> <?= $missionBase ?><br />
<span class="label2 wide">Mission Wins:</span> <?= $missionFaction ?><br />
<span class="label2 wide">Kills:</span> <?= $settings['kill points'] * $user->getKills() ?><br />
<span class="label2 wide">Miscellaneous:</span> <?= $user->getPoints() ?><hr />
<span class="label2 wide">Total Game Points:</span> <?= $totalGame ?><br />
<span class="label2 wide">Total Points:</span> <?= $totalPoints ?><br />
</p>
</fieldset>
