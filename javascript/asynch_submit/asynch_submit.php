<?php

foreach($_REQUEST as $k => $v) {
	if(!is_array($v)) {
		echo "$k:$v\n";
	}
}

?>