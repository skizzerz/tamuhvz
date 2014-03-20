<?php if(!defined('HVZ')) die(-1); ?>
<?php
$res = $db->query('SELECT * FROM content');
while($row = $res->fetchRow()) {
	${$row->page} = $row->content;
}
if(isset($_POST['submit'])) {
	//submit changes
	$mainli_new = mysql_real_escape_string($_POST['mainli']);
	$mainlo_new = mysql_real_escape_string($_POST['mainlo']);
	$rules_new = mysql_real_escape_string($_POST['rules']);
	if($mainlo != $mainlo_new) {
		writeLog('content', 'edit', 'mainlo');
		$db->query("UPDATE content SET content='$mainlo_new' WHERE page='mainlo'");
		$mainlo = $mainlo_new;
	}
	if($mainli != $mainli_new) {
		writeLog('content', 'edit', 'mainli');
		$db->query("UPDATE content SET content='$mainli_new' WHERE page='mainli'");
		$mainli = $mainli_new;
	}
	if($rules != $rules_new) {
		writeLog('content', 'edit', 'rules');
		$db->query("UPDATE content SET content='$rules_new' WHERE page='rules'");
		$rules = $rules_new;
	}
}
?>
<h1>Edit Pages</h1>
Jump to:
<ul>
<li><a href="#Mainli">Home page (logged in)</a></li>
<li><a href="#Mainlo">Home page (logged out)</a></li>
<li><a href="#Rules">Rules page</a></li>
<li><a href="#Submit">Submit changes</a></li>
</ul>
<br />
<form method="post" action="?page=admin&section=edit">
<h2 id="Mainli">Home page (logged in)</h2>
<div class="editor-back">
<textarea name="mainli">
<?= $mainli ?>
</textarea>
</div>
<br />
<h2 id="Mainlo">Home page (logged out)</h2>
<div class="editor-back">
<textarea name="mainlo">
<?= $mainlo ?>
</textarea>
</div>
<br />
<h2 id="Rules">Rules page</h2>
<div class="editor-back">
<textarea name="rules">
<?= $rules ?>
</textarea>
</div>
<br />
<h2 id="Submit">Submit changes</h2>
<input type="submit" name="submit" value="Submit changes" />
</form>
<script type="text/javascript">
	CKEDITOR.replace('mainli');
	CKEDITOR.replace('mainlo');
	CKEDITOR.replace('rules');
</script>
