<?php
chdir(__DIR__);
if (!is_readable('../settings.php')) {
	echo "You must configure settings.php first and ensure it is readable by the user running this script.\n";
	exit(1);
}
// to make settings.php play nice
define('HVZ', true);
define('NOSETUP', true);
require '../settings.php';
$cont = file_get_contents('htaccess.install');
file_put_contents('../.htaccess', str_replace('%url%', rtrim($url, '/'), $cont));
echo "Done.\n";
