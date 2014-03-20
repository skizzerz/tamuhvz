<?php if(!defined('HVZ')) die(-1); ?>
<?php
//force SSL
if($proto == 'http') {
	$sslurl = str_replace('http://', 'https://', $url);
	header("Location: $sslurl?page=developer");
	exit;
}
//check for POST data
if(isset($_POST['developer-settings'])) {
	//we has post, change settings at will
	$db->query("UPDATE settings SET value='{$_POST['profpic']}' WHERE name='profile pictures'");
	$settings['profile pictures'] = $_POST['profpic'];
	$db->query("UPDATE settings SET value='{$_POST['factions']}' WHERE name='factions'");
	$settings['factions'] = $_POST['factions'];
	$db->query("UPDATE settings SET value='{$_POST['printid']}' WHERE name='printid'");
	$settings['printid'] = $_POST['printid'];
	$db->query("UPDATE settings SET value='{$_POST['board']}' WHERE name='board'");
	$settings['board'] = $_POST['board'];
	$db->query("UPDATE settings SET value='{$_POST['email']}' WHERE name='email'");
	$settings['email'] = $_POST['email'];
	$db->query("UPDATE settings SET value='{$_POST['emailall']}' WHERE name='emailall'");
	$settings['emailall'] = $_POST['emailall'];
	$db->query("UPDATE settings SET value='{$_POST['changeusernames']}' WHERE name='change usernames'");
	$settings['change usernames'] = $_POST['changeusernames'];
	$db->query("UPDATE settings SET value='{$_POST['emailconfirmation']}' WHERE name='email confirmation'");
	$settings['email confirmation'] = $_POST['emailconfirmation'];
	$db->query("UPDATE settings SET value='{$_POST['guess']}' WHERE name='guess'");
	if(isset($_POST['restoreadmin'])) {
		//restore admin status to self
		$db->query("INSERT INTO permissions VALUES({$originalUser->uin}, 'admin')");
?>
<div class="messagebox">
<div class="header">Admin status restored</div>
<div class="message">Please wait for the next page to load</div>
</div>
<script type="text/javascript">
setTimeout("gotoDev()", 2500);
function gotoDev() {
	var loc = window.location.href.split('?')[0];
	window.location = loc + '?page=developer';
}
</script>
<?php
		return;
	}
} elseif(isset($_POST['developer-masquerade'])) {
	if($_POST['type'] == 'start') {
		$res = $db->query("SELECT uin FROM users WHERE username='{$_POST['who']}'");
		if($res->numRows()) {
			$row = $res->fetchRow();
			$uin = $row->uin;
			setcookie('masquerade', rib64_encode($uin), 0);
			setcookie('mybbuser', '', time() - 3600);
			setcookie('vtoken', '', time() - 3600);
			setcookie('sid', '', time() - 3600);
?>
<div class="messagebox">
<div class="header">You have begun masquerading as another user</div>
<div class="message">Please wait for the next page to load</div>
</div>
<script type="text/javascript">
setTimeout("gotoDev()", 2500);
function gotoDev() {
	var loc = window.location.href.split('?')[0];
	window.location = loc + '?page=developer';
}
</script>
<?php
			return;
		} else {
			//invalid user id
			echo '<span class="error">Invalid username specified</span><br />';
		}
	} elseif($_POST['type'] == 'stop') {
		setcookie('masquerade', '', time() - 3600);
		setcookie('mybbuser', '', time() - 3600);
		setcookie('vtoken', '', time() - 3600);
		setcookie('sid', '', time() - 3600);
?>
<div class="messagebox">
<div class="header">You have stopped masquerading as another user</div>
<div class="message">Please wait for the next page to load</div>
</div>
<script type="text/javascript">
setTimeout("gotoDev()", 2500);
function gotoDev() {
	var loc = window.location.href.split('?')[0];
	window.location = loc + '?page=developer';
}
</script>
<?php
		return;
	}
}
?>
<h1>Site Settings</h1>
<form method="post" action="?page=developer">
<table>
<tr>
	<td>Profile pictures:</td>
	<td>
		<input type="radio" name="profpic" id="profpic0" value="0"<?= $settings['profile pictures'] ? '' : ' checked="checked"' ?> /> 
		<label for="profpic0">Disabled</label>
	</td>
	<td>
		<input type="radio" name="profpic" id="profpic1" value="1"<?= $settings['profile pictures'] ? ' checked="checked"' : '' ?> /> 
		<label for="profpic1">Enabled</label>
	</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td>Factions:</td>
	<td>
		<input type="radio" name="factions" id="factions0" value="0"<?= $settings['factions'] ? '' : ' checked="checked"' ?> /> 
		<label for="factions0">Disabled</label>
	</td>
	<td>
		<input type="radio" name="factions" id="factions1" value="1"<?= $settings['factions'] == 1 ? ' checked="checked"' : '' ?> /> 
		<label for="factions1">Manual</label>
	</td>
	<td>
		<input type="radio" name="factions" id="factions2" value="2"<?= $settings['factions'] == 2 ? ' checked="checked"' : '' ?> /> 
		<label for="factions2">Automatic (Random)</label>
	</td>
	<td>
		<input type="radio" name="factions" id="factions3" value="3"<?= $settings['factions'] == 3 ? ' checked="checked"' : '' ?> /> 
		<label for="factions3">Player's Choice</label>
	</td>
