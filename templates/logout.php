<?php if(!defined('HVZ')) die(-1); ?>
<?php
	setcookie('hvz', '', time() - 3600, '/');
	//log out of forums too
	setcookie('mybbuser', '', time() - 3600, '/');
	$mysid = isset( $_COOKIE['sid'] ) ? $_COOKIE['sid'] : false;
	setcookie('sid', '', time() - 3600, '/');
	setcookie('masquerade', '', time() - 3600);
	if($mysid) {
		$mysid = mysql_real_escape_string($mysid);
		$db->query("DELETE FROM mybb_sessions WHERE sid='$mysid'");
	}
	@session_destroy();
	@session_unset();
?>
<h1>Logging out...</h1>
<script type="text/javascript">
setTimeout("gotoMain()", 2500);
function gotoMain() {
	var loc = window.location.href.split('?')[0];
	window.location = loc + '?page=main';
}
</script>
<noscript>
<a href="?page=main">Click here</a> to continue.
</noscript>