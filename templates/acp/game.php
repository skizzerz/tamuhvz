<?php if(!defined('HVZ')) die(-1); ?>
<h1>Game Flow</h1>
<?php
//pause/unpause game
if(isset($_POST['pausegame'])) {
	if($settings['game paused'] != 0) {
		//unpause game and update feed times for zombies
		writeLog('game', 'unpause', false);
		$add = time() - $settings['game paused']; //amount of time we have to add in
		$db->query("UPDATE game SET fed=TIMESTAMPADD(SECOND, $add, fed) WHERE game={$settings['current game']} AND uin IN (SELECT uin FROM users WHERE faction<0 AND faction>-3)");
		$db->query("UPDATE settings SET value='0' WHERE name='game paused'");
		$settings['game paused'] = 0;
	} else {
		//pause game
		writeLog('game', 'pause', false);
		$t = time();
		$db->query("UPDATE settings SET value='$t' WHERE name='game paused'");
		$settings['game paused'] = $t;
	}
}
for($i = 0; $i < 5; $i++) {
	if(isset($_POST["submit$i"])) {
		writeLog('game', 'advance', $i);
		$db->query("UPDATE settings SET value='$i' WHERE name='game status'");
		$settings['game status'] = $i;
		if($i == '0') {
			// update point values
			$db->query("UPDATE users u JOIN game g ON g.uin = u.uin AND g.game = {$settings['current game']} SET u.points = (u.points + {$settings['participation points']} + g.points + (g.kills * {$settings['kill points']}) + (SELECT COALESCE(SUM(mi.points + mr.points), 0) FROM missions m JOIN mission_info mi ON m.game = mi.game AND m.mission = mi.mission JOIN mission_results mr ON m.game = mr.game AND m.mission = mr.mission AND m.faction = mr.faction WHERE m.uin = u.uin AND m.game = {$settings['current game']}))");
			//end the game
			$settings['current game']++;
			$db->query("UPDATE settings set value='{$settings['current game']}' WHERE name='current game'");
			$db->query("TRUNCATE TABLE oz_pool"); // we don't save the OZ pool since it has real names and phone numbers in it
			$db->query("UPDATE users SET registered=0,faction=0");
			$db->query("UPDATE users SET status=0 WHERE status=1"); //kicked people are no longer kicked
			$db->query("UPDATE settings SET value='0' WHERE name='game paused'"); //unpause the game since it is now over
			$settings['game paused'] = 0;
		} elseif ($i == '3') {
			if($settings['oz select'] == '0') {
				//pick OZ(s)
				$num = explode('/', $settings['number ozs']);
				$res = $db->query("SELECT * FROM oz_pool");
				$pool = array();
				while($row = $res->fetchRow()) {
					$pool[] = $row->uin;
				}
				$pooltotal = count($pool);
				if(!$pooltotal) {
					//eep, nobody is in the OZ pool! the admins will have to manually set one
					echo '<span class="error">Nobody in OZ pool, you will have to manually pick an OZ</span><br /><br />';
					break;
				}
				if(count($num) == 2) {
					$res = $db->query("SELECT COUNT(*) AS players FROM users WHERE registered=1");
					$row = $res->fetchRow();
					$players = $row->players;
					$numozs = $num[0] * ceil($players / $num[1]);
					for($i = 0; $i < $numozs; $i++) {
						$r = rand(0, $pooltotal - 1 - $i);
						$oz = $pool[$r];
						array_splice($pool, $r, 1);
						$db->query("UPDATE users SET faction=-2 WHERE uin='$oz'");
						$db->query("UPDATE game SET turned=NOW(),fed=NOW() WHERE game={$settings['current game']} AND uin='$oz'");
						if($pooltotal - 1 - $i == 0) {
							break; //oz pool is empty
						}
					}
				} else {
					$numozs = $num[0];
					for($i = 0; $i < $numozs; $i++) {
						$r = rand(0, $pooltotal - 1 - $i);
						$oz = $pool[$r];
						array_splice($pool, $r, 1);
						$db->query("UPDATE users SET faction=-2 WHERE uin='$oz'");
						$db->query("UPDATE game SET turned=NOW(),fed=NOW() WHERE game={$settings['current game']} AND uin='$oz'");
						if($pooltotal - 1 - $i == 0) {
							break; //oz pool is empty
						}
					}
				}
			} elseif($settings['oz select'] == '1') {
				//show list of people in OZ pool for mods to choose from
				//as well as a button that cancels OZ selection and sends it back a stage
				//TODO: finish
			}
		} elseif($i == '4') {
			//update OZ turn/fed time
			$res = $db->query("SELECT * FROM users WHERE faction=-2");
			while($row = $res->fetchRow()) {
				$oz = $row->uin;
				$db->query("UPDATE game SET turned=NOW(),fed=NOW() WHERE game={$settings['current game']} AND uin='$oz'");
			}
		}
		break;
	}
}
if($settings['game paused'] != 0) {
	echo '<span class="error">Game has been paused -- Zombie starve timers will not decrease until game resumes</span><br />';
}

