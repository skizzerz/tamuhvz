<?php if(!defined('HVZ')) die(-1); ?>
<h1>Edit Players</h1>
<?php
if(isset($_POST['edituser'])) {
	//edit single player form submitted
	if(isset($_POST['email'])) {
		$email = $_POST['email'];
	}
	if(isset($_POST['newpass'])) {
		$newpass = $_POST['newpass'];
		$retpass = $_POST['retpass'];
	} else {
		$newpass = $retpass = false;
	}
	$uin = $_GET['edit'];
	$luser = new User($uin);
	//put changing factions and feeding here to allow them even if the target user is godmode
	if(isset($_POST['faction'])) {
		$faction = intval($_POST['faction']);
		if($luser->faction != $faction) {
			if($luser->faction < 0 && $faction >= 0) {
				$id = $luser->makeId();
			} else {
				$id = false;
			}
			writeLog('editplayer', 'changefaction', array('old' => $luser->faction, 'new' => $faction), $luser->uin, $id);
		}
		switch($faction) {
			case -3:
				if($luser->faction != -3) {
					//kill em off
					$db->query("UPDATE users SET faction=-3 WHERE uin={$luser->uin}");
					$db->query("UPDATE game SET starved=NOW() WHERE uin={$luser->uin}");
				}
				break;
			case -2:
				if($luser->faction != -2) {
					//OZ
					$db->query("UPDATE users SET faction=-2 WHERE uin={$luser->uin}");
					$db->query("UPDATE game SET fed=NOW(), turned=NOW() WHERE uin={$luser->uin}");
				}
				break;
			case -1:
				if($luser->faction != -1) {
					//Horde
					$db->query("UPDATE users SET faction=-1 WHERE uin={$luser->uin}");
					$db->query("UPDATE game SET fed=NOW(), turned=NOW() WHERE uin={$luser->uin}");
				}
				break;
			default:
				if(isDeletedFaction($faction)) break;
				if($luser->faction < 0) {
					//Cured
					$db->query("UPDATE game SET id='$id' WHERE uin={$luser->uin}");
				}
				if($luser->faction != $faction && is_numeric($faction)) {
					//switch factions
					$db->query("UPDATE users SET faction=$faction WHERE uin={$luser->uin}");
				}
				break;
		}
	}
	if(isset($_POST['feed'])) {
		writeLog('editplayer', 'feed', '', $luser->uin);
		$db->query("UPDATE game SET fed=NOW() WHERE uin={$luser->uin}");
	}
	//don't allow anything else to be edited though
	if( $luser->isAllowed('godmode') && !$user->isAllowed( 'developer' ) ) {
		editPlayer($uin);
		return;
	}
	$name = $_POST['fname'] . ' ' . $_POST['lname'];
	$banpic = isset($_POST['banpic']);
	$delpic = isset($_POST['delpic']);
	$picture = isset($_FILES['picture']) ? ($_FILES['picture']['error'] == UPLOAD_ERR_OK) : false;
	if($banpic && !$luser->isAllowed('nopicture')) {
		writeLog('editplayer', 'banpic', '', $luser->uin);
		$db->query("INSERT INTO permissions (uin, permission) VALUES('{$luser->uin}', ,'nopicture')");
	} elseif(!$banpic && $luser->isAllowed('nopicture')) {
		writeLog('editplayer', 'unbanpic', '', $luser->uin);
		$db->query("DELETE FROM permissions WHERE uin='{$luser->uin}' AND permission='nopicture'");
	}
	if($picture || $delpic) {
		if($delpic) {
			writeLog('editplayer', 'delpic', '', $luser->uin);
		} else {
			writeLog('editplayer', 'uploadpic', '', $luser->uin);
		}
		processPicture($picture, $delpic, $luser);
	}
	if($email != $luser->email) {
		writeLog('editplayer', 'changeemail', array('old' => $luser->email, 'new' => $email), $luser->uin);
		processEmail($email, $luser);
	}
	if($newpass) {
		if($newpass != $retpass) {
			echo '<span class="error">New passwords don\'t match</span><br />';
		} else {
			writeLog('editplayer', 'changepass', '', $luser->uin);
			$luser->updatePassword($newpass);
		}
	}
	if($name != $luser->name) {
		writeLog('editplayer', 'changename', array('old' => $luser->name, 'new' => $name), $luser->uin);
		processName($name, $luser);
	}
	//username switching
	//log this to a sep. base log type because mods shouldn't be switching this willy-nilly
	if($settings['change usernames']) {
		$username = strtolower($_POST['username']); //username, forced into lowercase (thus making it case-insensitive)
		if($username != $luser->username) {
			if(preg_match('/[a-z][a-z0-9. -]+[a-z0-9]/', $username)) {
				//valid, check if it exists in db
				$res = $db->query("SELECT * FROM users WHERE username='$username'");
				if($res->numRows()) {
					//it is
					echo '<span class="error">Specified username is already in use by another account</span><br />';
					writeLog('username', 'inuse', $username, $luser->uin);
				} else {
					//valid username
					writeLog('username', 'change', array('old' => $luser->username, 'new' => $username), $luser->uin);
					$db->query("UPDATE users SET username='$username' WHERE uin={$luser->uin}");
				}
			} else {
				echo '<span class="error">Invalid username specified</span><br />';
				writeLog('username', 'invalid', $username, $luser->uin);
			}
		}
	}
	//privs
	list( $permset, $permunset, $permgroups ) = getChangeableGroups( $user );
	$permcur = $luser->getPermissions();
	sort( $permcur );
	$permnew = $permcur;
	$permschanged = false;
	foreach( $permgroups as $g => $n ) {
		if( isset( $_POST["permission-$g"] ) && !$luser->isAllowed( $g ) && in_array( $g, $permset ) ) {
			$permnew[] = $g;
			$permschanged = true;
			$db->query("INSERT INTO permissions (uin,permission) VALUES({$luser->uin}, '{$g}')");
		} elseif( !isset( $_POST["permission-$g"] ) && $luser->isAllowed( $g ) && in_array( $g, $permunset ) ) {
			$permnew = array_diff( $permnew, array( $g ) );
			$permschanged = true;
			$db->query("DELETE FROM permissions WHERE uin={$luser->uin} AND permission='{$g}'");
		}
	}
	sort($permnew);
	if( $permschanged ) {
		writeLog('permissions', 'change', array('old' => $permcur, 'new' => $permnew), $luser->uin);
	}
	//refresh user
	editPlayer($uin);
} elseif(isset($_POST['massedit'])) {
	//mass edit a whole bunch of users (feed them if they are zombies, turn them to X faction, kick them, etc.)
	
} elseif(isset($_GET['edit'])) {
	//passed a UIN, so edit that player's profile
	$uin = $_GET['edit'];
	editPlayer($uin);
} elseif(isset($_GET['kick'])) {
	//kick 'em out!
	$uin = $_GET['kick'];
	writeLog('kick', 'kick', '', $uin);
	$luser = new User($uin);
	$luser->kick();
	echo 'You have successfully kicked the specified user<span id="redir"></span>';
} elseif(isset($_GET['ban'])) {
	//someone's been a bad boy (or girl)
	$uin = $_GET['ban'];
	writeLog('kick', 'ban', '', $uin);
	$luser = new User($uin);
	$luser->ban();
	echo 'You have successfully banned the specified user<span id="redir"></span>';
} elseif(isset($_GET['unban'])) {
	//second chances are lovely, yet totally unnecessary
	$uin = $_GET['unban'];
	writeLog('kick', 'unban', '', $uin);
	$luser = new User($uin);
	$luser->unban();
	echo 'You have successfully unbanned the specified user<span id="redir"></span>';
} elseif(isset($_GET['register'])) {
	//for all those idiots who don't seem to realize registering for site != registering for game
	$uin = $_GET['register'];
	$luser = new User($uin);
	$luser->register();
	writeLog('register', 'force', '', $luser->uin, $luser->id);
	$db->query("INSERT INTO emailqueue (`time`,`to`,`replyto`,`subject`,`message`) VALUES(NOW(), '{$luser->email}', '', 'Registration confirmation - HvZ', 'You have been registered by a Game Moderator for the current game of Texas A&M HvZ\r\nYour ID is {$luser->id}')");
	echo 'You have successfully registered the user. Their ID has been sent to them in an email<span id="redir"></span>';
} else {
	//make a list of everyone with an account (including non-registered users)
	if(isset($_GET['gd'])) {
		$faction = $_GET['af'];
		$pictures = isset($_GET['pi']);
		$username = isset($_GET['un']);
		$kills = isset($_GET['ki']);
		$fed = isset($_GET['tf']);
		$starved = isset($_GET['ts']);
		$turned = isset($_GET['tt']);
		$inactiveonly = isset($_GET['io']);
	} else {
		$faction = -6; //all
		$pictures = false; //no pictures
		$username = false; //no username
		$kills = false; //don't show # kills
		$fed = false; //don't show time fed
		$starved = false; //don't show time starved
		$turned = false; //don't show time turned
		$inactiveonly = false; //show everyone
	}
	listPlayers($faction, $pictures, $username, $kills, $fed, $starved, $turned, $inactiveonly);
}

