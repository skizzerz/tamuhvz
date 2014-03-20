<?php if(!defined('HVZ')) die(-1); ?>
<?php
$res = $db->select('content', true, array('page' => 'mainlo'), 1);
$row = $res->fetchRow();
echo $row->content;
?>
