<?php

/**
 * json_get_string
 * Build a JSON-compliant document from a PHP array.
 *
 * @author Zac Hester
 * @date 2006-04-09
 *
 * @param tree The PHP array to convert
 * @param quote String literal enclosure
 * @return A string representing the JSON document
 */
function json_get_string($tree, $quote = '"') {

	//Initialize an empty array to keep a list of value literals.
	$chunks = array();

	//Grab the keys of the array.
	$keys = array_keys($tree);

	//Check for numeric section.
	//If the first index isn't 0, this will think that the indexes are
	//  important to preserve and handle it as an associative array.
	if(is_int($keys[0]) && $keys[0] == 0) {

		//Loop through the numeric array.
		foreach($tree as $v) {

			//Arrays get processed.
			if(is_array($v)) {
				$chunks[] = json_get_string($v, $quote);
			}

			//Individual values are added to the list.
			else {
				$chunks[] = json_get_literal($v, $quote);
			}
		}

		//Build a string representing this array.
		return('['.implode(', ',$chunks).']');
	}

	//Associative section.
	else {

		//Loop through the associative array.
		foreach($tree as $k => $v) {

			//Arrays get processed.
			if(is_array($v)) {
				$chunks[] = "$quote$k$quote:".json_get_string($v,$quote);
			}

			//Individual elements are added to the list.
			else {
				$chunks[] = "$quote$k$quote:".json_get_literal($v,$quote);
			}
		}

		//Build a string representing this "object."
		return('{'.implode(', ',$chunks).'}');
	}
}


/**
 * json_get_literal
 * Returns an appropriate ECMAScript literal for a PHP variable.
 * You should probably consider this function "private" to this file.
 *
 * @author Zac Hester
 * @date 2006-04-09
 *
 * @param v The PHP variable
 * @param quote Desired string literal enclosure
 * @return A string representing an ECMAScript literal
 */
function json_get_literal($v, $quote) {

	//Character substitutions (order is important).
	static $targets = array('\\','"','/',"\n","\t","\r","\u");
	static $subs = array('\\\\','\\"','\\/','\\n','\\t','\\r','\\u');

	//Boolean values.
	if(is_bool($v)) {
		return($v ? 'true' : 'false');
	}

	//Find nulls (really?).
	else if(is_null($v)) {
		return('null');
	}

	//Numeric values.
	else if(is_int($v) || is_float($v)) {
		return($v);
	}

	//Everything else is a string (just put your fingers in your ears).
	else {
		return($quote.str_replace($targets,$subs,$v).$quote);
	}
}

?>