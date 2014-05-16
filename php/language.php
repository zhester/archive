<?php

/**
 * Builds a human-friendly string describing a file size.
 *
 * @author Zac Hester <zac@planetzac.net>
 * @date 2006-03-03
 *
 * @param size The size in bytes
 * @param verbose Whether or not to print verbose descriptions
 * @return A string describing the size
 */
function get_size($nbytes, $verbose = false) {
	if($verbose) {
		$units = array('bytes', 'kilobytes', 'megabytes',
			'gigabytes', 'terabytes');
	}
	else {
		$units = array('B', 'kB', 'MB', 'GB', 'TB');
	}
	$size = $nbytes;
	$index = 0;
	while($size >= 1024) {
		$size /= 1024;
		++$index;
	}
	return(sprintf('%01.2f %s', $size, $units[$index]));
}


/**
 * Turn the last word of the string into its plural form.
 *
 * @author Zac Hester <zac@planetzac.net>
 * @date 2006-03-31
 *
 * @param str The string that (hopefully) ends in a noun
 * @param number Nouns in question (1 returns original string) 
 * @return The plural form of the passed string.
 */
function get_plural($str, $number = 2) {
	if($number == 1) { return($str); }
	$str = trim($str);

	//Check for acronyms or numbers.
	if(preg_match('/^[A-Z0-9.]+$/', $str)) {
		return($str.'s');
	}

	//Build a reference table with the rules of pluralization.
	$rules = array(
		array(
			'new' => 's', 'remove' => 0,
			'endswith' => array('ay','ey','iy','oy','uy')
		),
		array(
			'new' => 'ies', 'remove' => 1,
			'endswith' => array('y')
		),
		array(
			'new' => 'es', 'remove' => 2,
			'endswith' => array('is')
		),
		array(
			'new' => 'i', 'remove' => 2,
			'endswith' => array('octopus','cactus')
		),
		array(
			'new' => 'es', 'remove' => 0,
			'endswith' => array('s','z','x','ch','sh')
		),
		array(
			'new' => 'es', 'remove' => 0,
			'endswith' => array('echo','embargo','hero','potato',
    			'tomato','torpedo','veto')
    	),
		array(
			'new' => 's', 'remove' => 0,
			'endswith' => array('o')
    	),
    	array(
    		'new' => 'ves', 'remove' => 2,
    		'endswith' => array('fe')
    	),
    	array(
    		'new' => 'ves', 'remove' => 1,
    		'endswith' => array('self')
    	),
    	array(
    		'new' => 'en', 'remove' => 2,
    		'endswith' => array('man')
    	),
    	array(
    		'new' => '', 'remove' => 0,
    		'endswith' => array('deer','fish')
    	)
    	//mouse/mice, goose/geese
    	//implement a 'wordis' set
	);

	//Scan reference table to match a given rule criterion.
	foreach($rules as $rule) {
		foreach($rule['endswith'] as $ending) {
			if(preg_match('/'.$ending.'$/i', $str)) {
				if($rule['remove']) {
					return(substr($str,0,-$rule['remove']).$rule['new']);
				}
				else {
					return($str.$rule['new']);
				}
			}
		}
	}

	//At this point, we default to a sensible plural.
	return($str.'s');
}


/**
 * Transform a phrase into proper title capitalization.
 *
 * @author Zac Hester <zac@planetzac.net>
 * @date 2006-03-31
 *
 * @param str The phrase to set as a title
 * @return The properly capitalized title
 */
function get_title_case($str) {
	$prepositions = array('about','behind','from','on','toward','above',
		'below','in','on','top','of','under','across','beneath','in',
		'front','of','onto','underneath','after','beside','inside','out',
		'of','until','against','between','instead','of','outside','up',
		'along','by','into','over','upon','among','down','like','past',
		'with','around','during','near','since','within','at','except',
		'of','through','without','before','for','off','to');
	$articles = array('a','an','the','some','any');
	$conjunctions = array('and','or','but');
	$punctuation = array(' ','-',':');

////////////////////////////////////////////////////////
	return($str);
}



