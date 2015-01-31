<?php

if(!defined('HVZ')) die(-1);

//force non-SSL
if($proto == 'https') {
	$sslurl = str_replace('https://', 'http://', $hvzurl);
	header("Location: $sslurl?page=board");
	exit;
}

if(!isset($_GET['ajax']) && !(isset($_GET['mode']) && $_GET['mode'] == 'xmlhttp'))
	echo '<!-- Custom CSS/JS for embedded forum -->
<style type="text/css">
@import url("'.$hvzurl.'mybb/cache/themes/theme1/star_ratings.css");
#board_body .logo { display: none !important; }
.post_author,.post_author_info,.post_content {
  text-align: left;
}
.trow2 form {
	background-color: inherit;
}
.usercp_notepad {
	width: 95%;
}
</style>
<script type="text/javascript">
var counter = 0;
function register_mybb_functions() {
	if(typeof MyBB == "undefined" && counter < 20) {
		setTimeout(register_mybb_functions, 500);
		counter++;
	}
	MyBB.popupWindow = function(url, name, width, height) {
		settings = "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes";

		if(width) {
			settings = settings+",width="+width;
		}

		if(height) {
			settings = settings+",height="+height;
		}
		window.open(url + "&popup=1", name, settings);
	};
}
setTimeout(register_mybb_functions, 500);
</script>
<div id="board_body">';
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'index';

//set up login session if the user doesn't already have one (or tampered with it)
if(!isset($_COOKIE['mybbuser']) || !isset($_COOKIE['vtoken']) || $_COOKIE['vtoken'] != sha1($user->getUin() . '-' . $_COOKIE['mybbuser'])) {
	if($user->getForumId()) {
		//mybb user exists
		$res = $db->select('mybb_users', 'loginkey', array('uid' => $user->getForumId()));
		$row = $res->fetchRow();
		$loginkey = $row->loginkey;
		$_COOKIE['mybbuser'] = $user->getForumId() . '_' . $loginkey;
		setcookie('mybbuser', $_COOKIE['mybbuser'], time() + 3600 * 24 * 30);
		setcookie('vtoken', sha1($user->getUin() . '-' . $_COOKIE['mybbuser']), time() + 3600 * 24 * 30);
		setcookie('sid', '', time() - 3600);
	}
}

//unset variables
$userh = $user;
$dbh = $db;
unset($user);
unset($page);
unset($tab);
unset($pagename);
unset($db);

