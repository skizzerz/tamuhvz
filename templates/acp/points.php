<?php if (!defined('HVZ')) die(-1); ?>
<h1>Missions</h1>
<p>You can generate mission IDs and assign point values to them on this page. Be sure to give the mission ID out to the players so they can input it (the debriefing at the end is a good time for this). New missions will appear at the bottom of the table.</p>
<?php
// get some variables set up
$res = $db->select('factions', '*');
$factions = array();
while ($row = $res->fetchRow()) {
	// only show factions which exist and exclude Deceased and OZ (OZs get credited as regular zombies)
	if (!($row->flags & 1) && $row->id > -2) {
		$factions[$row->id] = $row->name;
	}
}

// Check if we got posted to
if (isset($_POST['addmission'])) {
	// adding a mission
	// first we generate a unique 4-digit identifier for the mission and use that as the mission id
	// then we insert it into mission_info
	// then we insert rows into mission_results, one per faction
	do {
		$mid = mt_rand(0, 9999);
		$res = $db->select('mission_info', '*', array('game' => $settings['current game'], 'mission' => $mid));
	} while($res->numRows());

	$values = '';

	foreach ($factions as $fid => $name) {
		if ($values !== '') {
			$values .= ',';
		}

		$values .= "({$settings['current game']}, {$mid}, {$fid})";
	}

	$db->query("INSERT INTO mission_info (game, mission, description) VALUES ({$settings['current game']}, {$mid}, '')");
	$db->query("INSERT INTO mission_results (game, mission, faction) VALUES {$values}");
}

$missions = array();
$nomissions = false;
$res = $db->query("SELECT mi.mission, mi.description, mi.points, mi.flags, GROUP_CONCAT(mr.faction ORDER BY mr.faction ASC) faction, GROUP_CONCAT(mr.points ORDER BY mr.faction ASC) faction_points, GROUP_CONCAT(mr.note ORDER BY mr.faction ASC) faction_notes FROM mission_info mi LEFT JOIN mission_results mr ON mi.game = mr.game AND mi.mission = mr.mission WHERE mi.game = {$settings['current game']} GROUP BY mi.mission ORDER BY mi.inserted DESC");
if (!$res->numRows()) {
	$nomissions = true;
} else {
	while ($row = $res->fetchRow()) {
		if ($row->flags & 1) continue;
		$k = explode(',', $row->faction);
		$p = explode(',', $row->faction_points);
		$n = explode(',', $row->faction_notes);
		$v = array();
		foreach ($p as $i => $s) {
			$v[] = array('points' => $s, 'note' => $n[$i]);
		}
		$row->factions = array_combine($k, $v);
		$missions[] = $row;
	}

	if (count($missions) == 0) {
		$nomissions = true;
	}
}

// check if we're deleting/modifying any missions
$deleted = array();
foreach ($missions as $i => &$m) {
	if (isset($_POST["delete{$m->mission}"])) { // deleting
		$m->flags |= 1;
		$db->update('mission_info', array('flags' => $m->flags), array('game' => $settings['current game'], 'mission' => $m->mission));
		$deleted[] = $i;
	} else {
		$update = array();
		if (isset($_POST["points{$m->mission}"])) { // updating points
			$update['points'] = max(intval($_POST["points{$m->mission}"]), 0);
			$m->points = $update['points'];
		}

		if (isset($_POST["desc{$m->mission}"])) { // updating description
			$update['description'] = $_POST["desc{$m->mission}"];
			$m->description = $update['description'];
		}

		if ($update !== array()) {
			$db->update('mission_info', $update, array('game' => $settings['current game'], 'mission' => $m->mission));
		}

		foreach ($factions as $fid => $name) {
			$update = array();

			if (isset($_POST["note{$m->mission}f{$fid}"])) { // updating a note
				$update['note'] = $_POST["note{$m->mission}f{$fid}"];
				$m->factions[$fid]['note'] = $update['note'];
			}

			if (isset($_POST["points{$m->mission}f{$fid}"])) { // updating points
				$update['points'] = max(intval($_POST["points{$m->mission}f{$fid}"]), 0); // ensure it is never negative poins
				$m->factions[$fid]['points'] = $update['points'];
			}

			if ($update !== array()) {
				$db->update('mission_results', $update, array('game' => $settings['current game'], 'mission' => $m->mission, 'faction' => $fid));
			}
		}
	}
}

