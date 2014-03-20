<?php
/**
 * MyBB 1.6
 * Copyright 2010 MyBB Group, All Rights Reserved
 *
 * Website: http://mybb.com
 * License: http://mybb.com/about/license
 *
 * $Id$
 */

class session
{
	public $sid = 0;
	public $uid = 0;
	public $ipaddress = '';
	public $useragent = '';
	public $is_spider = false;

	/**
	 * Initialize a session
	 */
	function init()
	{
		global $db, $mybb, $cache;

		// Get our visitor's IP.
		$this->ipaddress = get_ip();

		// Find out the user agent.
		$this->useragent = $_SERVER['HTTP_USER_AGENT'];

		// Attempt to find a session id in the cookies.
		if(isset($mybb->cookies['sid']))
		{
			$sid = $db->escape_string($mybb->cookies['sid']);
			// Load the session
			$query = $db->simple_select("sessions", "*", "sid='{$sid}' AND ip='".$db->escape_string($this->ipaddress)."'", array('limit' => 1));
			$session = $db->fetch_array($query);
			if($session['sid'])
			{
				$this->sid = $session['sid'];
			}
		}

		// If we have a valid session id and user id, load that users session.
		if(!empty($mybb->cookies['mybbuser']))
		{
			$logon = explode("_", $mybb->cookies['mybbuser'], 2);
			$this->load_user($logon[0], $logon[1], /*HACK:*/true);
		}

		// HACK: If no user still, try passing the user's UIN and a null password
		global $dbh, $userh, $factionsh;
		if(!isset($mybb->user['uid'])) {
			$this->load_user($userh->getUin(), null, false);
		}
		
		// If no user still, then we have a guest.
		if(!isset($mybb->user['uid']))
		{
			// Detect if this guest is a search engine spider. (bots don't get a cookied session ID so we first see if that's set)
			if(!$this->sid)
			{
				$spiders = $cache->read("spiders");
				if(is_array($spiders))
				{
					foreach($spiders as $spider)
					{
						if(my_strpos(my_strtolower($this->useragent), my_strtolower($spider['useragent'])) !== false)
						{
							$this->load_spider($spider['sid']);
						}
					}
				}
			}

			// Still nothing? JUST A GUEST!
			if(!$this->is_spider)
			{
				$this->load_guest();
			}
		}
		else
		{
			// HACK: also update groups while we're at it
			if(is_object($dbh)) {
				require_once MYBB_ROOT."inc/datahandlers/user.php";
				$userhandler = new UserDataHandler("update");
				$res = $dbh->select('mybb_usergroups', array('gid', 'title'));
				$gs = array();
				while(($row = $res->fetchRow())) {
					$gs[$row->title] = $row->gid;
				}
				$addgroups = '';
				$maingroup = '2';
				//check for mod/admin status
				if($userh->isAllowed('mundo')) {
					$addgroups = $gs['Registered'] . ',' . $gs['Administrators'];
					$maingroup = $gs['mundo'];
				} elseif($userh->isAllowed('ebul')) {
					$addgroups = $gs['Registered'] . ',' . $gs['Game Moderators'];
					$maingroup = $gs['Ebul'];
				} elseif($userh->isAllowed('developer')) {
					$addgroups = $gs['Registered'];
					$maingroup = $gs['Administrators'];
				} elseif($userh->isAllowed('admin')) {
					$addgroups = $gs['Registered'];
					$maingroup = $gs['Game Moderators'];
				}
				if($userh->registered) {
					//yes we do
					$addgroups .= ',' . $gs['Player'];
					switch($userh->faction) {
						case -3:
							//nothing to do here
							break;
						case -2:
						case -1:
							$addgroups .= ',' . $gs['Horde'];
							break;
						case 0:
							$addgroups .= ',' . $gs['Resistance'];
							break;
						default:
							$addgroups .= ',' . $gs['Resistance'];
							$addgroups .= ',' . $gs["#FACTION-{$factionsh[$userh->faction][1]}#"];
							break;
					}
				}
				//get current groups so they remain in whatever squads they are in (first squad is group id 18)
				$squadres = $dbh->select('mybb_users', array('additionalgroups'), array('uid' => $userh->getForumId()));
				$squadrow = $squadres->fetchRow();
				$squads = explode(',', $squadrow->additionalgroups);
				foreach($squads as $squad) {
					if($squad >= 18 && $squad != $gs['Ebul']) {
						$addgroups .= ',' . $squad;
					} elseif($squad == $gs['Squad Leaders']) {
						if($maingroup == $gs['Registered']) {
							$maingroup = $gs['Squad Leaders'];
							$addgroups .= ',' . $gs['Registered'];
						} else {
							$addgroups .= ',' . $gs['Squad Leaders'];
						}
					}
				}
				$addgroups = trim($addgroups, ',');
				$data = array();
				$data['additionalgroups'] = $addgroups;
				$data['usergroup'] = $maingroup;
				$data['displaygroup'] = $maingroup;
				if($data != array()) {
					$data['uid'] = $mybb->user['uid'];
					$userhandler->data = $data;
					$userhandler->validate_user();
					$userhandler->update_user();
				}
			}
		}


		// As a token of our appreciation for getting this far (and they aren't a spider), give the user a cookie
		if($this->sid && (!isset($mybb->cookies['sid']) || $mybb->cookies['sid'] != $this->sid) && $this->is_spider != true)
		{
			my_setcookie("sid", $this->sid, -1, true);
		}
	}

