<?
/****************************************************************************
	CSV File Handling
	Zac Hester - 2005-01-31

	Copyright 2004-2005 Zac Hester. All rights reserved.
	See LICENSE.txt for copyright information.

	This is the collection of functions that maintain CSV files.

	Functions and Implementations to Build:
		add column processor (look for a checkmark) csvaddcolumn
		add row processor (look for checkmark) csvaddrow
		delete based on checkmarks (rows and columns) csvdeletechecked
		swap rows/columns based on first two check marks or first checkmark
  			and next neighbor
		reorder CSV file -- sort based on first column checkmarked
****************************************************************************/

/**
 * Writes an array out to a file pointer as a line of CSV data.
 */
function myfputcsv($fh, $line, $fd = ',', $quote = '"') {
	$buffer = '';
	foreach($line as $cell) {
		//Escape quotes.
		$cell = str_replace($quote, $quote.$quote, $cell);
		//Check for fields that need to be quoted.
		if(strstr($cell, $fd) !== false
			|| strstr($cell, $quote) !== false
			|| strstr($cell, "\n") !== false) {
			$buffer .= $quote.$cell.$quote;
		}
		else {
			$buffer .= $cell;
		}
		$buffer .= $fd;
	}
	//Strip final field delimiter and add a newline.
	$buffer = substr($buffer, 0, -strlen($fd))."\n";
	//Write line to handle.
	fputs($fh, $buffer);
	//Send back number of bytes written.
	return(strlen($buffer));
}


?>