<?php if(!defined('HVZ')) die(-1); ?>
<h1>Player List</h1>
<?php
if(isset($_GET['gd'])) {
	$faction = $_GET['af'];
	$pictures = isset($_GET['pi']);
	$kills = isset($_GET['ki']);
	$fed = isset($_GET['tf']);
	$starved = isset($_GET['ts']);
	$turned = isset($_GET['tt']);
} else {
	$faction = -4; //all
	$pictures = false; //don't show pictures
	$kills = true; //show # kills
	$fed = false; //don't show time fed
	$starved = false; //don't show time starved
	$turned = false; //don't show time turned
}
listPlayers($faction, $pictures, $kills, $fed, $starved, $turned);

function listPlayers($faction, $pictures, $kills, $fed, $starved, $turned) {
	global $db, $settings;
	$res = $db->query("SELECT users.*,game.kills AS gkills,game.feeds AS gfeeds,game.turned,game.fed,game.starved FROM users LEFT JOIN game ON game.game={$settings['current game']} AND users.uin=game.uin ORDER BY users.name");
	echo '<form method="GET" action=""><div style="text-align: center"><select name="af">';
	$fs = $db->query("SELECT * FROM factions");
	$fns = array();
	while($f = $fs->fetchRow()) {
		$fns[$f->id] = (intval($f->flags) & 1 == 1) ? false : $f->name;
	}
	echo '<option value="-4"'.($faction == -4 ? ' selected="selected"' : '').'>All</option>
	<option value="-3"'.($faction == -3 ? ' selected="selected"' : '').'>'.$fns['-3'].'</option>
	<option value="-1"'.($faction == -1 ? ' selected="selected"' : '').'>'.$fns['-1'].'</option>
	<option value="0"'.($faction == 0 ? ' selected="selected"' : '').'>'.$fns['0'].'</option>';
	foreach($fns as $id => $f) {
		if($f === false || $id < 1) continue;
		echo "<option value='{$id}'".($faction == $id ? ' selected="selected"' : '').">{$f}</option>";
	}
	$ot = isset($_GET['ot']) ? $_GET['ot'] : 'last';
	$od = isset($_GET['od']) ? $_GET['od'] : 'asc';
	echo '</select>&nbsp;<select name="ot"><option value="last"'.($ot == 'last' ? 'selected="selected"' : '').'>Last name</option>
	<option value="first"'.($ot == 'first' ? 'selected="selected"' : '').'>First name</option>
	<option value="kills"'.($ot == 'kills' ? 'selected="selected"' : '').'>Kills</option>
	<option value="turn"'.($ot == 'turn' ? 'selected="selected"' : '').'>Time turned</option>
	<option value="fed"'.($ot == 'fed' ? 'selected="selected"' : '').'>Time fed</option>
	<option value="starve"'.($ot == 'starve' ? 'selected="selected"' : '').'>Time starved</option>
	<option value="aff"'.($ot == 'aff' ? 'selected="selected"' : '').'>Affiliation</option>
	</select>&nbsp;<select name="od"><option value="asc"'.($od == 'asc' ? 'selected="selected"' : '').'>Ascending</option>
	<option value="des"'.($od == 'des' ? 'selected="selected"' : '').'>Descending</option></select>&nbsp;
	<input type="submit" name="gd" value="Refresh" /><br />
	<input type="checkbox" name="pi" id="pi" value="1" '.($pictures ? 'checked="checked"' : '').'/><label for="pi">Pictures</label> 
	<input type="checkbox" name="ki" id="ki" value="1" '.($kills ? 'checked="checked"' : '').'/><label for="ki">Kills</label> 
	<input type="checkbox" name="tf" id="tf" value="1" '.($fed ? 'checked="checked"' : '').'/><label for="tf">Time fed</label> 
	<input type="checkbox" name="ts" id="ts" value="1" '.($starved ? 'checked="checked"' : '').'/><label for="ts">Time starved</label> 
	<input type="checkbox" name="tt" id="tt" value="1" '.($turned ? 'checked="checked"' : '').'/><label for="tt">Time turned</label></div>';
	echo '<input type="hidden" name="page" value="main" /><input type="hidden" name="tab" value="players" /></form><br />';
	$cr = $db->query("SELECT (SELECT COUNT(*) FROM users WHERE registered=1 AND faction>=0) AS humans, (SELECT COUNT(*) FROM users WHERE registered=1 AND (faction=-1 OR faction=-2)) AS zombies, (SELECT COUNT(*) FROM users WHERE registered=1 AND faction=-3) AS deceased");
	$counts = $cr->fetchRow();
	//get a list of OZs to see which ones should be marked as resistance (based on their turn times)
	$ozq = $db->query('SELECT * FROM users LEFT JOIN game ON users.uin=game.uin WHERE registered=1 AND faction=-2');
	while($row = $ozq->fetchRow()) {
		if($settings['game status'] < 4 ||
			($settings['game status'] == 4
				&& (time() - strtotime($row->turned))/3600 - $settings['oz hide'] < 0
			)
		) {
			$counts->zombies--;
			$counts->humans++;
		}
	}
	$ct = $counts->humans + $counts->zombies + $counts->deceased;
	echo "<i>Quick stats: {$counts->humans} Resistance, {$counts->zombies} Horde, {$counts->deceased} Deceased, {$ct} Total</i><br />";
	echo '<table class="prettytable"><tr>';
	if($pictures) echo '<th style="width: 100px">Picture</th>';
	echo '<th>Name</th><th>Affiliation</th>';
	if($kills) echo '<th>Kills</th>';
	if($turned) echo '<th>Time Turned</th>';
	if($fed) echo '<th>Time Fed</th>';
	if($starved) echo '<th>Time Starved</th>';
	$count = 0;
	$rows = array();
	while($row = $res->fetchRow()) {
		//do mangling for hiding OZs here BEFORE we sort
		if($row->faction == -2 && ($settings['game status'] < 4
			|| ($settings['game status'] == 4
					&& (time() - strtotime($row->turned))/3600 - $settings['oz hide'] < 0
				)
		)) {
			//find user's previous faction from the logs and set that as faction
			$logq = $db->query("SELECT * FROM logging WHERE target={$row->uin} AND action='editplayer/changefaction' ORDER BY time DESC LIMIT 1");
			$logrow = $logq->fetchRow();
			$logval = unserialize($logrow->description);
			$row->faction = $logval['old'];
			if($row->faction < 0) $row->faction = 0; //just in case the logs are weird or something
			$row->gkills = '0';
		}
		$rows[] = $row;
	}
	sortRows($rows); //sort them rows! (this passes by reference)
	foreach($rows as $row) {
		if(!$row->registered) continue;
		switch($faction) {
			case -4: //all
				break;
			case -3: //deceased
				if($row->faction != -3) continue 2;
				break;
			case -2: //Horde (in admins this is OZs only, here we show all the Horde)
			case -1: //Horde (incl. OZs)
				if($row->faction != -1 && $row->faction != -2) continue 2;
				break;
			case 0:  //Resistance (incl. custom factions)
				if($row->faction < 0) continue 2;
				break;
			default: //specific faction
				if($row->faction != $faction) continue 2;
				break;
		}
		echo '<tr>';
		$user = User::getDefaultUser();
		if($pictures) {
			$user->picture = $row->picture;
			$p = $user->getPicture(false);
			echo '<td><img src="' . $p . '" alt="" /></td>';
		}
		echo "<td>{$row->name}</td><td>" . $fns[$row->faction] . '</td>';
		$user->starved = $row->starved;
		$user->fed = $row->fed;
		$user->turned = $row->turned;
		if($kills) {
			echo '<td>' . $row->gkills . '</td>';
		}
		if($turned) {
			echo '<td>' . ($row->faction < 0 ? $user->getTurnedTime() : '') . '</td>';
		}
		if($fed) {
			echo '<td>' . ($row->faction < 0 ? $user->getFedTime() : '') . '</td>';
		}
		if($starved) {
			echo '<td>' . ($row->faction == -3 ? $user->getStarvedTime() : '') . '</td>';
		}
		echo '</tr>';
		$count++;
	}
	echo '</table><br /><div style="text-align: center"><i>' . $count . ' player(s) listed</i></div>';
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
function sort_aff($a, $b) {
	$user = User::getDefaultUser();
	$user->faction = $a->faction;
	$fa = $user->getFactionName();
	$user->faction = $b->faction;
	$fb = $user->getFactionName();
	return strcasecmp($fa, $fb);
}
?>