if($settings['game status'] == 4) {
?>
<p>Pause the game -- this will cause the game to be put "on hold" meaning that zombie starve timers will not decrease.
When the game is resumed, zombie feed times will be updated so that they have exactly the same amount of time left after the game is resumed as
they did when the game got paused</p>
<form method="post" action="?page=admin&section=game">
<input type="submit" name="pausegame" value="<?= ($settings['game paused'] == 0) ? 'Pause game' : 'Resume game' ?>" />
</form>
<br /><br />
<?php } ?>
<form method="post" action="?page=admin&section=game">
<table class="admintable">
<tr><th>Advance</th><th>Stage</th></tr>
<tr>
	<td style="text-align: center"><?= $settings['game status'] == '0' ? '<input type="submit" name="submit1" value="Advance" />' : ($settings['game status'] == '1' ? 'Current' : '') ?></td>
	<td>
		<h3>Open Registration</h3>
		Begin registration for a new game
	</td>
</tr>
<tr>
	<td style="text-align: center"><?= $settings['game status'] == '1' ? '<input type="submit" name="submit2" value="Advance" />' : ($settings['game status'] == '2' ? 'Current' : '') ?></td>
	<td>
		<h3>Close Registration</h3>
		Close registration, you can still force-register people from the "Edit Players" tab. Late registration timers (if specified in settings) only start once the game actually begins
	</td>
</tr>
<tr>
	<td style="text-align: center"><?= $settings['game status'] == '2' ? '<input type="submit" name="submit3" value="Advance" />' : ($settings['game status'] == '3' ? 'Current' : '') ?></td>
	<td>
		<h3>Pick Original Zombie</h3>
		If OZ Selection is Automatic, this will choose who the OZs are. Otherwise, you will need to go to "Edit Players" to set people as OZs
	</td>
</tr>
<tr>
	<td style="text-align: center"><?= $settings['game status'] == '3' ? '<input type="submit" name="submit4" value="Advance" />' : ($settings['game status'] == '4' ? 'Current' : '') ?></td>
	<td>
		<h3>Start Game</h3>
		Starts the game. The OZs will be marked as Humans on the site for however long the OZ hide time is set to, after which they will become automatically revealed
	</td>
</tr>
<tr>
	<td style="text-align: center"><?= $settings['game status'] == '4' ? '<input type="submit" name="submit0" value="Advance" />' : ($settings['game status'] == '0' ? 'Current' : '') ?></td>
	<td>
		<h3>End Game</h3>
		Well, it was fun while it lasted!<br />
		<span class="error">This will finalize all point values, making them unable to be changed in the future. Make sure that the participation, kill, and mission points are where you want them to be at before proceeding!</span>
	</td>
</tr>
</table>
</form>