function listPlayers($faction, $pictures, $username, $kills, $fed, $starved, $turned, $inactiveonly) {
	global $db, $settings;
	$where = isset($_GET['name']) ? mysql_real_escape_string($_GET['name']) : false;
	if($where) {
		$where = "WHERE users.name LIKE '%$where%'";
	} else {
		$where = '';
	}
	$res = $db->query("SELECT users.*,game.id,game.kills AS gkills,game.feeds AS gfeeds,game.turned,game.fed,game.starved FROM users LEFT JOIN game ON users.uin=game.uin $where ORDER BY users.name");
	echo '<form method="GET" action=""><div style="text-align: center">Name filter: <input type="text" name="name" value="' . (isset($_GET['name']) ? $_GET['name'] : '') . '" /><br /><select name="af">';
	$fs = $db->query("SELECT * FROM factions");
	$fns = array();
	while($f = $fs->fetchRow()) {
		$fns[$f->id] = (intval($f->flags) & 1 == 1) ? false : $f->name;
	}
	echo '<option value="-6"'.($faction == -6 ? ' selected="selected"' : '').'>All</option>
	<option value="-5"'.($faction == -5 ? ' selected="selected"' : '').'>Not registered</option>
	<option value="-4"'.($faction == -4 ? ' selected="selected"' : '').'>Registered</option>';
	foreach($fns as $id => $f) {
		if($f === false) continue;
		echo "<option value='{$id}'".($faction == $id ? ' selected="selected"' : '').">".($id == -2 ? 'OZs only' : $f)."</option>";
	}
	$ot = isset($_GET['ot']) ? $_GET['ot'] : 'last';
	$od = isset($_GET['od']) ? $_GET['od'] : 'asc';
	echo '</select>&nbsp;<select name="ot"><option value="last"'.($ot == 'last' ? 'selected="selected"' : '').'>Last name</option>
	<option value="first"'.($ot == 'first' ? 'selected="selected"' : '').'>First name</option>
	<option value="kills"'.($ot == 'kills' ? 'selected="selected"' : '').'>Kills</option>
	<option value="turn"'.($ot == 'turn' ? 'selected="selected"' : '').'>Time turned</option>
	<option value="fed"'.($ot == 'fed' ? 'selected="selected"' : '').'>Time fed</option>
	<option value="starve"'.($ot == 'starve' ? 'selected="selected"' : '').'>Time starved</option>
	<option value="loggedin"'.($ot == 'loggedin' ? 'selected="selected"' : '').'>Last login</option>
	<option value="aff"'.($ot == 'aff' ? 'selected="selected"' : '').'>Affiliation</option>
	</select>&nbsp;<select name="od"><option value="asc"'.($od == 'asc' ? 'selected="selected"' : '').'>Ascending</option>
	<option value="des"'.($od == 'des' ? 'selected="selected"' : '').'>Descending</option></select>&nbsp;
	<input type="submit" name="gd" value="Refresh" /><br />
	<input type="checkbox" name="pi" id="pi" value="1" '.($pictures ? 'checked="checked"' : '').'/><label for="pi">Pictures</label> 
	<input type="checkbox" name="un" id="un" value="1" '.($username ? 'checked="checked"' : '').'/><label for="un">Username</label> 
	<input type="checkbox" name="ki" id="ki" value="1" '.($kills ? 'checked="checked"' : '').'/><label for="ki">Kills</label> 
	<input type="checkbox" name="tf" id="tf" value="1" '.($fed ? 'checked="checked"' : '').'/><label for="tf">Time fed</label> 
	<input type="checkbox" name="ts" id="ts" value="1" '.($starved ? 'checked="checked"' : '').'/><label for="ts">Time starved</label> 
	<input type="checkbox" name="tt" id="tt" value="1" '.($turned ? 'checked="checked"' : '').'/><label for="tt">Time turned</label><br /> 
	<input type="checkbox" name="io" id="io" value="1" '.($inactiveonly ? 'checked="checked"' : '').'/><label for="io">Show inactive only</label></div>';
	echo '<input type="hidden" name="page" value="admin" /><input type="hidden" name="section" value="players" /></form><br />';
	echo '<form method="POST" action="?page=admin&section=players"><table class="admintable"><tr>';
	echo '<th><input type="checkbox" name="all" class="selectall" value="1" /></th>';
	if($pictures) echo '<th>Picture</th>';
	echo '<th>Name</th>'.($username?'<th>Username</th>':'').'<th>ID</th><th>Affiliation</th>';
	if($kills) echo '<th>Kills</th>';
	echo '<th>Last Login</th>';
	if($turned) echo '<th>Time Turned</th>';
	if($fed) echo '<th>Time Fed</th>';
	if($starved) echo '<th>Time Starved</th>';
	echo '<th>Actions</th></tr>';
	$count = 0;
	$rows = array();
	while($row = $res->fetchRow()) {
		$rows[] = $row;
	}
	sortRows($rows); //sort them rows! (this passes by reference)
	foreach($rows as $row) {
		switch($faction) {
			case -6: //all
				break;
			case -5: //unregistered
				if($row->registered) continue 2;
				break;
			case -4: //registered
				if(!$row->registered) continue 2;
				break;
			case -3: //deceased
				if(!$row->registered || $row->faction != -3) continue 2;
				break;
			case -2: //OZs only
				if(!$row->registered || $row->faction != -2) continue 2;
				break;
			case -1: //Horde (incl. OZs)
				if(!$row->registered || ($row->faction != -1 && $row->faction != -2)) continue 2;
				break;
			case 0:  //Resistance (incl. custom factions)
				//note that unregistered people are set to 0, so weed them out
				if(!$row->registered || $row->faction < 0) continue 2;
				break;
			default: //specific faction
				if(!$row->registered || $row->faction != $faction) continue 2;
				break;
		}
		$ts = strtotime($row->loggedin);
		$tsn = time();
		$tsd = $settings['inactivity time'] * 60 * 60;
		$inactive = false;
		if($tsn > $ts + $tsd) {
			$inactive = true;
		} elseif($inactiveonly) {
			continue;
		}
		echo '<tr><td><input type="checkbox" name="id-' . $row->uin . '" class="rowcheck" value="1" /></td>';
		$user = User::getDefaultUser();
		if($pictures) {
			$user->picture = $row->picture;
			$p = $user->getPicture(true);
			$aspect = $p[2] / $p[1];
			$w = 120;
			$h = 120 * $aspect;
			echo '<td><img src="' . $p[0] . '" alt="" width="'.$w.'" height="'.$h.'" /></td>';
		}
		if(!isset($row->id)) $row->id = '';
		echo "<td>{$row->name}</td>".($username?"<td>{$row->username}</td>":'')."<td>{$row->id}</td><td>";
		if($row->faction == 0) {
			if($row->registered) {
				echo $fns[0] . '</td>';
			} else {
				switch($row->status) {
					case 0:
						echo 'Not registered</td>';
						break;
					case 1:
						echo '<span style="color: #ff0000">Kicked</span></td>';
						break;
					case 2:
						echo '<span class="error">Banned</span></td>';
						break;
				}
			}
		} else {
			echo $fns[$row->faction] . '</td>';
		}
		$user->loggedintime = $row->loggedin;
		$user->starved = $row->starved;
		$user->fed = $row->fed;
		$user->turned = $row->turned;
		if($kills) {
			echo '<td>' . $row->gkills . '</td>';
		}
		echo '<td>' . ($inactive ? '<span class="error">' : '') . $user->getLoggedIn() . ($inactive ? '</span>' : '') . '</td>';
		if($turned) {
			echo '<td>' . ($row->faction < 0 ? $user->getTurnedTime() : '') . '</td>';
		}
		if($fed) {
			echo '<td>' . ($row->faction < 0 ? $user->getFedTime() : '') . '</td>';
		}
		if($starved) {
			echo '<td>' . ($row->faction == -3 ? $user->getStarvedTime() : '') . '</td>';
		}
		global $settings;
		echo "<td><a href='?page=admin&section=players&edit={$row->uin}'>edit</a> &bull; ";
		echo ($row->registered ? "<a href='#' onclick=\"confirmKick('?page=admin&section=players&kick={$row->uin}')\">kick</a> &bull; " : "");
		echo (($row->status != 2 && !$row->registered && $settings['game status'] >= 1) ? "<a href='#' onclick=\"confirmRegister('?page=admin&section=players&register={$row->uin}')\">register</a> &bull; " : "");
		echo ($row->status != 2 ? "<a href='#' onclick=\"confirmBan('?page=admin&section=players&ban={$row->uin}')\">ban</a>" : "<a href='?page=admin&section=players&unban={$row->uin}'>unban</a>") . "</td>";
		echo '</tr>';
		$count++;
	}
	echo '</table><br /><div style="text-align: center"><i>' . $count . ' player(s) listed</i></div>';
}

