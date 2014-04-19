/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `content`
--

DROP TABLE IF EXISTS `content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `content` (
  `page` varchar(32) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`page`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

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
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emailqueue` (
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `to` text NOT NULL,
  `replyto` varchar(80) NOT NULL DEFAULT '',
  `subject` varchar(80) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `factions`
--

DROP TABLE IF EXISTS `factions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `factions` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `flags` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`name`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

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
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feeds` (
  `zombie` int(9) NOT NULL,
  `victim` varchar(12) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `feeds` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`zombie`,`victim`),
  UNIQUE KEY `victim` (`victim`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `game`
--

DROP TABLE IF EXISTS `game`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `guesses`
--

DROP TABLE IF EXISTS `guesses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `guesses` (
  `guess` text NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `uin` int(11) NOT NULL,
  `comment` text,
  `locked` tinyint(1) DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logging`
--

DROP TABLE IF EXISTS `logging`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40000 ALTER TABLE `logging` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oz_pool`
--

DROP TABLE IF EXISTS `oz_pool`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oz_pool` (
  `uin` int(9) NOT NULL,
  `realname` varchar(80) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `additional` text NOT NULL,
  PRIMARY KEY (`uin`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `uin` int(9) NOT NULL,
  `permission` varchar(30) NOT NULL,
  PRIMARY KEY (`uin`,`permission`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
INSERT INTO `permissions` VALUES (1,'admin'),(1,'developer')

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `name` varchar(30) NOT NULL,
  `value` varchar(80) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES ('game status','0'),('change usernames','0'),('starve time','1000'),('feed partners','2'),('inactivity time','8760'),('profile pictures','1'),('printid','1'),('factions','1'),('number ozs','1/100'),('oz hide','24'),('nextid','1'),('board','1'),('email','3'),('oz select','1'),('game paused','0'),('emailall','1'),('late register human','48'),('late register zombie','0'),('email confirmation','0'),('guess','0');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `temp_pw`
--

DROP TABLE IF EXISTS `temp_pw`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `temp_pw` (
  `uin` int(9) NOT NULL,
  `password` varchar(40) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`uin`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=MyISAM AUTO_INCREMENT=112 DEFAULT CHARSET=utf8;
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
(78,7,15,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1),
(94,16,21,1,1,0,1,1,1,1,1,1,1,1,1,1,1,1,0),
(95,4,21,1,1,0,1,1,1,1,1,1,1,1,1,1,1,1,0),
(96,6,21,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
(97,8,21,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
(98,10,21,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
(99,5,21,1,1,0,1,1,1,1,1,1,1,1,1,1,1,1,0),
(100,9,21,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
(101,7,21,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
(102,12,21,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
(103,11,21,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
(104,13,21,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
(105,14,21,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
(106,15,21,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
/*!40000 ALTER TABLE `mybb_forumpermissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mybb_forums`
--

DROP TABLE IF EXISTS `mybb_forums`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mybb_forums` (
  `fid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `linkto` varchar(180) NOT NULL DEFAULT '',
  `type` char(1) NOT NULL DEFAULT '',
  `pid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `parentlist` text NOT NULL,
  `disporder` smallint(5) unsigned NOT NULL DEFAULT '0',
  `active` int(1) NOT NULL DEFAULT '0',
  `open` int(1) NOT NULL DEFAULT '0',
  `threads` int(10) unsigned NOT NULL DEFAULT '0',
  `posts` int(10) unsigned NOT NULL DEFAULT '0',
  `lastpost` int(10) unsigned NOT NULL DEFAULT '0',
  `lastposter` varchar(120) NOT NULL DEFAULT '',
  `lastposteruid` int(10) unsigned NOT NULL DEFAULT '0',
  `lastposttid` int(10) NOT NULL DEFAULT '0',
  `lastpostsubject` varchar(120) NOT NULL DEFAULT '',
  `allowhtml` int(1) NOT NULL DEFAULT '0',
  `allowmycode` int(1) NOT NULL DEFAULT '0',
  `allowsmilies` int(1) NOT NULL DEFAULT '0',
  `allowimgcode` int(1) NOT NULL DEFAULT '0',
  `allowvideocode` int(1) NOT NULL DEFAULT '0',
  `allowpicons` int(1) NOT NULL DEFAULT '0',
  `allowtratings` int(1) NOT NULL DEFAULT '0',
  `status` int(4) NOT NULL DEFAULT '1',
  `usepostcounts` int(1) NOT NULL DEFAULT '0',
  `password` varchar(50) NOT NULL DEFAULT '',
  `showinjump` int(1) NOT NULL DEFAULT '0',
  `modposts` int(1) NOT NULL DEFAULT '0',
  `modthreads` int(1) NOT NULL DEFAULT '0',
  `mod_edit_posts` int(1) NOT NULL DEFAULT '0',
  `modattachments` int(1) NOT NULL DEFAULT '0',
  `style` smallint(5) unsigned NOT NULL DEFAULT '0',
  `overridestyle` int(1) NOT NULL DEFAULT '0',
  `rulestype` smallint(1) NOT NULL DEFAULT '0',
  `rulestitle` varchar(200) NOT NULL DEFAULT '',
  `rules` text NOT NULL,
  `unapprovedposts` int(10) unsigned NOT NULL DEFAULT '0',
  `defaultdatecut` smallint(4) unsigned NOT NULL DEFAULT '0',
  `defaultsortby` varchar(10) NOT NULL DEFAULT '',
  `defaultsortorder` varchar(4) NOT NULL DEFAULT '',
  `unapprovedthreads` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`fid`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mybb_forums`
--

LOCK TABLES `mybb_forums` WRITE;
/*!40000 ALTER TABLE `mybb_forums` DISABLE KEYS */;
INSERT INTO `mybb_forums` VALUES
(5,'General','General game talk, etc. Everyone is able to view and post in this forum.','','f',3,'3,5',1,1,1,237,3258,1390876804,'Walker Sawyer',1354,752,'Mission Videos',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(4,'Off Topic','','','c',0,'4',3,1,1,0,0,0,'',0,0,'',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(3,'HvZ','','','c',0,'3',1,1,1,0,0,0,'',0,0,'',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(6,'The Game','Discussions about the game currently underway. Everyone registered for the current game is able to view and post in this forum.','','f',3,'3,6',3,1,1,106,1072,1386187002,'Ryan Schmidt',1,743,'Wednesday Cure Hunt 12/3/2013',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(7,'The Resistance','Human-related discussion. Only Humans are able to view and post in this forum.','','f',3,'3,7',4,1,1,76,790,1390843198,'Doctor Lunatic',1267,751,'OZ Spotting',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(8,'The Horde','Zombie-related discussion. Only Zombies are able to view and post in this forum.','','f',3,'3,8',5,1,1,60,438,1386461042,'Hayden Davenport',1365,740,'ZOMBIE GROUPME',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(9,'#FACTION-1#','Faction-specific discussion. Only members of your faction are able to view and post in this forum.','','f',3,'3,9',6,1,1,0,0,0,'',0,0,'',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(10,'#FACTION-2#','Faction-specific discussion. Only members of your faction are able to view and post in this forum.','','f',3,'3,10',7,1,1,0,0,0,'',0,0,'',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(11,'#FACTION-3#','Faction-specific discussion. Only members of your faction are able to view and post in this forum.','','f',3,'3,11',8,1,1,0,0,0,'',0,0,'',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(12,'#FACTION-4#','Faction-specific discussion. Only members of your faction are able to view and post in this forum.','','f',3,'3,12',9,1,1,0,0,0,'',0,0,'',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(13,'#FACTION-5#','Faction-specific discussion. Only members of your faction are able to view and post in this forum.','','f',3,'3,13',10,1,1,0,0,0,'',0,0,'',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(14,'Moderators','','','c',0,'14',2,1,1,0,0,0,'',0,0,'',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(15,'Moderators','Mod talk. Only game moderators are able to view and post in this forum.','','f',14,'14,15',1,1,1,42,242,1385998398,'Ryan Schmidt',1,739,'QQ',1,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(16,'Off Topic','Off Topic discussion, you can post anything here but keep it civil.','','f',4,'4,16',1,1,1,96,1048,1389981609,'Walker Sawyer',1354,748,'Memes',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(17,'Website Feedback','For any and all feedback or help with the website','','f',4,'4,17',2,1,1,33,176,1348115162,'Ryan Schmidt',1,662,'Time off on Threads',0,1,1,1,0,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(18,'Squad Forums','','','c',0,'18',4,1,1,0,0,0,'',0,0,'',0,1,1,1,1,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0),
(21,'Mission Feedback and Suggestions','Leave feedback and suggestions for missions here.','','f',3,'3,21',2,1,1,12,31,1391554888,'Walker Sawyer',1354,756,'Possible Point System',0,1,1,1,1,1,1,1,1,'',1,0,0,0,0,0,0,0,'','',0,0,'','',0);
/*!40000 ALTER TABLE `mybb_forums` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mybb_usergroups`
--

DROP TABLE IF EXISTS `mybb_usergroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mybb_usergroups` (
  `gid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `type` smallint(2) NOT NULL DEFAULT '2',
  `title` varchar(120) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `namestyle` varchar(200) NOT NULL DEFAULT '{username}',
  `usertitle` varchar(120) NOT NULL DEFAULT '',
  `stars` smallint(4) NOT NULL DEFAULT '0',
  `starimage` varchar(120) NOT NULL DEFAULT '',
  `image` varchar(120) NOT NULL DEFAULT '',
  `disporder` smallint(6) unsigned NOT NULL,
  `isbannedgroup` int(1) NOT NULL DEFAULT '0',
  `canview` int(1) NOT NULL DEFAULT '0',
  `canviewthreads` int(1) NOT NULL DEFAULT '0',
  `canviewprofiles` int(1) NOT NULL DEFAULT '0',
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
  `canundovotes` int(1) NOT NULL DEFAULT '0',
  `canusepms` int(1) NOT NULL DEFAULT '0',
  `cansendpms` int(1) NOT NULL DEFAULT '0',
  `cantrackpms` int(1) NOT NULL DEFAULT '0',
  `candenypmreceipts` int(1) NOT NULL DEFAULT '0',
  `pmquota` int(3) NOT NULL DEFAULT '0',
  `maxpmrecipients` int(4) NOT NULL DEFAULT '5',
  `cansendemail` int(1) NOT NULL DEFAULT '0',
  `maxemails` int(3) NOT NULL DEFAULT '5',
  `canviewmemberlist` int(1) NOT NULL DEFAULT '0',
  `canviewcalendar` int(1) NOT NULL DEFAULT '0',
  `canaddevents` int(1) NOT NULL DEFAULT '0',
  `canbypasseventmod` int(1) NOT NULL DEFAULT '0',
  `canmoderateevents` int(1) NOT NULL DEFAULT '0',
  `canviewonline` int(1) NOT NULL DEFAULT '0',
  `canviewwolinvis` int(1) NOT NULL DEFAULT '0',
  `canviewonlineips` int(1) NOT NULL DEFAULT '0',
  `cancp` int(1) NOT NULL DEFAULT '0',
  `issupermod` int(1) NOT NULL DEFAULT '0',
  `cansearch` int(1) NOT NULL DEFAULT '0',
  `canusercp` int(1) NOT NULL DEFAULT '0',
  `canuploadavatars` int(1) NOT NULL DEFAULT '0',
  `canratemembers` int(1) NOT NULL DEFAULT '0',
  `canchangename` int(1) NOT NULL DEFAULT '0',
  `showforumteam` int(1) NOT NULL DEFAULT '0',
  `usereputationsystem` int(1) NOT NULL DEFAULT '0',
  `cangivereputations` int(1) NOT NULL DEFAULT '0',
  `reputationpower` bigint(30) NOT NULL DEFAULT '0',
  `maxreputationsday` bigint(30) NOT NULL DEFAULT '0',
  `maxreputationsperuser` bigint(30) NOT NULL DEFAULT '0',
  `maxreputationsperthread` bigint(30) NOT NULL DEFAULT '0',
  `candisplaygroup` int(1) NOT NULL DEFAULT '0',
  `attachquota` bigint(30) NOT NULL DEFAULT '0',
  `cancustomtitle` int(1) NOT NULL DEFAULT '0',
  `canwarnusers` int(1) NOT NULL DEFAULT '0',
  `canreceivewarnings` int(1) NOT NULL DEFAULT '0',
  `maxwarningsday` int(3) NOT NULL DEFAULT '3',
  `canmodcp` int(1) NOT NULL DEFAULT '0',
  `showinbirthdaylist` int(1) NOT NULL DEFAULT '0',
  `canoverridepm` int(1) NOT NULL DEFAULT '0',
  `canusesig` int(1) NOT NULL DEFAULT '0',
  `canusesigxposts` bigint(30) NOT NULL DEFAULT '0',
  `signofollow` int(1) NOT NULL DEFAULT '0',
  `cansendemailoverride` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`gid`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mybb_usergroups`
--

LOCK TABLES `mybb_usergroups` WRITE;
/*!40000 ALTER TABLE `mybb_usergroups` DISABLE KEYS */;
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
(17,2,'mundo','mundo has group when mundo pleases','<span style=\"color:purple\">{username}</span>','Goes where he pleases',0,'images/star.gif','',0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1,1,1,1,100,5,1,4,1,1,1,0,0,1,0,0,0,0,1,1,1,1,0,0,1,1,1,5,5,5,0,5000,0,0,1,0,0,1,0,1,0,0,0),(19,2,'Strong As Bear','','{username}','',0,'images/star.gif','',0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,100,5,0,4,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,1,5,0,5,0,5000,0,0,0,0,0,0,0,0,0,0,0);
/*!40000 ALTER TABLE `mybb_usergroups` ENABLE KEYS */;
UNLOCK TABLES;

TRUNCATE `mybb_posts`;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-04-18 18:06:07
