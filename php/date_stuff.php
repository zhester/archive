<?php
//random code for working with dates in php



//Helps us build a timestamp out of submitted form data with a simple
//  default naming convention.
function get_option_stamp($base, $hoff = 4) {
	if($base['g'] && ($base['a'] || $base['A'])) {
		//assume am = 0 and pm = 1
		$a = $base['a'] ? $base['a'] : $base['A'];
		$hour = $base['g'] + (12 * $a);
	}
	else {
		$hour = $base['H'] ? $base['H'] : $hoff;
	}
	$minute = $base['i'] ? $base['i'] : 0;
	$second = $base['s'] ? $base['s'] : 0;
	$month = $base['n'] ? $base['n'] : date('n');
	$day = $base['j'] ? $base['j'] : date('j');
	$year = $base['Y'] ? $base['Y'] : date('Y');
	return(mktime($hour,$minute,$second,$month,$day,$year));
}

?>