<?php
//functions for hvz

if(!defined('HVZ'))
	die(-1);

//main dispatcher, loads the appropriate page based on session data and query string
function dispatch() {
	global $db, $settings, $factionsh, $logout_epoch;
	$action = isset($_GET['page']) ? $_GET['page'] : 'main';
	//initalize the user
	if (isset($_SESSION['uin'])) {
		if (!isset($_SESSION['logout_epoch']) || $_SESSION['logout_epoch'] < $logout_epoch) {
			//force logout
			setcookie('hvz', '', time() - 3600);
			setcookie('mybbuser', '', time() - 3600);
			setcookie('vtoken', '', time() - 3600);
			setcookie('sid', '', time() - 3600);
			setcookie('masquerade', '', time() - 3600);
			session_destroy();
			session_unset();
			$user = User::getDefaultUser();
			$originalUser = $user;
		} else {
			$user = new User($_SESSION['uin'], $_SESSION['username']);
			$originalUser = $user;
			if ($user->isAllowed('developer') && isset($_COOKIE['masquerade'])) {
				$user = new User(rib64_decode($_COOKIE['masquerade']));
			}
		}
	} elseif(isset($_COOKIE['hvz'])) {
		$p = explode('|', rib64_decode($_COOKIE['hvz']));
		if (!isset($p[3]) || $p[3] < $logout_epoch) {
			//force logout
			setcookie('hvz', '', time() - 3600);
			setcookie('mybbuser', '', time() - 3600);
			setcookie('vtoken', '', time() - 3600);
			setcookie('sid', '', time() - 3600);
			setcookie('masquerade', '', time() - 3600);
			session_destroy();
			session_unset();
			$user = User::getDefaultUser();
			$originalUser = $user;
		} else {
			if (time() > $p[0]) {
				//expired
				$user = User::getDefaultUser();
				$originalUser = $user;
			} else {
				//not expired
				$uin = rib64_decode($p[1]);
				$username = rib64_decode($p[2]);
				$token = isset($p[4]) ? rib64_decode($p[4]) : '';
				$validSoFar = true;
				if (!is_numeric($uin) || $uin < 1 || !ctype_xdigit($token)) {
					$validSoFar = false;
				}
				if ($validSoFar) {
					$user = new User($uin, $username);
					$validSoFar = $user->getUin() > 0;
				} else {
					$user = User::getDefaultUser();
				}
				if ($validSoFar && $token != $user->getToken()) {
					$validSoFar = false;
				}
				$originalUser = $user;
				if ($validSoFar) {
					$_SESSION['uin'] = $uin;
					$_SESSION['username'] = $username;
					$_SESSION['logout_epoch'] = $logout_epoch;
					if ($user->isAllowed('developer') && isset($_COOKIE['masquerade'])) {
						$user = new User(rib64_decode($_COOKIE['masquerade']));
					}
				} else {
					// invalid (bogus cookie)
					setcookie('hvz', '', time() - 3600);
					setcookie('mybbuser', '', time() - 3600);
					setcookie('vtoken', '', time() - 3600);
					setcookie('sid', '', time() - 3600);
					setcookie('masquerade', '', time() - 3600);
					session_destroy();
					session_unset();
				}
			}
		}
	} else {
		$user = User::getDefaultUser();
		$originalUser = $user;
	}
	setVar('user', $user);
	setVar('originalUser', $originalUser);
	setVar('tab', 'none'); //default value
	setVar('page', 'invalid'); //default value
	setVar('pagename', 'Page Not Found'); //default value
	if ($user->loggedin && $settings['game paused'] == 0 && $settings['enable starvation']) {
		//update list of who's alive/deceased (log starvation as well)
		$res = $db->query("SELECT users.uin,users.faction FROM users LEFT JOIN game ON game.game={$settings['current game']} AND users.uin=game.uin WHERE (users.faction=-1 OR users.faction=-2) AND TIMEDIFF(TIMESTAMPADD(HOUR,(SELECT value FROM settings WHERE name='starve time'),game.fed),NOW())<0");
		while($row = $res->fetchRow()) {
			writeLog('kill', 'starve', array('old' => $row->faction));
		}
		$db->query("UPDATE users,game SET users.faction=-3,game.starved=NOW() WHERE game.game={$settings['current game']} AND users.uin=game.uin AND (users.faction=-1 OR users.faction=-2) AND TIMEDIFF(TIMESTAMPADD(HOUR,(SELECT value FROM settings WHERE name='starve time'),game.fed),NOW())<0");
	}
	$res = $db->select('factions', true);
	$factionsh = array();
	$i = 1;
	while(($row = $res->fetchRow())) {
		if($row->id < 1 || (intval($row->flags) & 1 == 1)) continue;
		$factionsh[$row->id] = array($row->name, $i);
		$i++;
	}
	switch($action) {
		case 'main':
			//main screen, determine if we are logged in or not
			if($user->loggedin) {
				dispatchMain($user);
			} else {
				setVar('tab', 'main');
				setVar('page', 'mainlo');
				setVar('pagename', 'Home');
			}
			break;
		case 'login':
			setVar('page', 'login');
			setVar('pagename', 'Login');
			setVar('tab', 'login');
			break;
		case 'logout':
			if(!$user->loggedin) {
				setVar('page', 'needlogin');
				setVar('pagename', 'Login Required');
				break;
			}
			if(ini_get("session.use_cookies")) {
				$params = session_get_cookie_params();
				setcookie(session_name(), '', time() - 42000,
					$params["path"], $params["domain"],
					$params["secure"], $params["httponly"]
				);
			}
			session_unset();
			session_destroy();
			setcookie('mybbuser', '', time() - 3600);
			setcookie('vtoken', '', time() - 3600);
			setcookie('sid', '', time() - 3600);
			
			setVar('page', 'logout');
			setVar('pagename', 'Logged Out');
			break;
		case 'acctregister':
			//this is for registering with the site
			//NOT for registering for a game--that's handled down in dispatchMain()
			if($user->loggedin) {
				setVar('page', 'nopermission');
				setVar('pagename', 'Access Denied');
				break;
			}
			setVar('page', 'acctregister');
			setVar('pagename', 'Register for a new account');
			setVar('tab', 'acctregister');
			break;
		case 'profile':
			if(!$user->loggedin) {
				setVar('page', 'needlogin');
				setVar('pagename', 'Login Required');
				break;
			}
			//let user edit their profile (real name, etc.)
			setVar('page', 'profile');
			setVar('pagename', 'Edit Profile');
			setVar('tab', 'profile');
			break;
		case 'guess':
			if(!$user->loggedin) {
				setVar('page', 'needlogin');
				setVar('pagename', 'Login Required');
				break;
			}
			setVar('page', 'guess');
			setVar('pagename', 'Guess');
			setVar('tab', 'guess');
			break;
		case 'board':
			if(!$settings['board']) {
				setVar('page', 'invalid');
				setVar('pagename', 'Page Not Found');
				setVar('tab', 'none');
				break;
			}
			setVar('page', 'board');
			setVar('tab', 'board');
			setVar('pagename', 'Board');
			break;
		case 'admin':
			if(!$user->loggedin) {
				setVar('page', 'needlogin');
				setVar('pagename', 'Login Required');
			} elseif($user->isAllowed('admin')) {
				dispatchAdmin($user);
			} else {
				setVar('page', 'nopermission');
				setVar('pagename', 'Access Denied');
			}
			break;
		case 'developer':
			if(!$originalUser->loggedin) {
				setVar('page', 'needlogin');
				setVar('pagename', 'Login Required');
			} elseif($originalUser->isAllowed('developer')) {
				setVar('tab', 'developer');
				setVar('page', 'developer');
				setVar('pagename', 'Site Features');
			} else {
				setVar('page', 'nopermission');
				setVar('pagename', 'Access Denied');
			}
			break;
	}
	if($user->loggedin) {
		$user->updateLoggedin();
	}
}

