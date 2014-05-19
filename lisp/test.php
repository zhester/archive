<?php

$m = $_REQUEST['m'] ? $_REQUEST['m'] : 3;
$c = $_REQUEST['c'] ? $_REQUEST['c'] : 3;

require('misscann.php');

echo '<pre>';
cross($m, $c);
echo '</pre>';

?>