<?
/****************************************************************************
	Number-to-Text Converter
	Zac Hester - 2004-09-22

	A seldom-used library to convert actual numbers (like the results of a
	mathematical operation) to a string of English words that describes the
	number.

	Future Plans
	- Support fractional parts of numbers.
****************************************************************************/

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
		$hun_name = get_hundred_phrase($ctrip);

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
		return(get_ordinal_version($buffer));
	}
	
	//Send back the number as text.
	return($buffer);
}

/**
 * Determines the expression used to describe any three digits.
 */
function get_hundred_phrase($triplet) {

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
function get_ordinal_version($phrase) {

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
	else if(preg_match('/y$/', $phrase)) {
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
function get_fraction_phrase($num, &$orders) {

	//Precision is a function of the number of digits.
	return('');
}

/*
//Test cases.
header('Content-Type: text/html');
$tests = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10',
	'11', '20', '21', '50', '100', '101', '152', '352', '1000',
	'-1001', '1020', '1021', '1432', '-20000', '30123',
	'46345', '100000', '600345', '2,543,876',
	'5 481 168 651 567 683 189 873 518 798', '023400000004',
	1.25e10);
for($i = 0; $i < count($tests); $i++) {
	echo $tests[$i].' -- '.num2text($tests[$i]).'<br />';
}
*/
?>