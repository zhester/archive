<?php

$regexp = stripslashes($_REQUEST['regexp']);
$source = stripslashes($_REQUEST['source']);
if(preg_match($regexp, $source, $matches)) {
	foreach($matches as $k => $v) {
		echo "$k>>$v\n";
	}
}
else {
	echo 'No match';
}

?>