</tr>
<tr>
	<td>Printable IDs:</td>
	<td>
		<input type="radio" name="printid" id="printid0" value="0"<?= $settings['printid'] ? '' : ' checked="checked"' ?> /> 
		<label for="printid0">Disabled</label>
	</td>
	<td>
		<input type="radio" name="printid" id="printid1" value="1"<?= $settings['printid'] ? ' checked="checked"' : '' ?> /> 
		<label for="printid1">Enabled</label>
	</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td>Board (MyBB):</td>
	<td>
		<input type="radio" name="board" id="board0" value="0"<?= $settings['board'] ? '' : ' checked="checked"' ?> /> 
		<label for="board0">Disabled</label>
	</td>
	<td>
		<input type="radio" name="board" id="board1" value="1"<?= $settings['board'] ? ' checked="checked"' : '' ?> /> 
		<label for="board1">Enabled</label>
	</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td>Email:</td>
	<td>
		<input type="radio" name="email" id="email0" value="0"<?= $settings['email'] == '0' ? ' checked="checked"' : '' ?> /> 
		<label for="email0">Disabled</label>
	</td>
	<td>
		<input type="radio" name="email" id="email1" value="1"<?= $settings['email'] == '1' ? ' checked="checked"' : '' ?> /> 
		<label for="email1">Send only</label>
	</td>
	<td>
		<input type="radio" name="email" id="email2" value="2"<?= $settings['email'] == '2' ? ' checked="checked"' : '' ?> /> 
		<label for="email2">View only</label>
	</td>
	<td>
		<input type="radio" name="email" id="email3" value="3"<?= $settings['email'] == '3' ? ' checked="checked"' : '' ?> /> 
		<label for="email3">Enabled</label>
	</td>
</tr>
<tr>
	<td>Email Everyone:</td>
	<td>
		<input type="radio" name="emailall" id="emailall0" value="0"<?= $settings['emailall'] == '0' ? ' checked="checked"' : '' ?> />
		<label for "emailall0">Disabled</label>
	</td>
	<td>
		<input type="radio" name="emailall" id="emailall1" value="1"<?= $settings['emailall'] == '1' ? ' checked="checked"' : '' ?> />
		<label for "emailall1">Active Only</label>
	</td>
	<td>
		<input type="radio" name="emailall" id="emailall2" value="2"<?= $settings['emailall'] == '2' ? ' checked="checked"' : '' ?> />
		<label for "emailall2">Enabled</label>
	</td>
	<td>&nbsp;</td>
<tr>
	<td>Username Changing:</td>
	<td>
		<input type="radio" name="changeusernames" id="changeusernames0" value="0"<?= $settings['change usernames'] ? '' : ' checked="checked"' ?> /> 
		<label for="changeusernames0">Disabled</label>
	</td>
	<td>
		<input type="radio" name="changeusernames" id="changeusernames1" value="1"<?= $settings['change usernames'] ? ' checked="checked"' : '' ?> /> 
		<label for="changeusernames1">Enabled</label>
	</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td>Email Confirmation:</td>
	<td>
		<input type="radio" name="emailconfirmation" id="emailconfirmation0" value="0"<?= $settings['email confirmation'] == '0' ? ' checked="checked"' : '' ?> />
		<label for="emailconfirmation0">Disabled</label>
	</td>
	<td>
		<input type="radio" name="emailconfirmation" id="emailconfirmation1" value="1"<?= $settings['email confirmation'] == '1' ? ' checked="checked"' : '' ?> />
		<label for="emailconfirmation1">User Registration</label>
	</td>
	<td>
		<input type="radio" name="emailconfirmation" id="emailconfirmation2" value="2"<?= $settings['email confirmation'] == '2' ? ' checked="checked"' : '' ?> />
		<label for="emailconfirmation2">Game Registration</label>
	</td>
	<td>
		<input type="radio" name="emailconfirmation" id="emailconfirmation3" value="3"<?= $settings['email confirmation'] == '3' ? ' checked="checked"' : '' ?> />
		<label for="emailconfirmation3">Both</label>
	</td>
