<?php if(!defined('HVZ')) die(-1); ?>
<?php
//force SSL
if($proto == 'http') {
	$sslurl = str_replace('http://', 'https://', $url);
	header("Location: $sslurl?page=admin");
	exit;
}
?>
<div id="acp">
<table width="99%" cellspacing="0" cellpadding="0">
<tr>
<td id="acp-menu">
<h1>Navigation</h1>
<ul>
<li class="<?= $section == 'game' ? 'selected' : '' ?>"><a href="?page=admin&section=game">Game Flow</a></li>
<li class="<?= $section == 'settings' ? 'selected' : '' ?>"><a href="?page=admin&section=settings">Settings</a></li>
<li class="<?= $section == 'edit' ? 'selected' : '' ?>"><a href="?page=admin&section=edit">Edit Pages</a></li>
<li class="<?= $section == 'players' ? 'selected' : '' ?>"><a href="?page=admin&section=players">Edit Players</a></li>
<li class="<?= $section == 'ozpool' ? 'selected' : '' ?>"><a href="?page=admin&section=ozpool">OZ Pool</a></li>
<li class="<?= $section == 'factions' ? 'selected' : '' ?>"><a href="?page=admin&section=factions">Edit Factions</a></li>
<li class="<?= $section == 'email' ? 'selected' : '' ?>"><a href="?page=admin&section=email">Send Emails</a></li>
<?php if ( $settings['guess'] ) { ?>
<li class="<?= $section == 'guess' ? 'selected' : '' ?>"><a href="?page=admin&section=guess">View Guess Results</a></li>
<?php } //end if guess ?>
<li class="<?= $section == 'logs' ? 'selected' : '' ?>"><a href="?page=admin&section=logs">View Logs</a></li>
</ul>
</td>
<td id="acp-content">
<?php include(dirname(__FILE__) . '/acp/' . $section . '.php'); ?>
</td>
</tr>
</table>
</div>