//for the main screen
function dispatchMain($user) {
	$tab = isset($_GET['tab']) ? $_GET['tab'] : 'main';
	setVar('tab', $tab);
	switch($tab) {
		case 'main':
			setVar('page', 'mainli');
			setVar('pagename', 'Home');
			break;
		case 'reportkill':
			setVar('page', 'reportkill');
			setVar('pagename', 'Report a Kill');
			break;
		case 'players':
			setVar('page', 'players');
			setVar('pagename', 'Player List');
			break;
		case 'register':
			setVar('page', 'register');
			setVar('pagename', 'Register for Game');
			break;
		case 'rules':
			setVar('page', 'rules');
			setVar('pagename', 'Rules');
			break;
		case 'suicide':
			setVar('page', 'suicide');
			setVar('pagename', 'Leave the Game');
			break;
		case 'printid':
			setVar('page', 'printid');
			setVar('pagename', 'Print ID');
			break;
		case 'leaderboard':
			setVar('page', 'leaderboard');
			setVar('pagename', 'Leaderboard');
			break;
	}
}

//for the admin panel
function dispatchAdmin($user) {
	$section = isset($_GET['section']) ? $_GET['section'] : 'game';
	switch($section) {
		case 'game':
		case 'settings':
		case 'edit':
		case 'factions':
		case 'players':
		case 'email':
		case 'logs':
		case 'ozpool':
		case 'guess':
		case 'points':
			break;
		default:
			setVar('page', 'invalid');
			setVar('pagename', 'Page Not Found');
			setVar('tab', 'none');
			return;
	}
	setVar('tab', 'admin');
	setVar('page', 'admin');
	setVar('pagename', 'Administrator Control Panel');
	setVar('section', $section);
}