function editPlayer($uin) {
	global $db, $settings, $user;
	//single player edit
	$luser = new User($uin);
?>
<a href="?page=admin&section=players">&larr; Back to players listing</a>
<?php
	$dis = '';
	if($luser->isAllowed('godmode') && !$user->isAllowed('developer')) {
		$dis = 'disabled="disabled"';
?>
	<br /><span class="error">You are unable to edit this user. You may review the user's current settings but you cannot change them.<?= ($luser->registered && !$luser->getStatus()) ? " You are allowed to change this user's faction and/or feed them, however." : "" ?></span>
<?php
	}
?>
<form method="post" enctype="multipart/form-data" action="?page=admin&section=players&edit=<?= $uin ?>">
<h3 style="text-align: center;">Editing <?= $luser->name ?><?= $luser->registered ? ' (ID: ' . $luser->id . ')' : '' ?></h3>
<table class="admintable">
<tr><th>Item</th><th>Value</th><th>Additional Information</th></tr>
<?php if($settings['change usernames']) { ?>
<tr><td>Username</td><td><input type="text" name="username" value="<?= $luser->getUsername(); ?>" <?= $dis ?> /></td><td>&nbsp;</td></tr>
<?php } else { ?>
<tr><td>Username</td><td><?= $luser->getUsername(); ?></td><td><i>(You are not able to change usernames)</i></td></tr>
<?php } ?>
<?php if( $dis == '' ) { ?>
<tr><td>Password<br /><span class="label">New:</span><br /><span class="label">Retype:</span></td><td><br /><input type="password" name="newpass" /><br /><input type="password" name="retpass" /></td><td>Leave these blank if you do not wish to change the user's password</td></tr>
<?php } ?>
<tr><td>Email</td><td><input type="text" name="email" value="<?= $luser->getEmail() ?>" <?= $dis ?> /></td><td>&nbsp;</td></tr>
<tr><td>Name<br /><span class="label">First:</span><br /><span class="label">Last:</span><br /></td><td><br /><input type="text" name="fname" value="<?php $lusername = explode(' ',$luser->getName()); echo $lusername[0]; ?>" <?= $dis ?> /><br /><input type="text" name="lname" value="<?= isset($lusername[1]) ? $lusername[1] : ''; ?>" <?= $dis ?> /></td><td>&nbsp;</td></tr>
<tr><td>Picture</td><td><img src="<?= $luser->getPicture(false) ?>" alt="" /><br /><input type="file" name="picture" id="picture" <?= $dis ?> /><br /><input type="checkbox" name="delpic" id="delpic" value="1" <?= $dis ?> /><label for="delpic">Delete profile picture</label><br /><input type="checkbox" name="banpic" id="banpic" value="1" <?= $luser->isAllowed('nopicture') ? 'checked="checked"' : '' ?> <?= $dis ?> /><label for="banpic">Ban user from uploading pictures</label></td><td>Change/delete the user's current picture, or ban a user from uploading pictures<br /><div style="font-size: 80%">Acceptable formats: png, jpg, jpeg, gif (no animated gifs!)<br />Maximum dimensions: 300 pixels high by 300 pixels wide (<a href="http://www.picresize.com/" target="_blank">free online picture resizer</a>)<br />Maximum filesize: 1MB</div></td></tr>
<?php if($luser->registered && !$luser->getStatus()) { ?>
<tr><td>Affiliation</td><td>
<select name="faction">
<?php
$res = $db->query("SELECT * FROM factions");
while($row = $res->fetchRow()) {
	if(isDeletedFaction($row)) continue;
	?>
<option value="<?= $row->id ?>" <?= $luser->faction == $row->id ? 'selected="selected"' : '' ?>><?= $row->name ?></option>	
	<?php
}
?>
</select>
</td><td>Changing a user from zombie/deceased to a human will change their ID</td></tr>
<?php if($luser->faction == -2 || $luser->faction == -1) { ?>
<tr><td>Last fed</td><td><?= $luser->getFedTime(); ?><br /><input type="checkbox" name="feed" id="feed" value="1" /><label for="feed">Reset feed time</label></td><td>You may reset a Zombie's feed time without it counting against their game ratio</td></tr>
<?php } //end if zombie ?>
<?php } //end current game stuff ?>
<tr><td>Privileges</td>
<td>
<?php getPermissionCheckboxes($luser); ?>
</td><td>Modify user's privileges, such as Administrator status<br /><div style="font-size: 80%">A checked box means the user is in that group.<br />An unchecked box means the user is not in that group.<br />A * indicates that you cannot remove the group once you have added it, or vice versa.</div></td></tr>
</table><br />
<?php if( $dis == '' || ($luser->registered && !$luser->getStatus()) ) { ?>
<input type="submit" name="edituser" value="Submit" />
<?php } ?>
</form>
<?php
}

