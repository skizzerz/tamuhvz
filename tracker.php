<?php
define( 'HVZ', true );
define( 'NOSETUP', true );
require_once( "settings.php" );
$db = mysql_connect( $dbserver, $dbuser, $dbpass );
mysql_select_db( $dbname, $db );
if( isset( $_SERVER ) && array_key_exists( 'REQUEST_METHOD', $_SERVER ) ) {
	echo "Access denied\n";
	return;
} else {
	//cron
	$r = mysql_query( "SELECT `value` FROM `settings` WHERE `name`='game status'", $db );
	$row = mysql_fetch_object( $r );
	if( $row->value < 4 ) {
		return; //nothing to do since we aren't in game
	}
	$r = mysql_query( "SELECT COUNT(*) AS count FROM `users` WHERE `faction`>=0 AND `registered`=1", $db );
	$row = mysql_fetch_object( $r );
	$res = $row->count;
	$r = mysql_query( "SELECT COUNT(*) AS count FROM `users` WHERE (`faction`=-1 OR `faction`=-2) AND `registered`=1", $db );
	$row = mysql_fetch_object( $r );
	$hrd = $row->count;
	$f = fopen( "track.txt", 'at' );
	$t = date( 'YmdHis' ) . ' ' . str_pad( $res, 4, '0', STR_PAD_LEFT ) . ' ' . str_pad( $hrd, 4, '0', STR_PAD_LEFT ) . "\n";
	fwrite( $f, $t );
	fclose( $f );
}
?>
