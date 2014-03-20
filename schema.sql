--
-- TAMU HvZ Database Schema
--

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- --------------------------------------------------------

--
-- Table structure for table `content`
--

DROP TABLE IF EXISTS `content`;
CREATE TABLE `content` (
  `page` varchar(32) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`page`)
) ENGINE=MyISAM;

--
-- Dumping data for table `content`
--

LOCK TABLES `content` WRITE;
/*!40000 ALTER TABLE `content` DISABLE KEYS */;
INSERT INTO `content` VALUES ('mainlo','Write Something Here'),('mainli','Write Something Here'),('rules','Write Something Here');
/*!40000 ALTER TABLE `content` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emailqueue`
--

DROP TABLE IF EXISTS `emailqueue`;
CREATE TABLE `emailqueue` (
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `to` text NOT NULL,
  `replyto` varchar(80) NOT NULL DEFAULT '',
  `subject` varchar(80) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`time`)
) ENGINE=MyISAM;

--
-- Table structure for table `factions`
--

DROP TABLE IF EXISTS `factions`;
CREATE TABLE `factions` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `flags` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`name`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM;

--
-- Dumping data for table `factions`
--

LOCK TABLES `factions` WRITE;
/*!40000 ALTER TABLE `factions` DISABLE KEYS */;
INSERT INTO `factions` VALUES (-3,'Deceased',2),(-2,'Horde (Original)',2),(-1,'Horde',2),(0,'Resistance',2);
/*!40000 ALTER TABLE `factions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feeds`
--

DROP TABLE IF EXISTS `feeds`;
CREATE TABLE `feeds` (
  `zombie` int(9) NOT NULL,
  `victim` varchar(12) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `feeds` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`zombie`,`victim`),
  UNIQUE KEY `victim` (`victim`)
) ENGINE=MyISAM;

--
-- Table structure for table `game`
--

DROP TABLE IF EXISTS `game`;
CREATE TABLE `game` (
  `uin` int(9) NOT NULL,
  `id` varchar(12) NOT NULL DEFAULT '',
  `kills` int(10) NOT NULL DEFAULT '0',
  `feeds` int(10) NOT NULL DEFAULT '0',
  `turned` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `fed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `starved` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`uin`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM;

--
-- Table structure for table `guesses`
--

DROP TABLE IF EXISTS `guesses`;
CREATE TABLE `guesses` (
  `guess` text NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `uin` int(11) NOT NULL,
  `comment` text,
  `locked` tinyint(1) DEFAULT '1'
) ENGINE=MyISAM;

--
-- Table structure for table `logging`
--

DROP TABLE IF EXISTS `logging`;
CREATE TABLE `logging` (
  `user` int(9) NOT NULL,
  `target` int(11) DEFAULT NULL,
  `targetid` varchar(12) DEFAULT NULL,
  `ip` varchar(40) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` varchar(32) NOT NULL,
  `action` varchar(32) NOT NULL,
  `description` text,
  KEY `user` (`user`),
  KEY `targetid` (`targetid`),
  KEY `target` (`target`)
) ENGINE=MyISAM;

--
-- Table structure for table `oz_pool`
--

DROP TABLE IF EXISTS `oz_pool`;
CREATE TABLE `oz_pool` (
  `uin` int(9) NOT NULL,
  `realname` varchar(80) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `additional` text NOT NULL,
  PRIMARY KEY (`uin`)
) ENGINE=MyISAM;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `uin` int(9) NOT NULL,
  `permission` varchar(30) NOT NULL,
  PRIMARY KEY (`uin`,`permission`)
) ENGINE=MyISAM;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'admin'),(1,'developer'),(1,'godmode');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `name` varchar(30) NOT NULL,
  `value` varchar(80) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES ('game status','1'),('change usernames','0'),('starve time','48'),('feed partners','2'),('inactivity time','8760'),('profile pictures','1'),('printid','1'),('factions','1'),('number ozs','1/100'),('oz hide','24'),('nextid','2395'),('board','1'),('email','3'),('oz select','1'),('game paused','0'),('emailall','1'),('late register human','48'),('late register zombie','0'),('email confirmation','0'),('guess','0');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `temp_pw`
--

DROP TABLE IF EXISTS `temp_pw`;
CREATE TABLE `temp_pw` (
  `uin` int(9) NOT NULL,
  `password` varchar(40) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`uin`)
) ENGINE=MyISAM;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `uin` int(9) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` text NOT NULL,
  `forum_id` int(11) NOT NULL DEFAULT '0',
  `forum_pw` mediumtext NOT NULL,
  `email` varchar(80) NOT NULL,
  `registered` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(60) NOT NULL DEFAULT '',
  `picture` varchar(60) NOT NULL DEFAULT '',
  `feedpref` float NOT NULL DEFAULT '-1',
  `feeds` int(10) NOT NULL DEFAULT '0',
  `kills` int(10) NOT NULL DEFAULT '0',
  `games` int(10) NOT NULL DEFAULT '0',
  `faction` int(10) NOT NULL DEFAULT '0',
  `status` tinyint(2) NOT NULL DEFAULT '0',
  `loggedin` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `options` varchar(1024) NOT NULL DEFAULT '',
  PRIMARY KEY (`uin`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `username` (`username`),
  KEY `registered` (`registered`)
) ENGINE=MyISAM;

--
-- Group/Forum info for MyBB
--

--
-- Dumping data for table `mybb_usergroups`
--

LOCK TABLES `mybb_usergroups` WRITE;
/*!40000 ALTER TABLE `mybb_usergroups` DISABLE KEYS */;
TRUNCATE `mybb_usergroups`;
INSERT INTO `mybb_usergroups` VALUES
(1,1,'Guests','The default group that all visitors are assigned to unless they\'re logged in.','{username}','Unregistered',0,'','',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,5,0,5,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,1,0,0,0,0,0),
(2,1,'Registered','After registration, all users are placed in this group by default.','{username}','',0,'images/star.gif','',0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1,1,1,1,500,5,1,5,1,1,1,0,0,1,0,0,0,0,0,1,1,1,0,0,1,1,1,5,0,0,1,5000,1,0,1,0,0,1,0,1,0,0,0),
(3,1,'Game Moderators','These users can moderate any forum.','<span style=\"color: #CC00CC;\"><strong>{username}</strong></span>','Game Moderator',6,'images/star.gif','',2,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1,1,1,1,1000,5,1,10,1,1,1,1,1,1,1,0,0,1,1,0,1,1,0,1,1,1,1,10,0,0,1,0,0,1,1,3,1,1,0,1,0,0,0),
(4,1,'Administrators','The group all administrators belong to.','<span style=\"color: green;\"><strong>{username}</strong></span>','Webmaster',7,'images/star.gif','',1,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1,1,1,1,0,0,1,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1,1,1,2,0,0,0,1,0,0,1,1,0,1,1,0,1,0,0,0),
(5,1,'Awaiting Activation','Users that have not activated their account by email or manually been activated yet.','{username}','Account not Activated',0,'images/star.gif','',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,20,5,0,5,1,1,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,1,0,0,0,0,0),
(6,1,'Moderators','These users moderate specific forums.','<span style=\"color: #CC00CC;\"><strong>{username}</strong></span>','Moderator',5,'images/star.gif','',3,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1,1,1,1,250,5,1,5,1,1,0,0,0,1,0,0,0,0,1,0,1,1,0,1,1,1,1,10,0,0,1,0,0,1,1,3,1,1,0,1,0,0,0),
(7,1,'Banned','The default user group to which members that are banned are moved to.','<s>{username}</s>','Banned',0,'images/star.gif','',0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,5,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,1,0,0,0,0,0),
(8,2,'#FACTION-1#','','{username}','',0,'images/star.gif','',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,100,5,1,4,1,1,1,0,0,1,0,0,0,0,0,1,1,1,0,0,1,1,1,5,0,0,0,5000,0,0,1,0,0,1,0,1,0,0,0),
(9,2,'#FACTION-2#','','{username}','',0,'images/star.gif','',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,100,5,1,4,1,1,1,0,0,1,0,0,0,0,0,1,1,1,0,0,1,1,1,5,0,0,0,5000,0,0,1,0,0,1,0,1,0,0,0),
(10,2,'#FACTION-3#','','{username}','',0,'images/star.gif','',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,100,5,1,4,1,1,1,0,0,1,0,0,0,0,0,1,1,1,0,0,1,1,1,5,0,0,0,5000,0,0,1,0,0,1,0,1,0,0,0),
(11,2,'#FACTION-4#','','{username}','',0,'images/star.gif','',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,100,5,1,4,1,1,1,0,0,1,0,0,0,0,0,1,1,1,0,0,1,1,1,5,0,0,0,5000,0,0,1,0,0,1,0,1,0,0,0),
(12,2,'#FACTION-5#','','{username}','',0,'images/star.gif','',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,100,5,1,4,1,1,1,0,0,1,0,0,0,0,0,1,1,1,0,0,1,1,1,5,0,0,0,5000,0,0,1,0,0,1,0,1,0,0,0),
(13,2,'Player','Registered for the game in progress','{username}','',0,'images/star.gif','',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,100,5,1,4,1,1,1,0,0,1,0,0,0,0,0,1,1,1,0,0,1,1,1,5,0,0,0,5000,0,0,1,0,0,1,0,1,0,0,0),
(14,2,'Resistance','On the Human team','{username}','',0,'images/star.gif','',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,100,5,1,4,1,1,1,0,0,1,0,0,0,0,0,1,1,1,0,0,1,1,1,5,0,0,0,5000,0,0,1,0,0,1,0,1,0,0,0),
(15,2,'Horde','On the Zombie team','{username}','',0,'images/star.gif','',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,100,5,1,4,1,1,1,0,0,1,0,0,0,0,0,1,1,1,0,0,1,1,1,5,0,0,0,5000,0,0,1,0,0,1,0,1,0,0,0),
(17,2,'mundo','mundo has group when mundo pleases','<span style=\"color:purple\">{username}</span>','Goes where he pleases',0,'images/star.gif','',0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1,1,1,1,100,5,1,4,1,1,1,0,0,1,0,0,0,0,1,1,1,1,0,0,1,1,1,5,5,5,0,5000,0,0,1,0,0,1,0,1,0,0,0);
/*!40000 ALTER TABLE `mybb_usergroups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `mybb_forums`
--

LOCK TABLES `mybb_forums` WRITE;
TRUNCATE `mybb_forums`;
/*!40000 ALTER TABLE `mybb_forums` DISABLE KEYS */;
INSERT INTO `mybb_forums` VALUES
(5,'General','General game talk, etc. Everyone is able to view and post in this forum.','','f',3,'3,5',1,1,1,235,3253,1382201142,'Mysterious Stranger',1267,605,'(Optional) Sqaud Forums',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(4,'Off Topic','','','c',0,'4',3,1,1,0,0,0,'',0,0,'',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),(3,'HvZ','','','c',0,'3',1,1,1,0,0,0,'',0,0,'',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(6,'The Game','Discussions about the game currently underway. Everyone registered for the current game is able to view and post in this forum.','','f',3,'3,6',3,1,1,102,1059,1381933426,'Michael Nootbaar',74,720,'The call to advertise',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(7,'The Resistance','Human-related discussion. Only Humans are able to view and post in this forum.','','f',3,'3,7',4,1,1,72,782,1382404455,'Walker Williams',1288,719,'N.O.O.B Squad',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(8,'The Horde','Zombie-related discussion. Only Zombies are able to view and post in this forum.','','f',3,'3,8',5,1,1,59,431,1360262898,'Games Dean',117,686,'ATTENTION ZOMBIES!',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(9,'#FACTION-1#','Faction-specific discussion. Only members of your faction are able to view and post in this forum.','','f',3,'3,9',6,1,1,0,0,0,'',0,0,'',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(10,'#FACTION-2#','Faction-specific discussion. Only members of your faction are able to view and post in this forum.','','f',3,'3,10',7,1,1,0,0,0,'',0,0,'',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(11,'#FACTION-3#','Faction-specific discussion. Only members of your faction are able to view and post in this forum.','','f',3,'3,11',8,1,1,0,0,0,'',0,0,'',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(12,'#FACTION-4#','Faction-specific discussion. Only members of your faction are able to view and post in this forum.','','f',3,'3,12',9,1,1,0,0,0,'',0,0,'',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(13,'#FACTION-5#','Faction-specific discussion. Only members of your faction are able to view and post in this forum.','','f',3,'3,13',10,1,1,0,0,0,'',0,0,'',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(14,'Moderators','','','c',0,'14',2,1,1,0,0,0,'',0,0,'',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(15,'Moderators','Mod talk. Only game moderators are able to view and post in this forum.','','f',14,'14,15',1,1,1,41,239,1378784438,'Ben Titzer',129,717,'FALL \'13 game 2',1,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(16,'Off Topic','Off Topic discussion, you can post anything here but keep it civil.','','f',4,'4,16',1,1,1,94,1046,1382330220,'CJ Garcia',1352,721,'Raider for sale! Drum + 5 extra mags + darts + SS',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(17,'Website Feedback','For any and all feedback or help with the website','','f',4,'4,17',2,1,1,33,176,1348115162,'Ryan Schmidt',1,662,'Time off on Threads',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(18,'Squad Forums','','','c',0,'18',4,1,1,0,0,0,'',0,0,'',0,1,1,1,1,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(21,'Mission Feedback and Suggestions','Leave feedback and suggestions for missions here.','','f',3,'3,21',2,1,1,9,26,1378404235,'Ryan Schmidt',1,709,'Conflicting Mod Statements on Zombies Carrying Blasters',0,1,1,1,1,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0);
/*!40000 ALTER TABLE `mybb_forums` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mybb_forumpermissions`
--

DROP TABLE IF EXISTS `mybb_forumpermissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mybb_forumpermissions` (
  `pid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fid` int(10) unsigned NOT NULL DEFAULT '0',
  `gid` int(10) unsigned NOT NULL DEFAULT '0',
  `canview` int(1) NOT NULL DEFAULT '0',
  `canviewthreads` int(1) NOT NULL DEFAULT '0',
  `canonlyviewownthreads` int(1) NOT NULL DEFAULT '0',
  `candlattachments` int(1) NOT NULL DEFAULT '0',
  `canpostthreads` int(1) NOT NULL DEFAULT '0',
  `canpostreplys` int(1) NOT NULL DEFAULT '0',
  `canpostattachments` int(1) NOT NULL DEFAULT '0',
  `canratethreads` int(1) NOT NULL DEFAULT '0',
  `caneditposts` int(1) NOT NULL DEFAULT '0',
  `candeleteposts` int(1) NOT NULL DEFAULT '0',
  `candeletethreads` int(1) NOT NULL DEFAULT '0',
  `caneditattachments` int(1) NOT NULL DEFAULT '0',
  `canpostpolls` int(1) NOT NULL DEFAULT '0',
  `canvotepolls` int(1) NOT NULL DEFAULT '0',
  `cansearch` int(1) NOT NULL DEFAULT '0',
  `nopermissions` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=93 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mybb_forumpermissions`
--

LOCK TABLES `mybb_forumpermissions` WRITE;
/*!40000 ALTER TABLE `mybb_forumpermissions` DISABLE KEYS */;
INSERT INTO `mybb_forumpermissions` VALUES
(49,16,2,1,1,0,1,1,1,1,1,1,1,1,1,1,1,1,0),
(48,4,2,1,1,0,1,1,1,1,1,1,1,1,1,1,1,1,0),
(87,6,2,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
(52,8,2,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
(53,10,2,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
(74,5,2,1,1,0,1,1,1,1,1,1,1,1,1,1,1,1,0),
(41,8,15,1,1,0,1,1,1,0,1,1,1,1,1,1,1,1,0),
(43,9,8,1,1,0,1,1,1,0,1,1,1,1,1,1,1,1,0),
(44,10,9,1,1,0,1,1,1,1,1,1,1,1,1,1,1,1,0),
(46,12,11,1,1,0,1,1,1,1,1,1,1,1,1,1,1,1,0),
(45,11,10,1,1,0,1,1,1,1,1,1,1,1,1,1,1,1,0),
(47,13,12,1,1,0,1,1,1,1,1,1,1,1,1,1,1,1,0),
(50,9,2,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
(61,7,2,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
(62,7,14,1,1,0,1,1,1,1,1,1,1,1,1,1,1,1,0),
(88,6,13,1,1,0,1,1,1,1,1,1,1,1,1,1,1,1,0),
(54,12,2,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
(55,11,2,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
(56,13,2,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
(57,14,2,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
(79,15,2,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
(78,7,15,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1);
/*!40000 ALTER TABLE `mybb_forumpermissions` ENABLE KEYS */;
UNLOCK TABLES;

-- posts
TRUNCATE `mybb_posts`;
