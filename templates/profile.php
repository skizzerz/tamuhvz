<?php if(!defined('HVZ')) die(-1); ?>
<?php
//force SSL
if($proto == 'http') {
	$sslurl = str_replace('http://', 'https://', $url);
	header("Location: $sslurl?page=profile");
	exit;
}
?>
<h1>View and Edit Your Profile</h1>
<?php
//check if we have post data
if(isset($_POST['submit'])) {
	$email = $_POST['email'];
	if(isset($_POST['newpass'])) {
		$curpass = isset($_POST['curpass']) ? $_POST['curpass'] : '';
		$newpass = $_POST['newpass'];
		$retpass = $_POST['retpass'];
	} else {
		$curpass = $newpass = $retpass = false;
	}
	if(isset($_POST['fname'])) {
		$name = $_POST['fname'] . ' ' . $_POST['lname'];
		if($name != $user->name) {
			processName($name);
		}
	}
	$delpic = isset($_POST['delpic']);
	$picture = isset($_FILES['picture']) ? ($_FILES['picture']['error'] == UPLOAD_ERR_OK) : false;
	processPicture($picture, $delpic);
	if($email != $user->email) {
		processEmail($email);
	}
	processPassword($curpass, $newpass, $retpass);
	switch($_POST['feedpreftype']) {
		case 'always':
			processFeedpref('-1');
			break;
		case 'never':
			processFeedpref('0');
			break;
		case 'other':
			processFeedpref($_POST['feedpref']);
			break;
	}
}
?>
<form method="post" action="?page=profile" enctype="multipart/form-data">
<table class="prettytable">
<tr><th>Item</th><th>Value</th><th>Description</th></tr>
<tr><td>Username</td><td><?= $user->getUsername() ?></td><td>Your username, used internally <i>(cannot be changed)</i></td></tr>
<tr><td>Password<br /><span class="label">Current:</span><br /><span class="label">New:</span><br /><span class="label">Retype:</span></td><td><br /><input type="password" name="curpass" value="" /><br /><input type="password" name="newpass" value="" /><br /><input type="password" name="retpass" value="" /></td><td>Use this to change your password. You only need to fill these in if you plan on changing your password</td></tr>
<tr><td>Email</td><td><input type="text" name="email" value="<?= $user->getEmail() ?>" /></td><td>Your email, used to contact you about important information</td></tr>
<?php if($settings['game status'] < 3 || !$user->registered) { ?>
<tr><td>Name<br /><span class="label">First:</span><br /><span class="label">Last:</span><br /></td><td><br /><input type="text" name="fname" value="<?php $username = explode(' ',$user->getName()); echo $username[0]; ?>" /><br /><input type="text" name="lname" value="<?= isset($username[1]) ? $username[1] : ''; ?>" /></td><td>Your name, shown on Players listing and ID</td></tr>
<?php } else { ?>
<tr><td>Name</td><td><?= $user->getName() ?></td><td>Your name, shown on Players listing and ID <i>(cannot be changed during gameplay)</i></td></tr>
<?php } //end if game in progress check ?>
<?php if($settings['profile pictures'] == 1) { ?>
<?php if(!$user->isAllowed('nopicture') && $user->getStatus() != 2) { ?>
<tr><td>Picture</td><td><img src="<?= $user->getPicture(false) ?>" alt="" /><br /><input type="file" name="picture" id="picture" /><br /><input type="checkbox" name="delpic" id="delpic" value="1" /><label for="delpic">Delete profile picture</label></td><td>Your profile picture, shown on Players listing<br /><div style="font-size: 80%">Acceptable formats: png, jpg, jpeg, gif (no animated gifs!)<br />Maximum dimensions: 300 pixels high by 300 pixels wide (<a href="http://www.picresize.com/" target="_blank">free online picture resizer</a>)<br />Maximum filesize: 1MB<br />Inappropriate pictures will be removed, and repeated attempts to upload them will be met with a ban</div></td></tr>
<?php } else { ?>
<tr><td>Picture</td><td>&nbsp;</td><td><span class="error">You have been banned from being able to upload pictures</span></td></tr>
<?php } //end if user is banned from uploading pictures ?>
<?php } //end if profile pictures are enabled set ?>
<tr><td>Feed preference</td><td>
<input type="radio" name="feedpreftype" value="always" id="feedpreftype-always" <?= ($user->getFeedpref() == -1) ? 'checked="checked"' : '' ?> /> <label for="feedpreftype-always">Always list me</label><br />
<input type="radio" name="feedpreftype" value="never" id="feedpreftype-never" <?= ($user->getFeedpref() == 0) ? 'checked="checked"' : '' ?> /> <label for="feedpreftype-never">Never list me</label><br />
<input type="radio" name="feedpreftype" value="other" id="feedpreftype-other" <?= ($user->getFeedpref() > 0) ? 'checked="checked"' : '' ?> /> <label for="feedpreftype-other">Only list me when I am this many hours away from starving:</label><br />
<input type="text" name="feedpref" value="<?= ($user->getFeedpref() > 0) ? $user->getFeedpref() : '' ?>" style="margin-left: 25px; width: 50px" />
</td><td>How close you must be to starving as a zombie before you get listed as a potential feed partner on the report a kill page</td></tr>
<tr><td>Games played</td><td><?= $user->getGames() ?></td><td>Number of HvZ games you've played</td></tr>
<tr><td>Total kills</td><td><?= $user->getTotalKills() ?></td><td>Total number of kills you've had as a Zombie in all games you've played</td></tr>
<tr><td>Total feeds</td><td><?= $user->getTotalFeeds() ?></td><td>Total number of times you've fed or been fed as a Zombie in all games you've played</td></tr>
<tr><td>Total ratio</td><td><?= $user->getTotalFeeds() ? sprintf('%01.3f', $user->getTotalKills() / $user->getTotalFeeds()) : 'N/A' ?></td><td>Your kills to feeds ratio over all games you've played. 1.000 is the best</td></tr>
<tr><td>Game status</td><td><?= !$user->getStatus() ? ($user->registered ? 'Registered' : 'Not registered') : ($user->getStatus() == 1 ? 'Removed from game' : '<span class="error">Banned</span>') ?></td><td>Your status for the current game</td></tr>
<?php if($user->registered && !$user->getStatus()) { ?>
<tr><td>ID</td><td><?= $user->getId() ?></td><td>Your ID that proves you're playing, give this to the Zombie that kills you.<br /><i>Note: Your ID <b>will change</b> if you get cured!</i></td></tr>
<tr><td>Affiliation</td><td><?= $user->getFactionName() ?></td><td>The group you are currently in for this game</td></tr>
<?php if($user->getFaction() == -2) { ?>
<tr><td>Original Zombie</td><td>&nbsp;</td><td>You are an Original Zombie for this game</td></tr>
<?php } //end OZ check ?>
<tr><td>Game kills</td><td><?= $user->getKills() ?></td><td>Number of kills you've had as a Zombie this game</td></tr>
<tr><td>Game feeds</td><td><?= $user->getFeeds() ?></td><td>Number of times you've fed or been fed as a Zombie this game</td></tr>
<tr><td>Game ratio</td><td><?= $user->getFeeds() ? sprintf('%01.3f', $user->getKills() / $user->getFeeds()) : 'N/A' ?></td><td>Your kills to feeds ratio for this game. 1.000 is the best</td></tr>
<?php if($user->getFaction() < 0) { ?>
<tr><td>Time turned</td><td><?= $user->getTurnedTime() ?></td><td>When you became a Zombie</td></tr>
<tr><td>Time fed</td><td><?= $user->getFedTime() ?></td><td>Time when you last fed</td></tr>
<?php if($user->getFaction() == -3) { ?>
<tr><td>Time starved</td><td><?= $user->getStarvedTime() ?></td><td>When you deceased from starvation</td></tr>
<?php } //end if deceased check ?>
<?php } //end if zombie check ?>
<?php } //end current game information ?>
<?php if($user->isAllowed('admin')) { ?>
<tr><td>Administrator</td><td>&nbsp;</td><td>You are an Administrator</td></tr>
<?php } //end is admin check ?>
<?php if($user->isAllowed('developer')) { ?>
<tr><td>Developer</td><td>&nbsp;</td><td>You are a Developer</td></tr>
<?php } //end if developer check ?>
</table>
<br />
<input type="submit" name="submit" value="Update profile" />
</form>
<script type="text/javascript">
if(document.addEventListener) {
	document.getElementById('delpic').addEventListener('click', togglePicture, false);
} else if(document.attachEvent) {
	document.getElementById('delpic').attachEvent('onclick', togglePicture);
}
function togglePicture() {
	if(document.getElementById('delpic').checked) {
		document.getElementById('picture').value = '';
		document.getElementById('picture').disabled = true;
	} else {
		document.getElementById('picture').disabled = false;
	}
}
</script>
