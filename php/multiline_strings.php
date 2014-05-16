<?php
/****************************************************************************
	Multiline Strings
	Zac Hester

	A group of functions for manipulating strings containing multiple
	lines where order of lines can be important (CSV is one example).

	This file and updates were done on 2009-02-11
****************************************************************************/


/**
 * move_line
 * Moves a line up or down in a string and returns the new string.
 *
 * This is an in-place, constant big-O algorithm (thanks, I tried).
 *
 * @author Zac Hester
 * @date 2005-10-12
 *
 * @param subject The multiline string
 * @param target The string of the line we will move
 * @param direction Which way we will move 'target' in 'subject'
 * @return The reordered multiline string
 */
define('ML_DOWN', 0);
define('ML_UP', 1);
function move_line($subject, $target, $direction = ML_UP) {

	//Break apart the string into lines.
	$lines = explode("\n", trim($subject));

	//Count up the number of lines.
	$nlines = count($lines);

	//Run through each line.
	for($i = 0; $i < $nlines; ++$i) {

		//Is the current line our target?
		if($lines[$i] == $target) {

			//Check direction and boundaries.
			if($direction == ML_DOWN && ($i+1) < $nlines) {
				$lines[$i] = $lines[$i+1];
				$lines[$i+1] = $target;
			}
			else if(($i-1) >= 0) {
				$lines[$i] = $lines[$i-1];
				$lines[$i-1] = $target;
			}
			//If our movement crosses a boundary, do nothing.

			//Once we've found our line, we're done.
			break;
		}
	}

	//Rebuild and return the reordered string.
	return(implode("\n", $lines));
}


/**
 * remove_line
 * Remove a single line from a multiline string.
 *
 * @param subject The multiline string one which to operate
 * @param target The content of the line to remove
 * @return The string with the missing line (if found)
 */
function remove_line($subject, $target) {
	$target = trim($target);
	$lines = explode("\n", trim($subject));
	$data = array();
	foreach($lines as $line) {
		$line = trim($line);
		if($line != $target) {
			$data[] = $line;
		}
	}
	return(implode("\n", $data));
}


/**
 * edit_line
 * Change a particular line in a multiline string.
 *
 * @param subject The string on which to operate
 * @param target The line to change
 * @param newvalue The new line to write in place of the target
 * @return A string with the updated line (if found)
 */
function edit_line($subject, $target, $newvalue) {
	$target = trim($target);
	$lines = explode("\n", trim($subject));
	foreach($lines as $k => $line) {
		$line = trim($line);
		if($line == $target) {
			$lines[$k] = $newvalue;
		}
		else {
			$lines[$k] = $line;
		}
	}
	return(implode("\n", $lines));
}


/**
 * add_line
 * Add a new line to a multiline string (mostly for functional completeness).
 *
 * @param subject The string on which to operate
 * @param newvalue The new line to add to the string
 * @return The string with a new line added.
 */
function add_line($subject, $newvalue) {
	$subject = trim($subject);
	if(strlen($subject) == 0) {
		return($newvalue);
	}
	return($subject."\n".$newvalue);
}

?>