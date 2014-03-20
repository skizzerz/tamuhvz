<?php
if ( !$settings['guess'] ) {
	echo '<span class="error">This page is disabled.</span>';
	return;
}
?><form method="POST" action="?page=guess">
Guess: <input name="guess" size="120" value="" /> <input type="submit" name="submit" value="Submit" />
<br /><br />
<?php
if ( isset( $_POST['submit'] ) && $_POST['submit'] == 'Submit' ) {
	$res = $db->query( 'SELECT * FROM guesses WHERE uin=' . $user->uin );
	$good = true;
	while ( $row = $res->fetchRow() ) {
		if ( time() < strtotime( $row->added ) + ( 10 * 60 ) ) {
			$good = false;
			break;
		}
	}
	$guess = mysql_real_escape_string( $_POST['guess'] );
	if ( $good ) {
		$db->query( "INSERT INTO guesses (uin, guess) VALUES ({$user->uin}, '{$guess}')" );
		echo 'Guess submitted. You should receive a reply shortly (it will appear in the "Mod Comment" column).';
	} else {
		echo '<span class="error">You must wait ' . ( strtotime( $row->added ) + ( 10 * 60 ) - time() ) . ' more seconds before you may submit another guess.</span>';
	}
} elseif ( isset( $_POST['submit'] ) && $_POST['submit'] == 'Update' ) {
	foreach ( $_POST as $key => $val ) {
		$kp = explode( '---', $key );
		if ( count( $kp ) == 2 && $kp[0] == 'g' ) {
			$guess = mysql_real_escape_string( $val );
			$added = mysql_real_escape_string( $kp[1] );
			$db->query( "UPDATE guesses SET guess='{$guess}', locked=1, added='{$added}' WHERE uin={$user->uin} AND locked=0 AND added='{$added}'" );
		}
	}
}

$res = $db->query( 'SELECT * FROM guesses WHERE uin=' . $user->uin . ' ORDER BY added');
?>
<table class="prettytable">
<tr><th>Guess</th><th>Mod Comment</th></tr>
<?php
while ( $row = $res->fetchRow() ) {
	$guess = htmlspecialchars( $row->guess );
	$comment = htmlspecialchars( $row->comment );
	if ( $row->locked ) {
		echo "<tr><td>{$guess}</td><td>{$comment}</td></tr>\n";
	} else {
		echo '<tr><td><input name="g---' . $row->added . '" value="' . addslashes( $guess ) . '" size=80 /> <input type="submit" name="submit" value="Update" /></td><td>' . $comment . '</td></tr>' . "\n";
	}
}
?>
</table>
</form>
