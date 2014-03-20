<?php if(!defined('HVZ')) die(-1); ?>
<?php
$res = $db->select('content', true, array('page' => 'rules'), 1);
$row = $res->fetchRow();
echo $row->content;
?>
