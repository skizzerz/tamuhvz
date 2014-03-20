<?php if(!defined('HVZ')) die(-1); ?>
<?php header('Content-Type: text/html; charset=UTF-8'); ?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="shortcut icon" href="/favicon.ico" />
	<title><?= $pagename ?> &mdash; Fightin' Texas Aggie Humans vs. Zombies</title>
	<?php if(!isset($noheader) || !$noheader) { ?>
	<link rel="stylesheet" href="styles/main.css" />
	<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
	<script type="text/javascript" src="scripts/jquery.js"></script>
	<?php } ?>
</head>
<body class="<?= $page ?> loggedin-<?= $user->loggedin ?>">
<?php if(!isset($noheader) || !$noheader) { ?>
<div id="globalwrapper">
<div id="mainbox">
<div id="top">
	<div id="logo">
		<img src="images/logo.png" alt="Fightin' Texas Aggie Humans vs. Zombies" />
	</div>
	<div id="userbar"><?php
		if($user->loggedin) {
			$name = $user->getName() ? $user->getName() : $user->getUsername();
			?>Welcome <b><?= $name ?></b>! &bull; <a href="<?= $url ?>?page=profile">Profile</a> &bull; <a href="<?= $url ?>?page=logout">Logout</a><?php
			if($user->getUsername() != $originalUser->getUsername()) {
				$oname = $originalUser->getName() ? $originalUser->getName() : $originalUser->getUsername();
				?><br />(Logged in as <b><?= $oname ?></b> &bull; <a href="<?= $url ?>?page=developer">Stop masquerading</a>)<?php
			}
		} else {
			?><a href="<?= $url ?>?page=login">Login</a> &bull; <a href="<?= $url ?>?page=acctregister">Register for account</a><?php
		}
	?></div>
	<div id="tabs" class="fade1">
		<ul>
		<li class="fade<?= $tab == 'main' ? '2' : '1' ?>"><a href="<?= $url ?>?page=main">Home</a></li>
		<?php if($user->loggedin) { ?>
		<?php if($user->registered) {
			if($user->getFaction() == -1 || $user->getFaction() == -2 || $user->isAllowed('mundo')) { ?>
		<li class="spacer">&nbsp;</li>
		<li class="fade<?= $tab == 'reportkill' ? '2' : '1' ?>"><a href="<?= $url ?>?page=main&tab=reportkill">Report a kill</a></li>
			<?php } //end if zombie check ?>
		<li class="spacer">&nbsp;</li>
		<li class="fade<?= $tab == 'players' ? '2' : '1' ?>"><a href="<?= $url ?>?page=main&tab=players">Players</a></li>
			<?php if ( $settings['guess'] ) { ?>
		<li class="spacer">&nbsp;</li>
		<li class="fade<?= $tab == 'guess' ? '2' : '1' ?>"><a href="<?= $url ?>?page=guess">Guess</a></li>
			<?php } // end if guess is enabled ?>
			<?php if($settings['game status'] >= 2) { ?>
		<li class="spacer">&nbsp;</li>
		<li class="fade<?= $tab == 'suicide' ? '2' : '1' ?>"><a href="<?= $url ?>?page=main&tab=suicide">Leave the game</a></li>
			<?php } //end if registration is closed check ?>
			<?php if($settings['printid']) { ?>
		<li class="spacer">&nbsp;</li>
		<li class="fade<?= $tab == 'printid' ? '2' : '1' ?>"><a href="<?= $url ?>?page=main&tab=printid">Print ID</a></li>
			<?php } //end printid check ?>
		<?php } else { ?>
		<li class="spacer">&nbsp;</li>
		<li class="fade<?= $tab == 'register' ? '2' : '1' ?>"><a href="<?= $url ?>?page=main&tab=register">Register for game</a></li>
		<?php } //end if registered for game check ?>
		<li class="spacer">&nbsp;</li>
		<li class="fade<?= $tab == 'profile' ? '2' : '1' ?>"><a href="<?= $url ?>?page=profile">Profile</a></li>
		<li class="spacer">&nbsp;</li>
		<li class="fade<?= $tab == 'rules' ? '2' : '1' ?>"><a href="<?= $url ?>?page=main&tab=rules">Rules</a></li>
		<?php if($settings['board']) { ?>
		<li class="spacer">&nbsp;</li>
		<li class="fade<?= $tab == 'board' ? '2' : '1' ?>"><a href="<?= $url ?>?page=board">Board</a></li>
		<?php } //end if board is enabled ?>
		<?php if($user->isAllowed('admin')) { ?>
		<li class="spacer">&nbsp;</li>
		<li class="fade<?= $tab == 'admin' ? '3' : '1' ?>"><a href="<?= $url ?>?page=admin">Admin</a></li>
		<?php } //end admin check ?>
		<?php if($originalUser->isAllowed('developer')) { ?>
		<li class="spacer">&nbsp;</li>
		<li class="fade<?= $tab == 'developer' ? '2' : '1' ?>"><a href="<?= $url ?>?page=developer">Developer</a></li>
		<?php } //end developer check ?>
		<?php } else { //else if logged out ?>
		<li class="spacer">&nbsp;</li>
		<li class="fade<?= $tab == 'login' ? '2' : '1' ?>"><a href="<?= $url ?>?page=login">Login</a></li>
		<li class="spacer">&nbsp;</li>
		<li class="fade<?= $tab == 'acctregister' ? '2' : '1' ?>"><a href="<?= $url ?>?page=acctregister">Register for account</a></li>
		<?php } //end if logged in check ?>
		<li class="spacer">&nbsp;</li>
		</ul>
	</div>
</div>
<div id="content">
<?php } ?>