function getPermissionCheckboxes( $luser ) {
	global $user;
	list( $set, $unset, $groups ) = getChangeableGroups( $user );
	//apply godmode restrictions
	if( $user != $luser && $luser->isAllowed( 'godmode' ) && !$user->isAllowed( 'developer' ) ) {
		$set = array();
		$unset = array();
	}
	$changeable = array();
	$notchange = array();
	foreach( $groups as $g => $n ) {
		if( $luser->isAllowed( $g ) ) {
			$c = true;
			if( in_array( $g, $set ) ) {
				$e = true;
			} else {
				$e = false;
			}
			if( in_array( $g, $unset ) ) {
				$s = false;
			} else {
				$s = true;
			}
		} else {
			$c = false;
			if( in_array( $g, $unset ) ) {
				$e = true;
			} else {
				$e = false;
			}
			if( in_array( $g, $set ) ) {
				$s = false;
			} else {
				$s = true;
			}
		}
		if( $e ) {
			$changeable[] = array( 'g' => $g, 'n' => $n, 'c' => $c, 's' => $s );
		} else {
			$notchange[] = array( 'g' => $g, 'n' => $n, 'c' => $c );
		}
	}
	foreach( $changeable as $arr ) {
		echo '<input type="checkbox" ' . ( $arr['c'] ? 'checked="checked" ' : '' ) . "id=\"permission-$arr[g]\" name=\"permission-$arr[g]\" value=\"1\" /> <label for=\"permission-$arr[g]\">$arr[n]" . ($arr['s'] ? '*' : '') . "</label><br />\n";
	}
	foreach( $notchange as $arr ) {
		echo '<input type="checkbox" ' . ( $arr['c'] ? 'checked="checked" ' : '' ) . "disabled=\"disabled\" /> $arr[n]<br />\n";
	}
}