	/**
	 * Load a user via the user credentials.
	 *
	 * @param int The user id.
	 * @param string The user's loginkey.
	 */
	function load_user($uid, $loginkey='', /*HACK:*/$is_correct_uid = false)
	{
		global $mybb, $db, $time, $lang, $mybbgroups, $session, $cache;

		// Read the banned cache
		$bannedcache = $cache->read("banned");

		// If the banned cache doesn't exist, update it and re-read it
		if(!is_array($bannedcache))
		{
			$cache->update_banned();
			$bannedcache = $cache->read("banned");
		}
		
		//HACK: init mybb user from tamuhvz id
		if($is_correct_uid) {
			$uid = intval($uid);
			$query = $db->query("
				SELECT u.*, f.*
				FROM ".TABLE_PREFIX."users u
				LEFT JOIN ".TABLE_PREFIX."userfields f ON (f.ufid=u.uid)
				WHERE u.uid='$uid'
				LIMIT 1
			");
			$mybb->user = $db->fetch_array($query);
		} else {
			//check if the UIN has a forum account tied to it
			global $dbh, $userh, $factionsh;
			$res = $dbh->select('users', true, array('uin' => $uid), 1);
			$row = $res->fetchRow();
			if($row->forum_id != 0) {
				return $this->load_user($row->forum_id, decodeString($row->forum_pw), true);
			} else {
				// Set up user handler.
				require_once MYBB_ROOT."inc/datahandlers/user.php";
				$userhandler = new UserDataHandler("insert");

				// Set the data for the new user.
				$rp = random_str();
				$user = array(
					"username" => $row->name,
					"password" => $rp,
					"password2" => $rp,
					"email" => $row->email,
					"email2" => $row->email,
					"usergroup" => 2,
					"referrer" => '',
					"timezone" => '',
					"language" => '',
					"profile_fields" => '',
					"regip" => $this->ipaddress,
					"longregip" => my_ip2long($this->ipaddress),
					"coppa_user" => 0,
				);
				$user['options'] = array(
					"allownotices" => 1,
					"hideemail" => 1,
					"subscriptionmethod" => 0,
					"receivepms" => 1,
					"pmnotice" => 0,
					"emailpmnotify" => 0,
					"invisible" => 0,
					"dstcorrection" => 0
				);

				$userhandler->set_data($user);
				$userhandler->validate_user();
				$user_info = $userhandler->insert_user();
				$loginkey = $rp;
				$uin = $userh->getUin();
				my_setcookie("mybbuser", $user_info['uid']."_".$user_info['loginkey'], null, true);
				my_setcookie("vtoken", sha1($uin . '-' .$user_info['uid']."_".$user_info['loginkey']), null, true);
				$uid = $user_info['uid'];
				$query = $db->query("
					SELECT u.*, f.*
					FROM ".TABLE_PREFIX."users u
					LEFT JOIN ".TABLE_PREFIX."userfields f ON (f.ufid=u.uid)
					WHERE u.uid='$uid'
					LIMIT 1
				");
				$mybb->user = $db->fetch_array($query);
				$ph = encodeString($rp);
				$dbh->update('users', array('forum_id' => $uid, 'forum_pw' => $ph), array('uin' => $uin));
				
				//See if we need to set any extra usergroups while we're at it
				if(is_object($dbh)) {
					$res = $dbh->select('mybb_usergroups', array('gid', 'title'));
					$gs = array();
					while(($row = $res->fetchRow())) {
						$gs[$row->title] = $row->gid;
					}
					$addgroups = '';
					$maingroup = '2';
					//check for mod/admin status
					if($userh->isAllowed('mundo')) {
						$addgroups = $gs['Registered'] . ',' . $gs['Administrators'];
						$maingroup = $gs['mundo'];
					} elseif($userh->isAllowed('ebul')) {
						$addgroups = $gs['Registered'] . ',' . $gs['Game Moderators'];
						$maingroup = $gs['Ebul'];
					} elseif($userh->isAllowed('developer')) {
						$addgroups = $gs['Registered'];
						$maingroup = $gs['Administrators'];
					} elseif($userh->isAllowed('admin')) {
						$addgroups = $gs['Registered'];
						$maingroup = $gs['Game Moderators'];
					}
					if($userh->registered) {
						//yes we do
						$addgroups .= ',' . $gs['Player'];
						switch($userh->faction) {
							case -3:
								//nothing to do here
								break;
							case -2:
							case -1:
								$addgroups .= ',' . $gs['Horde'];
								break;
							case 0:
								$addgroups .= ',' . $gs['Resistance'];
								break;
							default:
								$addgroups .= ',' . $gs['Resistance'];
								$addgroups .= ',' . $gs["#FACTION-{$factionsh[$userh->faction][1]}#"];
								break;
						}
					}
					//get current groups so they remain in whatever squads they are in (first squad is group id 18)
					$squadres = $dbh->select('mybb_users', array('additionalgroups'), array('uid' => $userh->getForumId()));
					$squadrow = $squadres->fetchRow();
					$squads = explode(',', $squadrow->additionalgroups);
					foreach($squads as $squad) {
						if($squad >= 18 && $squad != $gs['Ebul']) {
							$addgroups .= ',' . $squad;
						} elseif($squad == $gs['Squad Leaders']) {
							if($maingroup == $gs['Registered']) {
								$maingroup = $gs['Squad Leaders'];
								$addgroups .= ',' . $gs['Registered'];
							} else {
								$addgroups .= ',' . $gs['Squad Leaders'];
							}
						}
					}
					$addgroups = trim($addgroups, ',');
					$data = array();
					$data['additionalgroups'] = $addgroups;
					$data['usergroup'] = $maingroup;
					$data['displaygroup'] = $maingroup;
					if($data != array()) {
						$data['uid'] = $mybb->user['uid'];
						$userhandler->data = $data;
						$userhandler->validate_user();
						$userhandler->update_user();
					}
				}
			}
		}

		if(!empty($bannedcache[$uid]))
		{
			$banned_user = $bannedcache[$uid];
			$mybb->user['bandate'] = $banned_user['dateline'];
			$mybb->user['banlifted'] = $banned_user['lifted'];
			$mybb->user['banoldgroup'] = $banned_user['oldgroup'];
			$mybb->user['banolddisplaygroup'] = $banned_user['olddisplaygroup'];
			$mybb->user['banoldadditionalgroups'] = $banned_user['oldadditionalgroups'];
		}

		// Check the password if we're not using a session
		// HACK: check the actual password too
		require_once('functions_user.php');
		if(!validate_password_from_uid($uid, $loginkey)) {
			if(empty($loginkey) || $loginkey != $mybb->user['loginkey'] || !$mybb->user['uid'])
			{
				unset($mybb->user);
				$this->uid = 0;
				return false;
			}
		}
		$this->uid = $mybb->user['uid'];

		// Set the logout key for this user
		// HACK: and make it invalid
		$mybb->user['logoutkey'] = md5($mybb->user['loginkey'] . 'invalid');

		// Sort out the private message count for this user.
		if(($mybb->user['totalpms'] == -1 || $mybb->user['unreadpms'] == -1) && $mybb->settings['enablepms'] != 0) // Forced recount
		{
			$update = 0;
			if($mybb->user['totalpms'] == -1)
			{
				$update += 1;
			}
			if($mybb->user['unreadpms'] == -1)
			{
				$update += 2;
			}

			require_once MYBB_ROOT."inc/functions_user.php";
			$pmcount = update_pm_count('', $update);
			if(is_array($pmcount))
			{
				$mybb->user = array_merge($mybb->user, $pmcount);
			}
		}
		$mybb->user['pms_total'] = $mybb->user['totalpms'];
		$mybb->user['pms_unread'] = $mybb->user['unreadpms'];

		if($mybb->user['lastip'] != $this->ipaddress && array_key_exists('lastip', $mybb->user))
		{
			$lastip_add = ", lastip='".$db->escape_string($this->ipaddress)."', longlastip='".intval(my_ip2long($this->ipaddress))."'";
		}
		else
		{
			$lastip_add = '';
		}

		// If the last visit was over 900 seconds (session time out) ago then update lastvisit.
		$time = TIME_NOW;
		if($time - $mybb->user['lastactive'] > 900)
		{
			$db->shutdown_query("UPDATE ".TABLE_PREFIX."users SET lastvisit='{$mybb->user['lastactive']}', lastactive='$time'{$lastip_add} WHERE uid='{$mybb->user['uid']}'");
			$mybb->user['lastvisit'] = $mybb->user['lastactive'];
			require_once MYBB_ROOT."inc/functions_user.php";
			update_pm_count('', 2);
		}
		else
		{
			$timespent = TIME_NOW - $mybb->user['lastactive'];
			$db->shutdown_query("UPDATE ".TABLE_PREFIX."users SET lastactive='$time', timeonline=timeonline+$timespent{$lastip_add} WHERE uid='{$mybb->user['uid']}'");
		}

		// Sort out the language and forum preferences.
		if($mybb->user['language'] && $lang->language_exists($mybb->user['language']))
		{
			$mybb->settings['bblanguage'] = $mybb->user['language'];
		}
		if($mybb->user['dateformat'] != 0 && $mybb->user['dateformat'] != '')
		{
			global $date_formats;
			if($date_formats[$mybb->user['dateformat']])
			{
				$mybb->settings['dateformat'] = $date_formats[$mybb->user['dateformat']];
			}
		}

		// Choose time format.
		if($mybb->user['timeformat'] != 0 && $mybb->user['timeformat'] != '')
		{
			global $time_formats;
			if($time_formats[$mybb->user['timeformat']])
			{
				$mybb->settings['timeformat'] = $time_formats[$mybb->user['timeformat']];
			}
		}

		// Find out the threads per page preference.
		if($mybb->user['tpp'])
		{
			$mybb->settings['threadsperpage'] = $mybb->user['tpp'];
		}

		// Find out the posts per page preference.
		if($mybb->user['ppp'])
		{
			$mybb->settings['postsperpage'] = $mybb->user['ppp'];
		}

		// Does this user prefer posts in classic mode?
		if($mybb->user['classicpostbit'])
		{
			$mybb->settings['postlayout'] = 'classic';
		}
		else
		{
			$mybb->settings['postlayout'] = 'horizontal';
		}

		// Check if this user is currently banned and if we have to lift it.
		if(!empty($mybb->user['bandate']) && (isset($mybb->user['banlifted']) && !empty($mybb->user['banlifted'])) && $mybb->user['banlifted'] < $time)  // hmmm...bad user... how did you get banned =/
		{
			// must have been good.. bans up :D
			$db->shutdown_query("UPDATE ".TABLE_PREFIX."users SET usergroup='".intval($mybb->user['banoldgroup'])."', additionalgroups='".$mybb->user['oldadditionalgroups']."', displaygroup='".intval($mybb->user['olddisplaygroup'])."' WHERE uid='".$mybb->user['uid']."' LIMIT 1");
			$db->shutdown_query("DELETE FROM ".TABLE_PREFIX."banned WHERE uid='".$mybb->user['uid']."'");
			// we better do this..otherwise they have dodgy permissions
			$mybb->user['usergroup'] = $mybb->user['banoldgroup'];
			$mybb->user['displaygroup'] = $mybb->user['banolddisplaygroup'];
			$mybb->user['additionalgroups'] = $mybb->user['banoldadditionalgroups'];
			$cache->update_banned();

			$mybbgroups = $mybb->user['usergroup'];
			if($mybb->user['additionalgroups'])
			{
				$mybbgroups .= ','.$mybb->user['additionalgroups'];
			}
		}
		else if(!empty($mybb->user['bandate']) && (empty($mybb->user['banlifted'])  || !empty($mybb->user['banlifted']) && $mybb->user['banlifted'] > $time))
        {
            $mybbgroups = $mybb->user['usergroup'];
        }
        else
        {
			// Gather a full permission set for this user and the groups they are in.
			$mybbgroups = $mybb->user['usergroup'];
			if($mybb->user['additionalgroups'])
			{
				$mybbgroups .= ','.$mybb->user['additionalgroups'];
			}
        }

		$mybb->usergroup = usergroup_permissions($mybbgroups);
		if(!$mybb->user['displaygroup'])
		{
			$mybb->user['displaygroup'] = $mybb->user['usergroup'];
		}

		$mydisplaygroup = usergroup_displaygroup($mybb->user['displaygroup']);
		if(is_array($mydisplaygroup))
		{
			$mybb->usergroup = array_merge($mybb->usergroup, $mydisplaygroup);
		}

		if(!$mybb->user['usertitle'])
		{
			$mybb->user['usertitle'] = $mybb->usergroup['usertitle'];
		}

		// Update or create the session.
		if(!defined("NO_ONLINE"))
		{
			if(!empty($this->sid))
			{
				$this->update_session($this->sid, $mybb->user['uid']);
			}
			else
			{
				$this->create_session($mybb->user['uid']);
			}
		}
		return true;
	}

	/**
	 * Load a guest user.
	 *
	 */
	function load_guest()
	{
		global $mybb, $time, $db, $lang;

		// Set up some defaults
		$time = TIME_NOW;
		$mybb->user['usergroup'] = 1;
		$mybb->user['username'] = '';
		$mybb->user['uid'] = 0;
		$mybbgroups = 1;
		$mybb->user['displaygroup'] = 1;

		// Has this user visited before? Lastvisit need updating?
		if(isset($mybb->cookies['mybb']['lastvisit']))
		{
			if(!isset($mybb->cookies['mybb']['lastactive']))
			{
				$mybb->user['lastactive'] = $time;
				$mybb->cookies['mybb']['lastactive'] = $mybb->user['lastactive'];
			}
			else
			{
				$mybb->user['lastactive'] = intval($mybb->cookies['mybb']['lastactive']);
			}
			if($time - $mybb->cookies['mybb']['lastactive'] > 900)
			{
				my_setcookie("mybb[lastvisit]", $mybb->user['lastactive']);
				$mybb->user['lastvisit'] = $mybb->user['lastactive'];
			}
			else
			{
				$mybb->user['lastvisit'] = intval($mybb->cookies['mybb']['lastactive']);
			}
		}

		// No last visit cookie, create one.
		else
		{
			my_setcookie("mybb[lastvisit]", $time);
			$mybb->user['lastvisit'] = $time;
		}

		// Update last active cookie.
		my_setcookie("mybb[lastactive]", $time);

		// Gather a full permission set for this guest
		$mybb->usergroup = usergroup_permissions($mybbgroups);
		$mydisplaygroup = usergroup_displaygroup($mybb->user['displaygroup']);

		$mybb->usergroup = array_merge($mybb->usergroup, $mydisplaygroup);

		// Update the online data.
		if(!defined("NO_ONLINE"))
		{
			if(!empty($this->sid))
			{
				$this->update_session($this->sid);
			}
			else
			{
				$this->create_session();
			}
		}
	}

	/**
	 * Load a search engine spider.
	 *
	 * @param int The ID of the search engine spider
	 */
	function load_spider($spider_id)
	{
		global $mybb, $time, $db, $lang;

		// Fetch the spider preferences from the database
		$query = $db->simple_select("spiders", "*", "sid='{$spider_id}'", array('limit' => 1));
		$spider = $db->fetch_array($query);

		// Set up some defaults
		$time = TIME_NOW;
		$this->is_spider = true;
		if($spider['usergroup'])
		{
			$mybb->user['usergroup'] = $spider['usergroup'];
		}
		else
		{
			$mybb->user['usergroup'] = 1;
		}
		$mybb->user['username'] = '';
		$mybb->user['uid'] = 0;
		$mybb->user['displaygroup'] = $mybb->user['usergroup'];

		// Set spider language
		if($spider['language'] && $lang->language_exists($spider['language']))
		{
			$mybb->settings['bblanguage'] = $spider['language'];
		}

		// Set spider theme
		if($spider['theme'])
		{
			$mybb->user['style'] = $spider['theme'];
		}

		// Gather a full permission set for this spider.
		$mybb->usergroup = usergroup_permissions($mybb->user['usergroup']);
		$mydisplaygroup = usergroup_displaygroup($mybb->user['displaygroup']);
		$mybb->usergroup = array_merge($mybb->usergroup, $mydisplaygroup);

		// Update spider last minute (only do so on two minute intervals - decrease load for quick spiders)
		if($spider['lastvisit'] < TIME_NOW-120)
		{
			$updated_spider = array(
				"lastvisit" => TIME_NOW
			);
			$db->update_query("spiders", $updated_spider, "sid='{$spider_id}'", 1);
		}

		// Update the online data.
		if(!defined("NO_ONLINE"))
		{
			$this->sid = "bot=".$spider_id;
			$this->create_session();
		}

	}

	/**
	 * Update a user session.
	 *
	 * @param int The session id.
	 * @param int The user id.
	 */
	function update_session($sid, $uid='')
	{
		global $db;

		// Find out what the special locations are.
		$speciallocs = $this->get_special_locations();
		if($uid)
		{
			$onlinedata['uid'] = $uid;
		}
		else
		{
			$onlinedata['uid'] = 0;
		}
		$onlinedata['time'] = TIME_NOW;
		$onlinedata['location'] = $db->escape_string(get_current_location());
		$useragent = $this->useragent;
		if(my_strlen($useragent) > 100)
		{
			$useragent = my_substr($useragent, 0, 100);
		}
		$onlinedata['useragent'] = $db->escape_string($useragent);
		$onlinedata['location1'] = intval($speciallocs['1']);
		$onlinedata['location2'] = intval($speciallocs['2']);
		$onlinedata['nopermission'] = 0;
		$sid = $db->escape_string($sid);

		$db->update_query("sessions", $onlinedata, "sid='{$sid}'", 1);
	}

	/**
	 * Create a new session.
	 *
	 * @param int The user id to bind the session to.
	 */
	function create_session($uid=0)
	{
		global $db;
		$speciallocs = $this->get_special_locations();

		// If there is a proper uid, delete by uid.
		if($uid > 0)
		{
			$db->delete_query("sessions", "uid='{$uid}'");
			$onlinedata['uid'] = $uid;
		}
		// Is a spider - delete all other spider references
		else if($this->is_spider == true)
		{
			$db->delete_query("sessions", "sid='{$this->sid}'");
		}
		// Else delete by ip.
		else
		{
			$db->delete_query("sessions", "ip='".$db->escape_string($this->ipaddress)."'");
			$onlinedata['uid'] = 0;
		}

		// If the user is a search enginge spider, ...
		if($this->is_spider == true)
		{
			$onlinedata['sid'] = $this->sid;
		}
		else
		{
			$onlinedata['sid'] = md5(uniqid(microtime(true)));
		}
		$onlinedata['time'] = TIME_NOW;
		$onlinedata['ip'] = $db->escape_string($this->ipaddress);
		$onlinedata['location'] = $db->escape_string(get_current_location());
		$useragent = $this->useragent;
		if(my_strlen($useragent) > 100)
		{
			$useragent = my_substr($useragent, 0, 100);
		}
		$onlinedata['useragent'] = $db->escape_string($useragent);
		$onlinedata['location1'] = intval($speciallocs['1']);
		$onlinedata['location2'] = intval($speciallocs['2']);
		$onlinedata['nopermission'] = 0;
		$db->replace_query("sessions", $onlinedata, "sid", false);
		$this->sid = $onlinedata['sid'];
		$this->uid = $onlinedata['uid'];
	}

	/**
	 * Find out the special locations.
	 *
	 * @return array Special locations array.
	 */
	function get_special_locations()
	{
		global $mybb;
		$array = array('1' => '', '2' => '');
		if(preg_match("#forumdisplay.php#", $_SERVER['PHP_SELF']) && intval($mybb->input['fid']) > 0)
		{
			$array[1] = intval($mybb->input['fid']);
			$array[2] = '';
		}
		elseif(preg_match("#showthread.php#", $_SERVER['PHP_SELF']))
		{
			global $db;

			if($mybb->input['tid'] && intval($mybb->input['tid']) > 0)
			{
				$array[2] = intval($mybb->input['tid']);
			}
			elseif($mybb->input['pid'] && intval($mybb->input['pid']) > 0)
			{
				$array[2] = intval($mybb->input['pid']);
			}

			// If there is no tid but a pid, trick the system into thinking there was a tid anyway.
			if(!empty($mybb->input['pid']) && !isset($mybb->input['tid']))
			{
				$options = array(
					"limit" => 1
				);
				$query = $db->simple_select("posts", "tid", "pid=".$mybb->input['pid'], $options);
				$post = $db->fetch_array($query);
				$mybb->input['tid'] = $post['tid'];
			}

			$thread = get_thread(intval($mybb->input['tid']));
			$array[1] = $thread['fid'];
		}
		return $array;
	}
}
?>