chdir(dirname(__FILE__) . '/../mybb');
//catch output so we do some content replacements
ob_start();
try {
switch($mode) {
	case 'private':
		// do not show private messages if we are masquerading as another user
		if ($userh->getUsername() != $originalUser->getUsername()) {
			echo <<<EOM
				<style type="text/css">
				@import url("{$hvzurl}mybb/cache/themes/theme2/global.css");
				</style>
				<script type="text/javascript">
				setTimeout(function() { window.location = '{$hvzurl}?page=board'; }, 5000);
				</script>
				<center>
				<table class="tborder" style="width: 50%;">
				<tbody>
				<tr><td class="thead"><div style="font-size: 110%"><strong>Error</strong></div></td></tr>
				<tr><td class="trow1"><span class="smalltext">You may not view private messages while masquerading as another user.
				<br /><br />You will be redirected to the board index shortly. If you do not want to wait, <a href="{$hvzurl}?page=board">click here</a>.</span></td></tr>
				</tbody>
				</table>
				</center>
EOM;
		} else {
			include('private.php');
		}
		break;
	case 'forumdisplay':
		include('forumdisplay.php');
		break;
	case 'showthread':
	case 'threaded': //special case
	case 'linear': //special case
		include('showthread.php');
		break;
	case 'member':
		include('member.php');
		break;
	case 'announcements':
		include('announcements.php');
		break;
	case 'calendar':
		include('calendar.php');
		break;
	case 'modcp':
		include('modcp.php');
		break;
	case 'usercp':
		include('usercp.php');
		break;
	case 'usercp2':
		include('usercp2.php');
		break;
	case 'newthread':
		include('newthread.php');
		break;
	case 'newreply':
		include('newreply.php');
		break;
	case 'ratethread':
		include('ratethread.php');
		break;
	case 'editpost':
		include('editpost.php');
		break;
	case 'report':
		include('report.php');
		break;
	case 'polls':
		include('polls.php');
		break;
	case 'sendthread':
		include('sendthread.php');
		break;
	case 'printthread':
		include('printthread.php');
		break;
	case 'moderation':
		include('moderation.php');
		break;
	case 'search':
		include('search.php');
		break;
	case 'misc':
		include('misc.php');
		break;
	case 'xmlhttp':
		include('xmlhttp.php');
		break;
	case 'reputation':
		include('reputation.php');
		break;
	case 'warnings':
		include('warnings.php');
		break;
	case 'showteam':
		include('showteam.php');
		break;
	case 'stats':
		include('stats.php');
		break;
	case 'online':
		include('online.php');
		break;
	case 'managegroup':
		include('managegroup.php');
		break;
	case 'memberlist':
		include('memberlist.php');
		break;
	case 'logout':
		//show a logout disabled message (todo: stylize)
		echo <<<EOM
<style type="text/css">
@import url("{$hvzurl}mybb/cache/themes/theme2/global.css");
</style>
<script type="text/javascript">
setTimeout(function() { window.location = '{$hvzurl}?page=board'; }, 5000);
</script>
<center>
<table class="tborder" style="width: 50%;">
<tbody>
<tr><td class="thead"><div style="font-size: 110%"><strong>Error</strong></div></td></tr>
<tr><td class="trow1"><span class="smalltext">This function has been disabled. If you wish to log out, <a href="{$hvzurl}?page=logout">click here</a>.
<br /><br />You will be redirected to the board index shortly. If you do not want to wait, <a href="{$hvzurl}?page=board">click here</a>.</span></td></tr>
</tbody>
</table>
</center>
EOM;
		break;
	case 'resetpassword':
		//show a reset password disabled message (todo: stylize)
		echo <<<EOM
<style type="text/css">
@import url("{$hvzurl}mybb/cache/themes/theme2/global.css");
</style>
<script type="text/javascript">
setTimeout(function() { window.location = '{$hvzurl}?page=board'; }, 5000);
</script>
<center>
<table class="tborder" style="width: 50%;">
<tbody>
<tr><td class="thead"><div style="font-size: 110%"><strong>Error</strong></div></td></tr>
<tr><td class="trow1"><span class="smalltext">This function has been disabled. If you wish to change your password, <a href="{$hvzurl}?page=profile">click here</a>.
<br /><br />You will be redirected to the board index shortly. If you do not want to wait, <a href="{$hvzurl}?page=board">click here</a>.</span></td></tr>
</tbody>
</table>
</center>
EOM;
		break;
	case 'login':
		// show a login disabled message
		echo <<<EOM
<style type="text/css">
@import url("{$hvzurl}mybb/cache/themes/theme2/global.css");
</style>
<script type="text/javascript">
setTimeout(function() { window.location = '{$hvzurl}?page=board'; }, 5000);
</script>
<center>
<table class="tborder" style="width: 50%;">
<tbody>
<tr><td class="thead"><div style="font-size: 110%"><strong>Error</strong></div></td></tr>
<tr><td class="trow1"><span class="smalltext">This function has been disabled. If you wish to log in, <a href="{$hvzurl}?page=login">click here</a>.
<br /><br />You will be redirected to the board index shortly. If you do not want to wait, <a href="{$hvzurl}?page=board">click here</a>.</span></td></tr>
</tbody>
</table>
</center>
EOM;
		break;
	case 'lostpw':
		// show a password forgotten message
		echo <<<EOM
<style type="text/css">
@import url("{$hvzurl}mybb/cache/themes/theme2/global.css");
</style>
<script type="text/javascript">
setTimeout(function() { window.location = '{$hvzurl}?page=board'; }, 5000);
</script>
<center>
<table class="tborder" style="width: 50%;">
<tbody>
<tr><td class="thead"><div style="font-size: 110%"><strong>Error</strong></div></td></tr>
<tr><td class="trow1"><span class="smalltext">This function has been disabled. If you wish to reset your password, <a href="{$hvzurl}?page=login&action=resetpass">click here</a>.
<br /><br />You will be redirected to the board index shortly. If you do not want to wait, <a href="{$hvzurl}?page=board">click here</a>.</span></td></tr>
</tbody>
</table>
</center>
EOM;
		break;
	case 'register':
		//how a registration disabled message
		echo <<<EOM
<style type="text/css">
@import url("{$hvzurl}mybb/cache/themes/theme2/global.css");
</style>
<script type="text/javascript">
setTimeout(function() { window.location = '{$hvzurl}?page=board'; }, 5000);
</script>
<center>
<table class="tborder" style="width: 50%;">
<tbody>
<tr><td class="thead"><div style="font-size: 110%"><strong>Error</strong></div></td></tr>
<tr><td class="trow1"><span class="smalltext">This function has been disabled. If you wish to register, <a href="{$hvzurl}?page=acctregister">click here</a>.
<br /><br />You will be redirected to the board index shortly. If you do not want to wait, <a href="{$hvzurl}?page=board">click here</a>.</span></td></tr>
</tbody>
</table>
</center>
EOM;
		break;
	case 'index':
	default:
		include('index.php');
		break;
}
} catch(Exception $e) {
	//do nothing
}
$c = ob_get_contents();
ob_end_clean();

//CSS styling for popup windows
if(isset($_GET['popup'])) {
	$c = str_replace('<body>', '<body id="board_body">', $c);
}

$c = str_replace('/mybb/index.php', '/?page=board', $c);
$c = preg_replace('/page=([0-9]+)/', 'p=$1', $c);
$c = preg_replace('/mybb\/([^\/]*)\.php/', '$1.php', $c);
$c = preg_replace('/&mdash; <a href="'.preg_quote($hvzurl, '/').'member\.php\?action=logout.*?<\/a>/', '', $c);
$c = preg_replace('/<a href="member\.php\?action=logout.*?<\/a> \|/', '', $c);
$gamecache = array();
$c = preg_replace_callback('/<!--#HVZ#GamesPlayed#([0-9]+)#-->/', function($matches) {
	global $dbh, $gamecache;
	if(isset($gamecache[$matches[1]])) {
		$games = $gamecache[$matches[1]];
	} else {
		$res = $dbh->select('users', 'games', array('forum_id' => $matches[1]), 1);
		$row = $res->fetchRow();
		$res->freeResult();
		$gamecache[$matches[1]] = $row->games;
		$games = $row->games;
	}
	return "<br />Games Played: $games";
}, $c);

foreach($factionsh as $stuff) {
	$c = str_replace("#FACTION-{$stuff[1]}#", $stuff[0], $c);
}

echo $c;

if(!isset($_GET['ajax']) && !(isset($_GET['mode']) && $_GET['mode'] == 'xmlhttp'))
	echo '</div>';