//set a global variable for use in templates
function setVar($var, $val) {
	global $$var;
	$$var = $val;
}

//init settings variable
function initSettings() {
	global $db, $settings;
	$res = $db->query("SELECT * FROM settings");
	while($row = $res->fetchRow()) {
		$settings[$row->name] = $row->value;
	}
}

function processPicture($picture, $delpic, User $user = null) {
	global $settings;
	if(is_null($user)) {
		$user = $GLOBALS['user'];
	}
	if($picture && !$delpic) {
		//permissions check
		if($settings['profile pictures'] == 0) {
			echo '<span class="error">Profile pictures are disabled</span><br />';
			return false;
		}
		if($user->isAllowed('nopicture') || $user->getStatus() == 2) {
			echo '<span class="error">You have been banned from being able to upload pictures</span><br />';
			return false;
		}
		//process picture
		$ptmp = $_FILES['picture']['tmp_name'];
		$phash = md5_file($ptmp);
		$p1 = substr($phash, 0, 1);
		$p2 = substr($phash, 0, 2);
		$ptype = $_FILES['picture']['type'];
		$psize = $_FILES['picture']['size'];
		$ext = false;
		if($psize > 1024 * 1024) { //bigger than 1MB
			echo '<span class="error">Could not upload new profile picture: Picture size over 1MB</span><br />';
			return false;
		}
		//determine if the type was valid
		switch($ptype) {
			case 'image/jpeg':
				//is this a valid jpg?
				$valid = imagecreatefromjpeg($ptmp);
				if(!$valid) {
					echo '<span class="error">Could not upload new profile picture: Invalid JPG</span><br />';
					return false;
				}
				$ext = 'jpg';
				break;
			case 'image/png':
				//is this a valid png?
				$valid = imagecreatefrompng($ptmp);
				if(!$valid) {
					echo '<span class="error">Could not upload new profile picture: Invalid PNG</span><br />';
					return false;
				}
				$ext = 'png';
				break;
			case 'image/gif':
				//is this a valid gif?
				$valid = imagecreatefromgif($ptmp);
				if(!$valid) {
					echo '<span class="error">Could not upload new profile picture: Invalid GIF</span><br />';
					return false;
				}
				//make sure gif isn't animated
				if(is_ani($ptmp)) {
					echo '<span class="error">Could not upload new profile picture: GIF is animated</span><br />';
					return false;
				}
				$ext = 'gif';
				break;
			default:
				echo '<span class="error">Picture may only be a png, jpg/jpeg, or gif</span><br />';
				return false;
		}
		if(!$ext) {
			echo '<span class="error">Picture may only be a png, jpg/jpeg, or gif</span><br />';
			return false;
		}
		if(imagesx($valid) > 300 || imagesy($valid) > 300) { //too tall or wide
			echo '<span class="error">Could not upload new profile picture: Picture is over 300 pixels tall or 300 pixels wide</span><br />';
			return false;
		}
		$dir = dirname(__FILE__) . '/../images';
		if(!is_dir("$dir/$p1")) {
			mkdir("$dir/$p1");
		}
		if(!is_dir("$dir/$p1/$p2")) {
			mkdir("$dir/$p1/$p2");
		}
		$sx = sprintf("%03d", imagesx($valid));
		$sy = sprintf("%03d", imagesy($valid));
		move_uploaded_file($ptmp, "$dir/$p1/$p2/$phash$sx$sy.$ext");
		$user->updatePicture("$phash$sx$sy.$ext");
		imagedestroy($valid);
		return true;
	} elseif($delpic) {
		$user->updatePicture('');
		return true;
	}
}

