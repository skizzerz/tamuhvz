<?php
//register output buffer and session
ob_start();
session_start();
date_default_timezone_set('America/Chicago'); //get the right TZ for Texas
define('HVZ', true);
$settings = array();
$tab = '';
$page = '';
$pagename = '';
$user = null;
$proto = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
//includes
require('includes/killquotes.php');
require('includes/defines.php');
require('includes/RIB64.php');
require('includes/string.php');
require('includes/hash.php');
require('includes/functions.php');
require('includes/db.php');
require('includes/classes.php');
require('settings.php');
initSettings();
//dispatch request
dispatch();
//standard header
if(!isset($_GET['ajax']) && !(isset($_GET['mode']) && $_GET['mode'] == 'xmlhttp') && !isset($_GET['popup']))
	include('templates/header.php');
//actual page
include("templates/$page.php");
//standard footer
if(!isset($_GET['ajax']) && !(isset($_GET['mode']) && $_GET['mode'] == 'xmlhttp') && !isset($_GET['popup']))
	include('templates/footer.php');
//finish output
ob_end_flush();