/**
 * Builds a human-friendly string for how long ago something happened.
 *
 * Future plans:
 *  - Provide a date()-like formatting string.
 *  - Are there _significant_ problems with DST?
 *
 * Table of calculated values:
 *   Days in a year 365.25 = 31+28.25+31+30+31+30+31+31+30+31+30+31
 *   Days in a month 30.44 = 365.25 / 12
 *   An hour      3600        = 60 * 60
 *   A day        86400       = 60 * 60 * 24
 *   A week       604800      = 60 * 60 * 24 * 7
 *   A month      2630016     = 60 * 60 * 24 * 30.44
 *   A year       31557600    = 60 * 60 * 24 * 365.25
 *   Max seconds  4294967295  = 2 ^ 32 - 1
 *
 * @author Zac Hester <zac@zacharyhester.com>
 * @date 2005-10-06
 *
 * @param stamp A unix timestamp of the time in the past
 * @param allow_future Set to True to indicate that you expect a "time-until"
 * @return A string indicating how long ago something happened
 */
function get_timeago($stamp, $allow_future = false) {

	//Number of seconds between now and then.
	$diff = mktime() - $stamp;

	//A very strange case we should check for.
	if($diff == 0) {

		//This should work in a sentence in place of the real value.
		return('no amount of time');
	}

	//This shouldn't happen unless a date in the future is passed.
	else if($diff < 0) {

		//We can just calculate it in reverse, if they want it.
		if($allow_future) { $diff *= -1; }

		//We were expecting a time in the past.
		else { return('no amount of time'); }
	}

	//Seconds range.
	if($diff < 60) {
		$interval = $diff;
		$unit = 'second';
	}

	//Minutes range.
	else if($diff < 3600) {
		$interval = floor($diff/60);
		$unit = 'minute';
	}

	//Hours range.
	else if($diff < 86400) {
		$interval = floor($diff/3600);
		$unit = 'hour';
	}

	//Days range.
	else if($diff < 604800) {
		$interval = floor($diff/86400);
		$unit = 'day';
	}

	//Weeks range.
	else if($diff < 2630016) {
		$interval = floor($diff/604800);
		$unit = 'week';
	}

	//Months range.
	else if($diff < 31557600) {
		$interval = floor($diff/2630016);
		$unit = 'month';
	}

	//Years range.
	else if($diff < 4294967295) {
		$interval = floor($diff/31557600);
		$unit = 'year';
	}

	//This happens if we want something more than 137 years ago.
	else {
		return('a very long time');
	}

	//Send back the constructed string.
	return($interval.' '.$unit.($interval==1?'':'s'));
}


/**
 * Number-to-Text Converter
 *
 * A seldom-used function to convert actual numbers (like the results of a
 * mathematical operation) to a string of English words that describes the
 * number.
 * Future Plans
 * - Support fractional parts of numbers.
 *
 * @author Zac Hester
 * @date 2004-09-22
 *
 * @param num The integer number to represent in English
 * @param ordinal Set to retrieve an ordinal (first, second, etc) string
 * @return A string describing the number in English
 */
function num2text($num, $ordinal = false) {

	//Number name lookup tables.
	$triplets = array('thousand', 'million', 'billion', 'trillion',
		'quadrillion', 'quintillion', 'sextillion', 'septillion',
		'octillion', 'nonillion', 'decillion', 'undecillion', 'duodecillion',
		'tredecillion', 'quattuordecillion', 'quindecillion', 'sexdecillion',
		'septendecillion', 'octodecillion', 'novemdecillion', 'vigintillion');

	//Drop extraneous stuff.
	$number = str_replace(',', '', trim($num));
	$number = str_replace(' ', '', $number);

	//Ensure this number isn't in scientific notation.
	if(strlen($number) < 12) {
		$number = floatval($number);
	}
	
	//Break off any fractional part.
	list($number, $fraction) = explode('.', $number);

	//Check for special case.
	if($number == '0' && (!$fraction)) {
		if($ordinal) { return('zeroth'); }
		return('zero');
	}

	//Text buffer.
	$buffer = '';
		
	//Check for a negative prefix.
	if(substr($number, 0, 1) == '-') {
		$buffer .= 'negative';
		$number = substr($number, 1);
	}

	//Count triplets.
	$trips = ceil(strlen($number) / 3);
	
	//Check for zero-fill padding.
	if((strlen($number) % 3) || ($trips < 3)) {
		$number = str_pad($number, (3*$trips), '0', STR_PAD_LEFT);
	}
	
	//Loop through triplets.
	for($i = 0, $j = $trips; $i < $trips; $i++, $j--) {

		//Extract current triplet.
		$ctrip = substr($number, (3*$i), 3);

		//Find triplet's name.
		if($j > 0) {
			$trip_name = $triplets[($j-2)];
		}
		else { $trip_name = ''; }

		//Get the expression for this triplet's "hundred"
		$hun_name = n2t_get_hundred_phrase($ctrip);

		//Check for previous triplet.
		if(strlen($buffer) && $hun_name) {
			$buffer .= ', '.$hun_name;
			if($trip_name) {
				$buffer .= ' '.$trip_name;
			}
		}

		//Make sure there is a value for this triplet.
		else if($hun_name) {
			$buffer .= $hun_name;
			if($trip_name) {
				$buffer .= ' '.$trip_name;
			}
		}
	}

	//Check for ordinal request.
	if($ordinal) {
		return(n2t_get_ordinal_version($buffer));
	}
	
	//Send back the number as text.
	return($buffer);
}

