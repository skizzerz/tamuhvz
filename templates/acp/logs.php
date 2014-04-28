<?php
//allows admins to view and search logs
if(!defined('HVZ')) die(-1);

//display sort controls and stuff
$names = array(
	'all' => 'All logs',
	'content' => 'Content log',
	'editplayer' => 'Admin log',
	'email' => 'Email log',
	'faction' => 'Faction log',
	'game' => 'Game log',
	'kick' => 'Kick/ban log',
	'kill' => 'Kill log',
	'permissions' => 'Permissions log',
	'register' => 'Registration log',
	'settings' => 'Settings log',
	'username' => 'Username log',
	'mission' => 'Mission log',
);

$messages = array(
	'content/edit' => 'edited $3',
	'editplayer/changefaction' => 'changed faction of $1 from "#$3" to "#$4"',
	'editplayer/changename' => 'renamed $1 from "$3" to "$4"',
	'editplayer/changepass' => 'changed $1\'s password',
	'editplayer/delpic' => 'deleted $1\'s profile picture',
	'editplayer/uploadpic' => 'uploaded a new profile picture for $1',
	'editplayer/banpic' => 'banned $1 from uploading profile pictures',
	'editplayer/unbanpic' => 'unbanned $1 from uploading profile pictures',
	'editplayer/changeemail' => 'changed $1\'s email address from "$3" to "$4"',
	'editplayer/feed' => 'reset the starve timer on $1',
	'editplayer/removeozapp' => 'removed $1\'s OZ application',
	'username/change' => 'changed $1\'s username from "$3" to "$4"',
	'username/inuse' => 'tried to change $1\'s username to "$3", but the username was already in use by another account',
	'username/invalid' => 'tried to change $1\'s username, but the username was invalid',
	'permissions/addadmin' => 'gave administrator access to $1',
	'permissions/deladmin' => 'removed administrator access from $1',
	'permissions/change' => 'changed $1\'s permissions from %@3 to %@4',
	'kick/kick' => 'kicked $1 from the game',
	'kick/ban' => 'banned $1 from the game',
	'kick/unban' => 'unbanned $1 from the game',
	'register/force' => 'force-registered $1 into the game',
	'register/register' => 'registered for the game',
	'register/lateregister' => 'registered late for the game as #$3',
	'register/suicide' => 'left the game',
	'kill/invalid' => 'entered an invalid id',
	'kill/used' => 'tried to kill a user who was already killed, id "$3"',
	'kill/kill' => 'killed $1, id "$2"',
	'kill/starve' => 'died of starvation',
	'kill/unknown' => 'entered a valid but unused id "$3"',
	'email/send' => 'sent an email to #$3',
	'email/get' => 'retrieved emails for #$3',
	'faction/create' => 'created faction "$3"',
	'faction/rename' => 'renamed faction from "$4" to "$5"',
	'faction/delete' => 'deleted faction "#$3"',
	'game/advance' => 'advanced game to stage $3',
	'game/pause' => 'paused the game',
	'game/unpause' => 'unpaused the game',
	'mission/redeem' => 'redeemed code $3 in faction #$4',
	'settings' => 'changed setting "$0" from "$3" to "$4"', //settings is a special case
);

$dateselect = array(
	'1' => 'Last day',
	'3' => 'Last 3 days',
	'7' => 'Last 7 days',
	'14' => 'Last 14 days',
	'30' => 'Last 30 days',
	'90' => 'Last 90 days',
	'180' => 'Last 180 days',
	'365' => 'Last 365 days',
	'0' => 'Beginning of time',
);

$showselect = array(
	'50' => '50 entries',
	'100' => '100 entries',
	'250' => '250 entries',
	'500' => '500 entries',
	'0' => 'Everything',
);

if( isset( $_GET['log'] ) && array_key_exists( $_GET['log'], $names ) ) {
	$logtype = $_GET['log'];
} else {
	$logtype = 'all';
}

if( isset( $_GET['days'] ) && array_key_exists( $_GET['days'], $dateselect ) ) {
	$days = $_GET['days'];
} else {
	$days = '7';
}

