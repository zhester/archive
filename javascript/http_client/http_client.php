<?php

/**
 * Server-side test script.
 */

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

$sleepfor = 0;

if($_REQUEST['timeout']) {
	$sleepfor = floor($_REQUEST['timeout']/1000);
	sleep($sleepfor);
}

echo 'server response text '.date('H:i:s')
	."\nslept for $sleepfor seconds";

?>