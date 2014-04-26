<?php
if(!defined('HVZ')) die(-1);
//classes for hvz

class User {
	public $uin, $username, $name, $email, $status, $tfeeds, $tkills, $games, $faction, $picture, $options;
	public $feeds, $kills, $turned, $fed, $starved, $id, $feedpref, $permissions = false, $token;
	public $loggedin, $registered, $loggedintime, $forum_id, $db;
	
	public function __construct($uin, $username = false, $email = false, $fromDb = true) {
		$this->uin = $uin;
		//load info from db
		if(!$fromDb) {
			return;
		}
		global $db;
		$this->db = $db;
		$res = $db->select('users', '*', array('uin' => $uin));
		if(!$res->numRows()) {
			//user doesn't exist in db yet, create it
			return self::getDefaultUser();
		}
		$row = $res->fetchRow();
		$this->loggedin = true;
		$this->username = $row->username;
		if($username && $this->username != $username) {
			//not a match, force a relog
			return self::getDefaultUser();
		}
		$this->forum_id = $row->forum_id;
		$this->email = $row->email;
		$this->name = $row->name;
		$this->picture = $row->picture;
		$this->status = $row->status;
		$this->tfeeds = $row->feeds;
		$this->tkills = $row->kills;
		$this->games = $row->games;
		$this->feedpref = $row->feedpref;
		$this->faction = $row->faction;
		$this->registered = $row->registered;
		$this->loggedintime = $row->loggedin;
		$this->parseOptions( $row->options );
		$this->token = $row->token;
		if($this->registered) {
			$res = $db->select('game', '*', array('uin' => $uin));
			$row = $res->fetchRow();
			$this->kills = $row->kills;
			$this->feeds = $row->feeds;
			$this->turned = $row->turned;
			$this->fed = $row->fed;
			$this->starved = $row->starved;
			$this->id = $row->id;
		}
	}
	
	public static function getDefaultUser() {
		$user = new User(0, false, false, false);
		$user->loggedin = false;
		return $user;
	}
	
	public static function createUser($uin, $username, $email) {
		global $db;
		$db->query("INSERT INTO `users` (`uin`,`username`,`email`) VALUES($uin,'$username','$email')");
		return new User($uin, $username, $email);
	}
	
	public function getUin() { return $this->uin; }
	public function getUsername() { return $this->username; }
	public function getEmail() { return $this->email; }
	public function getToken() { return $this->token; }
	public function getName() { return $this->name; }
	public function getStatus() { return $this->status; }
	public function getTotalFeeds() { return $this->tfeeds; }
	public function getTotalKills() { return $this->tkills; }
	public function getGames() { return $this->games; }
	public function getFaction() { return $this->faction; }
	public function getFeeds() { return $this->feeds; }
	public function getKills() { return $this->kills; }
	public function getTurnedTime() { return $this->time($this->turned); }
	public function getFedTime() { return $this->time($this->fed); }
	public function getStarvedTime() { return $this->time($this->starved); }
	public function getLoggedIn() { return $this->time($this->loggedintime); }
	public function getId() { return $this->id; }
	public function getForumId() { return $this->forum_id; }
	public function getFeedpref() { return $this->feedpref; }
	public function getPermissions() {
		if( $this->permissions == false ) {
			$this->loadPermissions();
		}
		return $this->permissions;
	}
	
	public function time($date) {
		$ts = strtotime($date);
		return strftime("%b %d, %Y %I:%M %p", $ts);		
	}
	
	//register for a game, assigning to a random faction if enabled
	//does NOT check if the user is banned or already registered
	public function register($name = null, $oz_pool = array()) {
		global $settings;
		$this->registered = true;
		//see if we should set a faction at random
		if($settings['factions'] == 2) {
			$res = $this->db->query("SELECT COUNT(*) AS total FROM factions");
			$r = $res->fetchRow();
			$num = $r->total;
			$faction = rand(1, $num);
		} else {
			$faction = 0;
		}
		$id = strtoupper($this->makeId());
		$this->id = $id;
		if(!is_null($name)) {
			$s = $this->db->query("UPDATE users SET registered=1,status=0,faction={$faction},games=games+1,name='{$name}' WHERE uin={$this->uin}");
		} else {
			$s = $this->db->query("UPDATE users SET registered=1,status=0,faction={$faction},games=games+1 WHERE uin={$this->uin}");
		}
		if(!$s) {
			throw new Exception('RU' . mysql_errno() . ': ' . mysql_error());
		}
		$s = $this->db->query("INSERT INTO game VALUES({$this->uin},'$id',DEFAULT,DEFAULT,DEFAULT,DEFAULT,DEFAULT)");
		if(!$s) {
			throw new Exception('RG' . mysql_errno() . ': ' . mysql_error());
		}
		if( is_array( $oz_pool ) && count( $oz_pool ) > 0 ) {
			$realname = mysql_real_escape_string( $oz_pool['realname'] );
			$phone = mysql_real_escape_string( $oz_pool['phone'] );
			$additional = mysql_real_escape_string( $oz_pool['additional'] );
			$this->db->query("INSERT INTO oz_pool (uin, realname, phone, additional) VALUES({$this->uin}, '{$realname}', '{$phone}', '{$additional}')");
		}
		if($this->forum_id) {
			$res = $this->db->select('mybb_usergroups', array('gid', 'title'));
			$gs = array();
			while(($row = $res->fetchRow())) {
				$gs[$row->title] = $row->gid;
			}
			$res->freeResult();
			$res = $this->db->select('mybb_users', 'additionalgroups', array('uid' => $this->forum_id));
			$row = $res->fetchRow();
			//todo: faction (if applicable)
			$groups = trim($row->additionalgroups . ',' . $gs['Player'] . ',' . $gs['Resistance'], ',');
			$this->db->update('mybb_users', array('additionalgroups' => $groups), array('uid' => $this->forum_id));
		}
	}
	