</tr>
<tr>
	<td>Guess Tab:</td>
	<td>
		<input type="radio" name="guess" id="guess0" value="0"<?= $settings['guess'] == '0' ? ' checked="checked"' : '' ?> />
		<label for="guess0">Disabled</label>
	</td>
	<td>
		<input type="radio" name="guess" id="guess1" value="1"<?= $settings['guess'] == '1' ? ' checked="checked"' : '' ?> />
		<label for="guess1">Enabled</label>
	</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
<?php if(!$originalUser->isAllowed('admin')) { ?>
<tr>
	<td colspan="4">
		<input type="checkbox" name="restoreadmin" id="restoreadmin" value="1" />
		<label for="restoreadmin">Restore Admin status</label>
	</td>
</tr>
<?php } //end if not admin check ?>
</table>
<br />
<input type="submit" name="developer-settings" value="Submit" />
</form>
<br />
<h1 id="masquerade">Masquerade</h1>
<form method="post" action="?page=developer">
<?php
	if($user->getUsername() != $originalUser->getUsername()) {
		?><input type="hidden" name="type" value="stop" /><input type="submit" name="developer-masquerade" value="Stop Masquerading" /><?php
	} else {
		?>Masquerade as user: <input type="text" name="who" value="" /> (Enter username)<br />
		<input type="hidden" name="type" value="start" /><input type="submit" name="developer-masquerade" value="Submit" /><?php
	}
?>
</form>
<br />
<h1 id="queue">Email queue</h1>
<?php
$res = $db->query("SELECT * FROM emailqueue ORDER BY time");
$rows = array();
while($row = $res->fetchRow()) {
	$rows[] = $row;
}
$jobs = $res->numRows();
if(isset($_GET['id'])) {
	switch($_GET['action']) {
		case 'view':
			$to = htmlspecialchars(implode(', ', explode(',', $rows[$_GET['id']]->to)));
			$sub = htmlspecialchars($rows[$_GET['id']]->subject);
			$mes = nl2br(htmlspecialchars($rows[$_GET['id']]->message));
			echo "<table border='1'><tr><th>Subject</th><th>To</th><th>Body</th></tr><tr><td>{$sub}</td><td>{$to}</td><td>{$mes}</td></tr></table><br />";
			break;
		case 'delete':
			$to = mysql_real_escape_string($rows[$_GET['id']]->to);
			$sub = mysql_real_escape_string($rows[$_GET['id']]->subject);
			$mes = mysql_real_escape_string($rows[$_GET['id']]->message);
			$db->query("DELETE FROM emailqueue WHERE `to`='{$to}' AND `subject`='{$sub}' AND `message`='{$mes}' LIMIT 1");
			echo "<span class='error'>Email Deleted</span><br />";
			array_splice($rows, $_GET['id'], 1);
			$jobs--;
			break;
		case 'up':
			if($_GET['id'] == 0) break; //don't operate on first id
			$to = mysql_real_escape_string($rows[$_GET['id']]->to);
			$sub = mysql_real_escape_string($rows[$_GET['id']]->subject);
			$mes = mysql_real_escape_string($rows[$_GET['id']]->message);
			$ourtime = $rows[$_GET['id']]->time;
			$upto = mysql_real_escape_string($rows[$_GET['id']-1]->to);
			$upsub = mysql_real_escape_string($rows[$_GET['id']-1]->subject);
			$upmes = mysql_real_escape_string($rows[$_GET['id']-1]->message);
			$uptime = $rows[$_GET['id']-1]->time;
			$dummytime = '1970-01-01 00:00:00';
			$db->query("UPDATE emailqueue SET `time`='{$dummytime}' WHERE `to`='{$upto}' AND `subject`='{$upsub}' AND `message`='{$upmes}'");
			$db->query("UPDATE emailqueue SET `time`='{$uptime}' WHERE `to`='{$to}' AND `subject`='{$sub}' AND `message`='{$mes}'");
			$db->query("UPDATE emailqueue SET `time`='{$ourtime}' WHERE `to`='{$upto}' AND `subject`='{$upsub}' AND `message`='{$upmes}'");
			$res = $db->query("SELECT * FROM emailqueue ORDER BY time");
			$rows = array();
			while($row = $res->fetchRow()) {
				$rows[] = $row;
			}
			$jobs = $res->numRows();
			break;
		case 'down':
			if($_GET['id'] >= count($rows) - 1) break; //don't operate on last id
			$to = mysql_real_escape_string($rows[$_GET['id']]->to);
			$sub = mysql_real_escape_string($rows[$_GET['id']]->subject);
			$mes = mysql_real_escape_string($rows[$_GET['id']]->message);
			$ourtime = $rows[$_GET['id']]->time;
			$dnto = mysql_real_escape_string($rows[$_GET['id']+1]->to);
			$dnsub = mysql_real_escape_string($rows[$_GET['id']+1]->subject);
			$dnmes = mysql_real_escape_string($rows[$_GET['id']+1]->message);
			$dntime = $rows[$_GET['id']+1]->time;
			$dummytime = '1970-01-01 00:00:00';
			$db->query("UPDATE emailqueue SET `time`='{$dummytime}' WHERE `to`='{$dnto}' AND `subject`='{$dnsub}' AND `message`='{$dnmes}'");
			$db->query("UPDATE emailqueue SET `time`='{$dntime}' WHERE `to`='{$to}' AND `subject`='{$sub}' AND `message`='{$mes}'");
			$db->query("UPDATE emailqueue SET `time`='{$ourtime}' WHERE `to`='{$dnto}' AND `subject`='{$dnsub}' AND `message`='{$dnmes}'");
			$res = $db->query("SELECT * FROM emailqueue ORDER BY time");
			$rows = array();
			while($row = $res->fetchRow()) {
				$rows[] = $row;
			}
			$jobs = $res->numRows();
			break;
		default:
			break;
	}
}
?>
<table border="1">
<tr><th>Subject</th><th>Time remaining</th><th>Actions</th></tr>
<?php
	$mins = 0;
	$id = 0;
	foreach($rows as $row) {
		$to = explode(',', $row->to);
		$mins += ceil(count($to) / 50);
		echo "<tr><td>{$row->subject}</td><td>$mins minute(s)</td>";
		echo "<td><a href='?page=developer&action=view&id=$id'>View</a> &bull; <a href='?page=developer&action=up&id=$id'>Move Up</a> &bull; <a href='?page=developer&action=down&id=$id'>Move Down</a> &bull; <a href='?page=developer&action=delete&id=$id'>Delete</a></td>";
		echo "</tr>\n";
		$id++;
	}
