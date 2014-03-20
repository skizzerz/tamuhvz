<?php if(!defined('HVZ')) die(-1); ?>
<?php
if ( isset( $_GET['remove'] ) ) {
	$uin = intval( $_GET['remove'] );
	if ( $uin > 0 ) {
		$db->query( "DELETE FROM oz_pool WHERE uin={$uin}" );
		writeLog( 'editplayer', 'removeozapp', '', $uin );
	}
}
?>
<h1>OZ Pool</h1>
<p>You may view OZ applications here. If OZ Selection is automatic, it will select randomly from the list below, use the "remove" link to remove applicants that you do not wish to be selected.
If OZ Selection is manual, you will have to create OZs yourself by clicking the "edit" link and changing their affiliation to "Original Zombie."</p>
<?php
$res = $db->query( 'SELECT o.uin,u.name,u.email,u.games,o.realname,o.phone,o.additional from oz_pool o LEFT JOIN users u ON u.uin=o.uin ORDER BY u.name ASC' );
$rows = array();
while ( $row = $res->fetchRow() ) {
	$rows[] = $row;
}
?>
<table class="admintable">
<tr><th>Site Name</th><th>Real Name</th><th>Email Address</th><th>Phone Number</th><th>Games</th><th>Additional Information</th><th>Actions</th></tr>
<?php
if ( $rows == array() ) {
	echo '<tr><td colspan="7">No users are in the OZ pool.</td></tr>';
} else {
	foreach ( $rows as $row ) {
		?>
		<tr>
			<td><?= htmlspecialchars( $row->name ) ?></td>
			<td><?= htmlspecialchars( $row->realname ) ?></td>
			<td><a href="mailto:<?= htmlspecialchars( $row->email ) ?>"><?= htmlspecialchars( $row->email ) ?></a></td>
			<td><?= htmlspecialchars( $row->phone ) ?></td>
			<td><?= $row->games ?></td>
			<td><?= nl2br( htmlspecialchars( $row->additional ) ) ?></td>
			<td><a href="?page=admin&section=players&edit=<?= $row->uin ?>">edit</a> &bull; <a href="#" onclick="confirmRemove('?page=admin&section=ozpool&remove=<?= $row->uin ?>')">remove</a></td>
		</tr>
		<?php
	}
}
?>
</table>
<script type="text/javascript">
function confirmRemove(uri) {
	if(confirm("Removing this user's application will not allow them to submit another OZ application.\n\nAre you sure you want to removing this user's application?")) {
		window.location = window.location.href.split('?')[0] + uri;
	} else {
		return;
	}
}
</script>