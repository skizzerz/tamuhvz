<?php if(!defined('HVZ')) die(-1); ?>
<h1>Edit Factions</h1>
<?php
if($settings['factions'] == 0) {
	echo '<span class="error">Factions are disabled</span>';
	return;
}
//get current factions
$res = $db->query("SELECT factions.*,(SELECT COUNT(*) FROM users WHERE factions.id = users.faction AND users.registered = 1) AS count FROM factions");
$factions = array();
while($row = $res->fetchRow()) {
	$factions[$row->id] = $row;
}
$res->freeResult();
if(isset($_POST['submit'])) {
	foreach($factions as $faction) {
		//see if any were deleted or changed
		$name = $faction->name;
		$id = $faction->id;
		if(isset($_POST["delete$id"]) && canBeDeleted($id)) {
			//deleting a faction, will reset all users belonging to it
			writeLog('faction', 'delete', array('id' => $id, 'name' => $name));
			$db->query("UPDATE factions SET flags=flags+1 WHERE id=$id");
			$db->query("UPDATE users SET faction=0 WHERE faction=$id");
		} elseif($_POST["faction$id"] != $name && canBeRenamed($id)) {
			//changing faction name
			$fn = mysql_real_escape_string($_POST["faction$id"]);
			writeLog('faction', 'rename', array('id' => $id, 'oldname' => $name, 'newname' => $fn));
			$db->query("UPDATE factions SET name='$fn' WHERE id=$id");
		}
	}
	if($_POST['new'] != '') {
		//add a new faction
		$fn = mysql_real_escape_string($_POST['new']);
		writeLog('faction', 'create', $fn);
		$db->query("INSERT INTO factions (name) VALUES('$fn')");
	}
	//rebuild $factions
	$res = $db->query("SELECT factions.*,(SELECT COUNT(*) FROM users WHERE factions.id = users.faction AND users.registered = 1) AS count FROM factions");
	$factions = array();
	while($row = $res->fetchRow()) {
		$factions[$row->id] = $row;
	}
}
?>
You may add, rename, and delete factions here.
<ul>
<li>To <b>add</b> a faction, type its name into the blank text box at the bottom of the form. You may only add one faction at a time</li>
<li>To <b>rename</b> a faction, change the name inside of the respective text box</li>
<li>To <b>delete</b> a faction, check the box that says "Delete" next to the faction in question. Users currently in this faction will be placed into the "<?= $factions[0]->name ?>" group</li>
</ul>
Hit the submit button to make changes take effect. You may add, rename, and delete factions all in one submit<br /><br />
<form method="post" action="?page=admin&section=factions">
<table class="admintable">
<tr><th>Faction</th><th>Number of members</th><th>Delete?</th></tr>
<?php
foreach($factions as $faction) {
	if( isDeleted( $faction->id ) ) {
		continue;
	}
	echo '<tr><td>';
	if( canBeRenamed( $faction->id ) ) {
		echo "<input type='text' name='faction{$faction->id}' value='{$faction->name}' />";
	} else {
		echo $faction->name . ' <i>(Faction cannot be renamed)</i>';
	}
	echo "</td><td>{$faction->count}</td><td>";
	if( canBeDeleted( $faction->id ) ) {
		echo "<input type='checkbox' name='delete{$faction->id}' value='1' />";
	} else {
		echo '<i>(Faction cannot be deleted)</i>';
	}
	echo '</td></tr>';
}
?>
<tr><td><input type="text" name="new" value="" /></td><td><i>(New faction)</i></td><td>&nbsp;</td></tr>
</table><br />
<input type="submit" name="submit" value="Submit" />
</form>
<?php

function isDeleted( $id ) {
	global $factions;
	return intval( $factions[$id]->flags ) & FLAG_DELETED == FLAG_DELETED;
}

function isSystem( $id ) {
	global $factions;
	return intval( $factions[$id]->flags ) & FLAG_SYSTEM == FLAG_SYSTEM;
}

function isImmutable( $id ) {
	global $factions;
	return intval( $factions[$id]->flags ) & FLAG_IMMUTABLE == FLAG_IMMUTABLE;
}

function canBeDeleted( $id ) {
	global $factions;
	return ( intval( $factions[$id]->flags ) ^ ( FLAG_SYSTEM | FLAG_IMMUTABLE ) ) == ( FLAG_SYSTEM | FLAG_IMMUTABLE );
}

function canBeRenamed( $id ) {
	global $factions;
	return intval( $factions[$id]->flags ) ^ FLAG_IMMUTABLE == FLAG_IMMUTABLE;
}