	public function makeId() {
		$id = '';
		for($i=0; $i<8; $i++) {
			$id .= dechex(rand(0,15));
		}
		//is this ID already in use?
		$res = $this->db->select('game', 'id', array('id' => $id));
		if($res->numRows()) {
			$res->freeResult();
			return $this->makeId();
		}
		$res = $this->db->select('feeds', 'victim', array('victim' => $id));
		if($res->numRows()) {
			$res->freeResult();
			return $this->makeId();
		}
		return strtoupper($id);
	}
	
	//update loggedin timer
	public function updateLoggedin() {
		$this->db->query("UPDATE users SET loggedin=NOW() WHERE uin={$this->uin}");
	}
	
	//test for admin permissions
	public function isAllowed($permission) {
		if($this->permissions == false) {
			$this->loadPermissions();
		}
		return in_array($permission, $this->permissions);
	}
	
	private function loadPermissions() {
		$res = $this->db->select('permissions', '*', array('uin' => $this->uin));
		if(!$res->numRows()) {
			$this->permissions = array();
			return;
		}
		$this->permissions = array();
		while($row = $res->fetchRow()) {
			$this->permissions[] = $row->permission;
		}
		$this->permissions = array_unique($this->permissions);
	}
	
	public function getPicture($array = true) {
		$dir = dirname(__FILE__) . '/../images/';
		$p1 = $dir . substr($this->picture, 0, 1);
		$p2 = $p1 . '/' . substr($this->picture, 0, 2);
		$picture = $p2 . '/' . $this->picture;
		$dir = str_replace(dirname(__FILE__) . '/../', '', $dir); 
		if(is_dir($p1) && is_dir($p2) && is_file($picture)) {
			$picture = str_replace(dirname(__FILE__) . '/../', '', $picture); 
			if($array) {
				return array($picture, substr($picture, -10, 3), substr($picture, -7, 3));
			} else {
				return $picture;
			}
		} else {
			if($array) {
				return array($dir . 'pixel.png', 1, 1);
			} else {
				return $dir . 'pixel.png';
			}
		}
	}
	
	public function getFactionName() {
		global $db;
		$res = $db->select('factions', '*', array('id' => $this->faction));
		$row = $res->fetchRow();
		return $row->name;
	}
	
	public function updatePicture($file) {
		$this->db->update('users', array('picture' => $file), array('uin' => $this->uin));
		//now see if we should delete the old pic
		$res = $this->db->select('users', '*', array('picture' => $this->picture));
		if(!$res->numRows() && $this->picture) {
			$dir = dirname(__FILE__) . '/../images/';
			$p1 = substr($this->picture, 0, 1);
			$p2 = substr($this->picture, 0, 2);
			unlink("$dir/$p1/$p2/{$this->picture}");
		}
		$this->picture = $file;
	}
	
	public function updateEmail($email) {
		$this->db->update('users', array('email' => $email), array('uin' => $this->uin));
		$this->email = $email;
	}
	
	public function updatePassword($password) {
		$pw = mysql_real_escape_string(Password::crypt($password, $this->uin));
		$this->db->update('users', array('password' => $pw), array('uin' => $this->uin));
	}
	
	public function updateName($name) {
		$this->db->update('users', array('name' => $name), array('uin' => $this->uin));
		$this->db->update('mybb_users', array('username' => $name), array('uid' => $this->getForumId()));
		$this->name = $name;
	}
	
	public function updateFeedpref($newpref) {
		if($newpref < 0) {
			$newpref = -1;
		}
		$this->db->update('users', array('feedpref' => $newpref), array('uin' => $this->uin));
		$this->feedpref = $newpref;
	}
	
	public function checkPass($password) {
		$res = $this->db->select('users', 'password', array('uin' => $this->uin));
		$row = $res->fetchRow();
		$pw = $row->password;
		return Password::compare($pw, $password, $this->uin);
	}
	
	public function kick() {
		$this->db->query("UPDATE users SET registered=0, faction=0, status=1 WHERE uin={$this->uin}");
		$this->db->query("DELETE FROM game WHERE uin={$this->uin}");
		$this->db->query("DELETE FROM oz_pool WHERE uin={$this->uin}");
	}
	
	public function ban() {
		$this->db->query("UPDATE users SET registered=0, faction=0, status=2 WHERE uin={$this->uin}");
		$this->db->query("DELETE FROM game WHERE uin={$this->uin}");
		$this->db->query("DELETE FROM oz_pool WHERE uin={$this->uin}");
	}
	
	public function unban() {
		$this->db->query("UPDATE users SET status=0 WHERE uin={$this->uin}");
	}
	
	public function parseOptions( $opts ) {
		$default = array(
			'profile' => PROFILE_PICTURES | PROFILE_KILLS,
		);
		$this->options = $default;
		if($opts === '') {
			$o = '';
			foreach($default as $name => $val) {
				$o .= $name . ':' . $val . ';';
			}
			$o = rtrim($o, ';');
			$this->db->update('users', array('options' => $o), array('uin' => $this->uin));
			return;
		}
		$parts = explode( ';', $opts );
		foreach($parts as $part) {
			list($name, $val) = explode( ':', $part, 2 );
			$this->options[$name] = $val;
		}
	}
}