function getChangeableGroups( $luser ) {
	global $user;
	//first define what groups are valid along with their descriptions, as well as what groups each group can change
	//if you add groups here also add them to logs.php in groupName()
	$groups = array(
		'admin' => 'Administrator',
		'developer' => 'Developer',
		'godmode' => 'Cannot be edited',
		'mundo' => 'Reserved for Dr. Mundo',
		'ebul' => 'Evil (gives red name on forums)',
	);
	//stuff not listed here can't modify groups, list superusers first because of some shirt-circuit code paths in place for them
	$permissions = array(
		'developer' => true,
		'admin' => array( 'add' => array( 'admin', 'ebul' ), 'remove' => array( 'admin', 'ebul' ) ),
		'godmode' => array( 'removeself' => 'godmode' ),
	);
	//get a list of every group the user can add or remove
	$set = array();
	$unset = array();
	foreach( $permissions as $group => $addrem ) {
		if( $luser->isAllowed( $group ) ) {
			if( $addrem === true ) {
				//can do everything, so just exit out
				$set = array_keys( $groups );
				$unset = array_keys( $groups );
				break;
			} elseif( is_array( $addrem ) ) {
				$keys = array( 'add', 'remove', 'addself', 'removeself' );
				foreach( $keys as $k ) {
					if( ( $k == 'addself' || $k == 'removeself' ) && $user != $luser ) {
						continue;
					}
					if( isset( $addrem[$k] ) ) {
						if( $addrem[$k] === true ) {
							$addrem[$k] = array_keys( $groups );
						}
						if( is_array( $addrem[$k] ) ) {
							if( $k == 'add' || $k == 'addself' ) {
								$set = array_merge( $set, $addrem[$k] );
							} elseif( $k == 'remove' || $k == 'removeself' ) {
								$unset = array_merge( $unset, $addrem[$k] );
							}
						}
					}
				}
			}
		}
	}
	$set = array_unique( $set );
	$unset = array_unique( $unset );
	return array( $set, $unset, $groups );
}