?>
</table>
<i><?= $jobs ?> job(s) in queue</i>
<br />
<h1 id="query">Query the Database</h1>
<form method="post" action="?page=developer#query">
<textarea name="query" cols="60" rows="8">
<?= isset($_POST['query']) ? $_POST['query'] : '' ?>
</textarea>
<br />
<input type="submit" name="doquery" value="Submit" />
</form>
<br />
<?php
if(isset($_POST['doquery'])) {
	$res = $db->query($_POST['query']);
	if(!$res) {
		echo mysql_error() . ' (' . mysql_errno() . ')<br />';
	} else {
		if(is_object($res) && $res->numRows()) {
			$headinit = false;
			echo '<table class="prettytable">';
			while($row = mysql_fetch_assoc($res->getRawResult())) {
				if(!$headinit) {
					echo '<tr>';
					$heads = array_keys($row);
					foreach($heads as $h) {
						echo '<th>' . $h . '</th>';
					}
					echo '</tr>';
					$headinit = true;
				}
				echo '<tr>';
				foreach($row as $key => $value) {
					echo '<td>' . (is_null($value) ? '<i>NULL</i>' : htmlspecialchars($value)) . '</td>';
				}
				echo '</tr>';
			}
			echo '</table><br />';
		}
		if(preg_match('/INSERT|DELETE|UPDATE|REPLACE/i', $_POST['query'])) {
			echo 'Query OK, ' . mysql_affected_rows() . ' row(s) affected<br />';
		} elseif(preg_match('/SELECT|SHOW/i', $_POST['query'])) {
			echo 'Query OK, ' . mysql_num_rows($res->getRawResult()) . ' row(s) returned<br />';
		}
	}
}
?>
<br />
<h1 id="eval">Evaluate PHP Code</h1>
<form method="post" action="?page=developer#eval">
<textarea name="eval" cols="60" rows="8">
<?= isset($_POST['eval']) ? $_POST['eval'] : '' ?>
</textarea>
<br />
<input type="submit" name="doeval" value="Submit" />
</form>
<br />
<?php
if(isset($_POST['doeval'])) {
	ob_start();
	$r = eval($_POST['eval']);
	$c = ob_get_contents();
	ob_end_clean();
	if($r !== NULL) {
		var_dump($r);
		?><br /><hr /></br /><?php
	}
	echo $c;
}
?>
