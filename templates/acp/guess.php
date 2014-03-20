<?php
if ( !$settings['guess'] ) {
	echo '<br /><span class="error">This page is disabled</span>';
	return;
}
?>
<h1>View Guess Results</h1>
<form method="POST" action="?page=admin&section=guess">
<table class="admintable">
<tr><th>Username</th><th>Name</th><th>Added</th><th>Guess</th><th>Mod Comment</th><th>Locked</th></tr>
<?php
if ( isset( $_POST['submit'] ) && $_POST['submit'] == 'Submit' ) {
	foreach ( $_POST as $key => $val ) {
		$kp = explode( '---', $key );
		if ( count ( $kp ) == 3 ) {
			if ( $kp[0] == 'c' ) {
				$comment = mysql_real_escape_string( $val );
				$db->query( "UPDATE guesses SET comment='{$comment}', added='{$kp[2]}' WHERE uin={$kp[1]} AND added='{$kp[2]}'" );
			} elseif ( $kp[0] == 'l' ) {
				$locked = $val == '1' ? '1' : '0';
				$db->query( "UPDATE guesses SET locked={$locked}, added='{$kp[2]}' WHERE uin={$kp[1]} AND added='{$kp[2]}'" );	
			}
		}
	}
}

$res = $db->query( 'SELECT * FROM guesses ORDER BY uin, added' );
while ( $row = $res->fetchRow() ) {
	$u = new User( $row->uin );
	$guess = htmlspecialchars( $row->guess );
	echo "<tr><td>{$u->getUsername()}</td><td>{$u->getName()}</td><td>{$u->time($row->added)}</td><td>{$guess}</td><td>" .
		'<input type="text" size="80" name="c---' . $row->uin . '---' . $row->added . '" value="' . $row->comment . '" /></td><td>' .
		'<select name="l---' . $row->uin . '---' . $row->added . '">' .
		'<option value="1" ' . ( $row->locked == '1' ? 'selected' : '' ) . '>Locked</option>' .
		'<option value="0" ' . ( $row->locked == '0' ? 'selected' : '' ) . '>Unlocked</option></select></td></tr>' . "\n";
}
?>
</table>
<br />
<input type="submit" name="submit" value="Submit" />
</form>