/**
 * Determines the expression used to describe any three digits.
 */
function n2t_get_hundred_phrase($triplet) {

	//Number name lookup tables.
	$ones = array('zero', 'one', 'two', 'three', 'four', 'five', 'six',
		'seven', 'eight', 'nine');
	$teens = array('ten', 'eleven', 'twelve', 'thirteen', 'fourteen',
		'fifteen', 'sixteen', 'seventeen', 'eightteen', 'nineteen');
	$tens = array('twenty', 'thirty', 'forty', 'fifty', 'sixty',
		'seventy', 'eighty', 'ninety');

	//Text buffer.
	$buffer = '';

	//Extract each digit.
	$digit[0] = substr($triplet, 0, 1);
	$digit[1] = substr($triplet, 1, 1);
	$digit[2] = substr($triplet, 2, 1);
	$tensy = substr($triplet, 1, 2);

	//Decide what to name hundreds digit.
	if($digit[0] != '0') {
		$buffer .= $ones[$digit[0]].' hundred';
	}

	//Check for two-part numbers.
	if(strlen($buffer) && ($tensy > 0)) {
		$buffer .= ' and ';
	}

	//Ignore zeros.
	if($tensy == 0) {
		return($buffer);
	}
	//Check for one's-place stuff.
	else if($tensy < 10) {
		$buffer .= $ones[$digit[2]];
	}
	//Check for "teens"
	else if($tensy < 20) {
		$buffer .= $teens[$digit[2]];
	}
	//Otheriwise, it's a normal ten's number.
	else {

		//Throw on the appropriate ten.
		$buffer .= $tens[($digit[1]-2)];

		//Check out our one's place again.
		if($digit[2] != '0') {
			$buffer .= '-'.$ones[$digit[2]];
		}
	}

	//Send back the expression.
	return($buffer);
}

/**
 * Modify existing number phrase into ordinal version.
 */
function n2t_get_ordinal_version($phrase) {

	//Scan for final word.
	if(preg_match('/(one|two|three|five|twelve)$/', $phrase, $match)) {

		//Capture type of ending.
		$final = $match[1];

		//Check for "odd" endings.
		if($final == 'one') {
			$phrase = preg_replace('/(one)$/', 'first', $phrase);
		}
		else if($final == 'two') {
			$phrase = preg_replace('/(two)$/', 'second', $phrase);
		}
		else if($final == 'three') {
			$phrase = preg_replace('/(three)$/', 'third', $phrase);
		}
		else if($final == 'five') {
			$phrase = preg_replace('/(five)$/', 'fifth', $phrase);
		}
		else if($final == 'twelve') {
			$phrase = preg_replace('/(twelve)$/', 'twelfth', $phrase);
		}
	}

	//Scan for "Y" endings.
	else if(substr($phrase, -1) == 'y') {
		$phrase = substr($phrase, 0, -1).'ieth';
	}

	//Must be a normal ending (this one's tough).
	else {
		$phrase .= 'th';
	}

	//Send back the modified phrase.
	return($phrase);
}

/**
 * Converts a string of digits to a fractional phrase.
 */
function n2t_get_fraction_phrase($num, &$orders) {

	//Precision is a function of the number of digits.
	return('');
}

?>