function sortRows(&$rows) {
	//sort rows
	$order = isset($_GET['ot']) ? $_GET['ot'] : 'last';
	$direction = isset($_GET['od']) ? $_GET['od'] : 'asc';
	if($order == 'first') {
		if($direction == 'asc') {
			return; //MySQL did this for us, yay
		} else {
			$rows = array_reverse($rows);
			return;
		}
	}
	if($order == 'last') {
		usort($rows, "sort_lastname");
		if($direction == 'des') {
			$rows = array_reverse($rows);
		}
		return;
	}
	if($order == 'kills') {
		usort($rows, "sort_kills");
		if($direction == 'des') {
			$rows = array_reverse($rows);
		}
		return;
	}
	if($order == 'turn') {
		usort($rows, "sort_turn");
		if($direction == 'des') {
			$rows = array_reverse($rows);
		}
		return;
	}
	if($order == 'fed') {
		usort($rows, "sort_fed");
		if($direction == 'des') {
			$rows = array_reverse($rows);
		}
		return;
	}
	if($order == 'starve') {
		usort($rows, "sort_starve");
		if($direction == 'des') {
			$rows = array_reverse($rows);
		}
		return;
	}
	if($order == 'loggedin') {
		usort($rows, "sort_loggedin");
		if($direction == 'des') {
			$rows = array_reverse($rows);
		}
		return;
	}
	if($order == 'aff') {
		usort($rows, "sort_aff");
		if($direction == 'des') {
			$rows = array_reverse($rows);
		}
		return;
	}
}