if( isset( $_GET['show'] ) && array_key_exists( $_GET['show'], $showselect ) ) {
	$show = $_GET['show'];
} else {
	$show = '50';
}

$nameFilter = isset( $_GET['name'] ) ? $_GET['name'] : '';
$showUsernames = isset( $_GET['un'] ) && $_GET['un'] == 1;
$showIPs = isset( $_GET['ip'] ) && $_GET['ip'] == 1;

//get the logs
if( $logtype != 'all' ) {
	$where = "WHERE type='$logtype'";
} else {
	$where = '';
}
if( $days != 0 ) {
	if( $where != '' ) {
		$where .= " AND time >= CURRENT_TIMESTAMP() - INTERVAL $days DAY";
	} else {
		$where = "WHERE time >= CURRENT_TIMESTAMP() - INTERVAL $days DAY";
	}
}
if( $show != 0 ) {
	$limit = "LIMIT $show";
} else {
	$limit = '';
}
$res = $db->query( "SELECT * FROM logging $where ORDER BY time DESC $limit" );
$rows = array();
while( $row = $res->fetchRow() ) {
	$rows[] = $row;
}
?>
<h1>View Logs</h1>
<form method="get" action="">
<center>
<label for="name">Name filter:</label> <input type="text" name="name" value="<?= $nameFilter ?>" /><br />
<select name="log">
<?php foreach( $names as $type => $desc ) { ?>
	<option value="<?= $type ?>"<?= $logtype == $type ? ' selected="selected"' : '' ?>><?= $desc ?></option>
<?php } ?>
</select>
<select name="days">
<?php foreach( $dateselect as $num => $desc ) { ?>
	<option value="<?= $num ?>"<?= $days == $num ? ' selected="selected"' : '' ?>><?= $desc ?></option>
<?php } ?>
</select>
<select name="show">
<?php foreach( $showselect as $num => $desc ) { ?>
	<option value="<?= $num ?>"<?= $show == $num ? ' selected="selected"' : '' ?>><?= $desc ?></option>
<?php } ?>
</select>
<input type="hidden" name="page" value="admin" /><input type="hidden" name="section" value="logs" /><input type="submit" value="Refresh" /><br />
<input type="checkbox" id="un" name="un" value="1"<?= $showUsernames ? ' checked="checked"' : '' ?> /><label for="un">Usernames</label>
<input type="checkbox" id="ip" name="ip" value="1"<?= $showIPs ? ' checked="checked"' : '' ?> /><label for="ip">IP addresses</label>
</center>
</form>
<?php
$lastday = 100000000;
$close = false;
$shown = 0;
foreach( $rows as $row ) {
	$logLink = "<a href=\"?name={$nameFilter}&log={$row->type}&days={$days}&show={$show}&un={$showUsernames}&ip={$showIPs}&page=admin&section=logs\">{$names[$row->type]}</a>";
	$curday = date( 'Ymd', strtotime( $row->time ) );
	$dateday = date( 'j F Y', strtotime( $row->time ) );
	$datehour = date( 'H:i', strtotime( $row->time ) );
	$userLink = generateUserLink( $row->user, $showUsernames );
	if( $row->type != 'settings' && strpos( $messages[$row->action], '$1' ) !== false ) {
		$targetLink = generateUserLink( $row->target, $showUsernames );
	} else {
		$targetLink = '';
	}
	if( $nameFilter != '' && strpos( strtolower( $userLink ), strtolower( $nameFilter ) ) === false && strpos( strtolower( $targetLink ), strtolower( $nameFilter ) ) === false ) {
		continue;
	}
	$shown++;
	if( $curday < $lastday ) {
		$lastday = $curday;
		if( $close ) {
			echo '</ul>';
		}
		$close = true;
		echo "<h3>$dateday</h3><ul>";
	}

	echo "<li>($logLink); $datehour . . $userLink " . ($showIPs ? "($row->ip) " : '') . parseDescription( $row, $targetLink ) . '</li>';
}
if( $close ) echo '</ul>';
if( $shown == 0 ) echo 'No log entries match the search criteria';

