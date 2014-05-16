<?php

/**
 * packjs
 * Attempts to compress a string of JavaScript to save a little bandwidth.
 * Note: This makes a few key assumptions on coding style, so stuff that is
 * coded to a relaxed style (NTTAWWT) may get garbled.
 *
 * @author Zac Hester
 * @date 2007-09-28
 *
 * Known issues:
 *  - String literals with punctuation will get clobbered.
 *  - Regular expressions that use a literal space will get clobbered.
 *  - Function literal assignment like "var f = function() {}" will get
 *    hosed if the literal isn't terminated (;) like a regular line of
 *    code (even though this is ECMA-okay if the literal is the last
 *    thing on the line).
 *  + For now, this can be toggled with the second parameter.
 *
 * Future features:
 *  - Attempt to track down block-scoped variables and rename them.
 *  - Rescan compressed code and add moderate line wrapping.
 *  - Look for super-common function names with more than a couple
 *    characters.  Write an alias function, and refactor.
 *
 * @param js The JavaScript source string.
 * @param loose Set this to specify the code might be a little "loose."
 * @return The compressed JavaScript string.
 */
function packjs($js, $loose = false) {

	//Convert strange line endings.
	if(strpos($js, "\r\n") !== false) {
		$js = str_replace("\r", '', $js);
	}
	if(strpos($js, "\r") !== false) {
		$js = str_replace("\r", "\n", $js);
	}

	//Regular expression filter.
	$filter = array(

		//Strip all comments.
		'#//[^\n]*\n#' => '',
		'#/\*(.*?)\*/#ms' => '',

		//Strip all leading whitespace.
		'/(\n)\s+/' => '$1',

		//Strip all trailing whitespace.
		'/\s+(\n)/' => '$1',

		//Strip all empty lines.
		'/(\n)\n/' => '$1'
	);

	//Check to see if we avoid this compression step.
	if(!$loose) {

		//Attempt to remove more extraneous white space
		//  (might mess with some string literals and regexps).
		$filter['#\s+([-+/*=!?<>|&^~()\[\]{},.:;])#'] = '$1';
		$filter['#([-+/*=!?<>|&^~()\[\]{},.:;])\s+#'] = '$1';
	}

	//Send back the slightly more packed JavaScript.
	return(
		trim(
			preg_replace(
				array_keys($filter),
				array_values($filter),
				$js
			)
		)
	);
}



/**
 * packjs_file
 * Compresses a JavaScript source file and writes to a new file.
 *
 * @param source The path to the JavaScript source file.
 * @param target The path to write the compressed JavaScript file.
 * @return True on success.
 */
function packjs_file($source, $target) {
	if(file_exists($source)) {
		$js = packjs(file_get_contents($source));
		$fh = fopen($target, 'w');
		if($fh) {
			fwrite($fh, $js);
			fclose($fh);
			return(true);
		}
	}
	return(false);
}


?>