function processEmail($email, User $user = null) {
	global $db;
	if(is_null($user)) {
		$user = $GLOBALS['user'];
	}
	if(preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $email)) {
		$res = $db->query("SELECT * FROM users WHERE email='$email'");
		if($res->numRows()) {
			echo '<span class="error">Specified email address is already in use by another account</span><br />';
			return false;
		} else {
			$user->updateEmail($email);
			return true;
		}
	}
	echo '<span class="error">Invalid email address specified</span><br />';
	return false;
}

function processPassword($curpass, $newpass, $retpass, User $user = null) {
	if(is_null($user)) {
		$user = $GLOBALS['user'];
	}
	if(!$curpass) {
		return false; //no error, we just aren't changing the pass
	}
	if(!$user->checkPass($curpass)) {
		echo '<span class="error">Current password is incorrect</span><br />';
		return false;
	}
	if($newpass == '') {
		echo '<span class="error">Password cannot be blank</span><br />';
		return false;
	}
	if($newpass != $retpass) {
		echo '<span class="error">New passwords don\'t match</span><br />';
		return false;
	}
	$user->updatePassword($newpass);
	return true;
}

function processFeedpref($newpref, User $user = null) {
	if(is_null($user)) {
		$user = $GLOBALS['user'];
	}
	if(!preg_match('/^-?[0-9]+(\.[0-9]+)?$/', $newpref)) {
		echo '<span class="error">Feed preference must be a number</span>';
		return false;
	}
	$user->updateFeedpref($newpref);
	return true;
}

function processName($name, User $user = null) {
	global $db;
	if(is_null($user)) {
		$user = $GLOBALS['user'];
	}
	if(preg_match('/^[A-Z][A-Z0-9]+ [A-Z][A-Z0-9]+$/i', $name)) {
		$res = $db->query("SELECT * FROM users WHERE name='$name'");
		if($res->numRows()) {
			echo '<span class="error">Specified name is already in use by another account</span><br />';
			return false;
		} else {
			$user->updateName($name);
			return true;
		}
	}
	echo '<span class="error">Invalid first and/or last name</span><br />';
	return false;
}

function writeLog($logtype, $logaction, $comment, $target = false, $targetid = false) {
	global $db, $user;
	$uin = $user->getUin();
	if(!$target) {
		$target = 'NULL';
	} else {
		$target = intval($target);
	}
	if(!$targetid || !preg_match('/[ABCDEF0-9]{8}/i', $targetid)) {
		$targetid = 'NULL';
	} else {
		$targetid = "'" . strtoupper($targetid) . "'";
	}
	$ip = $_SERVER['REMOTE_ADDR'];
	$action = "$logtype/$logaction";
	$comment = mysql_real_escape_string(serialize($comment));
	$db->query("INSERT INTO logging (user, target, targetid, ip, time, type, action, description) VALUES($uin, $target, $targetid, '$ip', NOW(), '$logtype', '$action', '$comment')");
}

function is_ani($filename) {
    if(!($fh = @fopen($filename, 'rb')))
        return false;
    $count = 0;
    //an animated gif contains multiple "frames", with each frame having a
    //header made up of:
    // * a static 4-byte sequence (\x00\x21\xF9\x04)
    // * 4 variable bytes
    // * a static 2-byte sequence (\x00\x2C)
   
    // We read through the file til we reach the end of the file, or we've found
    // at least 2 frame headers
    while(!feof($fh) && $count < 2)
        $chunk = fread($fh, 1024 * 100); //read 100kb at a time
        $count += preg_match_all('#\x00\x21\xF9\x04.{4}\x00\x2C#s', $chunk, $matches);
   
    fclose($fh);
    return $count > 1;
}