function generateUserLink( $uin, $username = false ) {
	global $db;
	$res = $db->query("SELECT username,name FROM users WHERE uin=$uin");
	$user = $res->fetchRow();
	return '<a href="?page=admin&section=players&edit=' . $uin . '">' . ($username ? $user->username : $user->name) . '</a>';
}

function parseDescription( $row, $targetLink ) {
	global $messages;
	$action = explode( '/', $row->action, 2 );
	$action = $action[1];
	$desc = unserialize( $row->description );
	if( $row->type == 'settings' ) {
		$message = $messages['settings'];
	} else {
		$message = $messages[$row->action];
	}
	//do scalar replacements first
	$replacements = array(
		$action,
		$targetLink,
		$row->targetid
	);
	if( $desc ) {
		if( !is_array( $desc ) ) {
			$desc = array( $desc );
		}
		foreach( $desc as $item ) {
			if( is_array( $item ) ) {
				$replacements[] = "Array";
			} else {
				$replacements[] = $item;
			}
		}
	} else {
		$desc = array();
	}
	$find = array();
	for( $i = 0; $i < count( $replacements ); $i++ ) {
		$find[] = '$' . $i;
	}
	$message = str_replace( $find, $replacements, $message );
	//I hate special cases
	$replacements = array(
		$action,
		$targetLink,
		$row->targetid
	);
	foreach( $desc as $item ) {
		$replacements[] = flattenArray( $item, '#' );
	}
	$find = array();
	for( $i = 0; $i < count( $replacements ); $i++ ) {
		$find[] = '#@' . $i;
	}
	$message = str_replace( $find, $replacements, $message );
	$replacements = array(
		$action,
		$targetLink,
		$row->targetid
	);
	foreach( $desc as $item ) {
		$replacements[] =  flattenArray( $item, '%' );
	}
	$find = array();
	for( $i = 0; $i < count( $replacements ); $i++ ) {
		$find[] = '%@' . $i;
	}
	$message = str_replace( $find, $replacements, $message );
	//now do array replacements (map @0 to @2 to be the same as $0 to $2 for now, we might eventually have other special functionality for those)
	$replacements = array(
		$action,
		$targetLink,
		$row->targetid
	);
	foreach( $desc as $item ) {
		$replacements[] = flattenArray( $item );
	}
	$find = array();
	for( $i = 0; $i < count( $replacements ); $i++ ) {
		$find[] = '@' . $i;
	}
	$message = str_replace( $find, $replacements, $message );
	//fix faction names
	$message = preg_replace( '/#(-?[0-9]+)/e', 'factionName("$1")', $message );
	//fix group names
	$message = preg_replace( '/%([a-z0-9]+)/e', 'groupName("$1")', $message );
	return $message;
}

function flattenArray( $arr, $prefix = '' ) {
	if( !is_array( $arr ) ) {
		return $arr;
	}
	if( $arr == array() ) {
		return 'None';
	}
	$ret = '';
	$first = true;
	foreach( $arr as $ele ) {
		if( !$first ) {
			$ret .= ', ';
		} else {
			$first = false;
		}
		$ret .= $prefix . $ele;
	}
	return $ret;
}

function groupName( $group ) {
	//if you add groups here also add them in players.php in getChangeableGroups()
	$groups = array(
		'admin' => 'Administrator',
		'developer' => 'Developer',
		'godmode' => 'Cannot be edited',
		'mundo' => 'Reserved for Dr. Mundo',
		'ebul' => 'Evil (gives red name on forums)',
	);
	if( isset( $groups[$group] ) ) {
		return $groups[$group];
	} else {
		return $group;
	}
}

function factionName( $id ) {
	global $db;
	$res = $db->query("SELECT * FROM factions");
	while( $row = $res->fetchRow() ) {
		$factions[$row->id] = $row->name;
	}
	if( array_key_exists( $id, $factions ) ) {
		return $factions[$id];
	} else {
		//special cases
		switch($id) {
			case -6:
				return "Everyone";
			case -5:
				return "All Unregistered Players";
			case -4:
				return "All Registered Players";
		}
	}
	return $id;
}