function sort_lastname($a, $b) {
	$pa = explode(' ', $a->name);
	$pb = explode(' ', $b->name);
	$la = array_pop($pa);
	$lb = array_pop($pb);
	return strcasecmp($la, $lb);
}

function sort_kills($a, $b) {
	if($a->gkills < $b->gkills) {
		return -1;
	} elseif($a->gkills == $b->gkills) {
		return 0;
	} else {
		return 1;
	}
}

function sort_turn($a, $b) {
	if($a->faction >= 0) {
		if($b->faction >= 0) {
			return 0;
		} else {
			return 1;
		}
	} elseif($b->faction >= 0) {
		return -1;
	} else {
		$ta = strtotime($a->turned);
		$tb = strtotime($b->turned);
		if($ta < $tb) {
			return -1;
		} elseif($ta == $tb) {
			return 0;
		} else {
			return 1;
		}
	}
}	
function sort_fed($a, $b) {
	if($a->faction >= 0) {
		if($b->faction >= 0) {
			return 0;
		} else {
			return 1;
		}
	} elseif($b->faction >= 0) {
		return -1;
	} else {
		$ta = strtotime($a->fed);
		$tb = strtotime($b->fed);
		if($ta < $tb) {
			return -1;
		} elseif($ta == $tb) {
			return 0;
		} else {
			return 1;
		}
	}
}		
function sort_starve($a, $b) {
	if($a->faction >= -2) {
		if($b->faction >= -2) {
			return 0;
		} else {
			return 1;
		}
	} elseif($b->faction >= -2) {
		return -1;
	} else {
		$ta = strtotime($a->starved);
		$tb = strtotime($b->starved);
		if($ta < $tb) {
			return -1;
		} elseif($ta == $tb) {
			return 0;
		} else {
			return 1;
		}
	}
}		
function sort_loggedin($a, $b) {
	$ta = strtotime($a->loggedin);
	$tb = strtotime($b->loggedin);
	if($ta < $tb) {
		return -1;
	} elseif($ta == $tb) {
		return 0;
	} else {
		return 1;
	}
}				
function sort_aff($a, $b) {
	$user = User::getDefaultUser();
	if(!$a->registered) {
		switch($a->status) {
			case 0:
				$fa = 'Not registered';
				break;
			case 1:
				$fa = 'Kicked';
				break;
			case 2:
				$fa = 'Banned';
				break;
		}
	} else {
		$user->faction = $a->faction;
		$fa = $user->getFactionName();
	}
	if(!$b->registered) {
		switch($b->status) {
			case 0:
				$fb = 'Not registered';
				break;
			case 1:
				$fb = 'Kicked';
				break;
			case 2:
				$fb = 'Banned';
				break;
		}
	} else {
		$user->faction = $b->faction;
		$fb = $user->getFactionName();
	}
	return strcasecmp($fa, $fb);
}
function isDeletedFaction($id) {
	if(is_object($id)) {
		$row = $id;
	} else {
		global $db;
		$res = $db->query("SELECT * FROM factions WHERE id=$id");
		$row = $res->fetchRow();
	}
	return intval( $row->flags ) & 1 == 1;
}
?>
<script type="text/javascript">
function confirmKick(uri) {
	if(confirm("Kicking this user will remove them from the current game. If you do this in error, you may re-register them, but they will get a new ID.\n\nAre you sure you want to kick this user?")) {
		window.location = window.location.href.split('?')[0] + uri;
	} else {
		return;
	}
}