unset($m); // php is special and keeps $m around as a reference, meaning the next time we assign to $m it'll smash the last element of our array

foreach ($deleted as $i) {
	unset($missions[$i]);
}

if (count($missions) == 0) {
	$nomissions = true;
}
?>
<form method="POST" action="?page=admin&section=points">
<input type="submit" name="addmission" value="Add Mission" />
<br />
<table class="admintable">
	<tr>
		<th style="width: 80px">Mission ID</th>
		<th>Description</th>
		<th style="width: 80px">Points</th>
<?php foreach ($factions as $id => $name) { ?>
		<th style="width: 200px"><?= $name ?></th>
<?php } // factions ?>
		<th style="width: 50px">Delete</th>
	</tr>
<?php if ($nomissions) { ?>
	<tr><td colspan="<?= 4 + count($factions) ?>">No missions yet, set them up by clicking "Add Mission" above!</td></tr>
<?php } else { // nomissions ?>
	<?php foreach ($missions as $m) { ?>
		<tr>
			<td data-key="<?= $m->mission ?>"><?= str_pad($m->mission, 4, '0', STR_PAD_LEFT) ?></td>
			<td><div class="actionlinks" data-target="desc" data-type="textarea">[<a class="editlink" href="#">Edit</a>]</div><div class="desc"><?= nl2br(htmlspecialchars($m->description)) ?></div></td>
			<td><span class="points"><?= $m->points ?></span> <span class="actionlinks" data-target="points" data-type="text" data-size="3">[<a class="editlink" href="#">Edit</a>]</span>
		<?php foreach ($factions as $id => $name) { ?>
			<td data-faction="<?= $id ?>">
				Points: <span class="points"><?= $m->factions[$id]['points'] ?></span> <span class="actionlinks" data-target="points" data-type="text" data-size="3">[<a class="editlink" href="#">Edit</a>]</span><br />
				Note: <span class="actionlinks" data-target="note" data-type="text" data-size="20">[<a class="editlink" href="#">Edit</a>]</span><div class="note"><?= htmlspecialchars($m->factions[$id]['note']) ?></div> 
			</td>
		<?php } //factions ?>
			<td><input type="submit" class="deletelink" name="delete<?= $m->mission ?>" value="Delete" /></td>
		</tr>
	<?php } // missions ?>
<?php } // nomissions ?>
</table>
<?php if (!$nomissions) { ?>
<br />
<input type="submit" name="save" value="Save" />
<?php } // !nomissions ?>
</form>
<script type="text/javascript">
$('.editlink').click(function () {
	var actionlinks = $(this).closest('.actionlinks'),
		key = $(this).closest('tr').find('[data-key]').attr('data-key'),
		faction = $(this).closest('td').attr('data-faction');
	var target = $(this).closest('td').find('.' + actionlinks.attr('data-target'));
	var content = target.text(), html;

	actionlinks.css('display', 'none');
	switch (actionlinks.attr('data-type')) {
	case 'textarea':
		html = $('<textarea></textarea>').attr('name', actionlinks.attr('data-target') + key + (faction !== undefined ? 'f' + faction : '')).css('width', '99%').attr('rows', 8).val(content);
		break;
	case 'text':
		html = $('<input type="text" />').attr('name', actionlinks.attr('data-target') + key + (faction !== undefined ? 'f' + faction : '')).attr('size', actionlinks.attr('data-size')).val(content);
		break;
	}
	target.html(html);
});
$('.deletelink').click(function (evt) {
	if (!confirm('Are you sure you want to delete this mission? This action cannot be undone.')) {
		evt.preventDefault();
	}
});
</script>