function confirmBan(uri) {
	if(confirm("Banning this user will remove them from the current game and prevent them from registering for new games.\n\nAre you sure you want to ban this user?")) {
		window.location = window.location.href.split('?')[0] + uri;
	} else {
		return;
	}
}

function confirmRegister(uri) {
	if(confirm("You are about to register this user for the current game. They will be sent an email with their ID.\nIf you wish to set this user to a specific faction, you will need to edit them after you register them\n\nAre you sure you want to register this user?")) {
		window.location = window.location.href.split('?')[0] + uri;
	} else {
		return;
	}
}

if(document.getElementById('redir')) {
	var inner = document.getElementById('redir').innerHTML;
	var extra = '';
	if(inner) {
		extra = '&edit=' + inner;
	}
	setTimeout('window.location = window.location.href.split("?")[0] + "?page=admin&section=players'+extra+'";', 2500);
}
if(document.getElementById('delpic')) {
	if(document.addEventListener) {
		document.getElementById('delpic').addEventListener('click', togglePicture, false);
	} else if(document.attachEvent) {
		document.getElementById('delpic').attachEvent('onclick', togglePicture);
	}
}
function togglePicture() {
	if(document.getElementById('delpic').checked) {
		document.getElementById('picture').value = '';
		document.getElementById('picture').disabled = true;
	} else {
		document.getElementById('picture').disabled = false;
	}
}

</script>
