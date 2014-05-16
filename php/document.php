<?php

/**
 * get_image_tag
 * Build an HTML image tag based on any file path to an image.
 *
 * @param file The path to the image file.
 * @return The HTML to display an image.
 */
function get_image_tag($file, $alt = '') {
	if(substr($file,0,1) == '/') {
		$path = substr($file, strlen($_SERVER['DOCUMENT_ROOT']));
	}
	else {
		$path = $file;
	}
	$alt = $alt ? htmlentities($alt) : htmlentities(basename($file));
	$b = '<img src="'.$path.'" alt="'.$alt.'" title="'.$alt.'"';
	if(file_exists($file)) {
		list($w, $h) = getimagesize($file);
		$b .= ' style="width:'.$w.'px; height:'.$h.'px;"';
	}
	return($b.' />');
}


/**
 * Makes sure a specified URL is ready for use on a web page.
 */
function urlize($url) {
	if(!preg_match('#^http\://#', $url)) {
		return('http://'.$url);
	}
	return($url);
}

/**
 * Makes sure the dollar sign is used on a user-supplied money amount.
 */
function get_money($input) {
	$input = trim($input);
	if(substr($input, 0, 1) != '$') {
		return('$'.$input);
	}
	return($input);
}


/**
 * Returns some reasonable markup for a plain text string.
 *
 * This function runs its own nl2br()-like transform as well as scanning
 * for any hyperlink-able strings.  This also won't interfere with any
 * preexisting HTML links.  HTML block-level formatting on the input should
 * be avoided (extra vertical spaces).
 *
 * @author Zac Hester <zac@zacharyhester.com>
 * @date 2005-07-15
 *
 * @param input The document to mark up
 * @param paragraphs Tells the function to break blocks using <p></p>
 * @return The marked up document
 */
function get_html($input, $paragraphs = false) {

	//Check for paragraph scanning and insertion.
	if($paragraphs) {
		return(

			//Start the first paragraph.
			'<p>'

			//Insert paragraph breaks for each double break.
			.str_replace("<br />\n <br />\n", "</p>\n<p>", get_html($input))

			//Finish the last paragraph.
			.'</p>'
		);
	}

	//Check referer for a query string in referer.
	if(preg_match('/(\?|&)q=([^&]+)/', $_SERVER['HTTP_REFERER'], $m)) {

		//Try to build a regexp to match all the words in the page.
		$qpattern = '/\b('.str_replace(' ', '|', urldecode($m[2])).')\b/i';
	}
	else {

		//Hopefully, this will never match.
		$qpattern = '/\bqX4jZ8\b/';
	}

	//Standard markup sequence.
	return(
		//Link really obvious hyperlinks.
		preg_replace('#(\s)(http://\S+)#i',
			'\1<a href="\2">\2</a>',

			//Link more subtle hyperlinks.
			preg_replace('/(\s)(www\.\S+)/i',
				'\1<a href="http://\2">\2</a>',

				//Link email addresses.
				preg_replace('/(\s)([\w\._-]+?@\S+)/',
				'\1<a href="mailto:\2">\2</a>',

					//Highlight any search words.
					preg_replace($qpattern,
						'<span class="hl">\1</span>',

						//Through in some breaks.
						str_replace("\n",
							" <br />\n",

							//Get rid of any carriage returns.
							str_replace("\r", '', $input)
						)
					)
				)
			)
		)
	);
}

/**
 * Large string truncating function.
 *
 * This function attempts to "intelligently" truncate a long string
 * (from a content field) into a "preview" string for things like
 * search results and index listings.
 *
 * @author Zac Hester
 * @date 2005-11-01
 *
 * @param input The string to be truncated
 * @param br A list (array) of preferred line endings
 * @param max The maximum number of characters to allow
 * @param more A string ending to use if it was truncated
 * @return The truncated string
 */
function truncate($input, $br=array("\n\n","\n"), $max=128, $more='...') {

	//No tags allowed.
	$input = strip_tags($input);

	//Check for short strings.
	if(strlen($input) <= $max) { return($input); }

	//Check each specified breaker in order.
	foreach($br as $breaker) {
		$offset = strpos($input, $breaker);
		if($offset !== false) {
			$input = substr($input, 0, $offset);
			break;
		}
	}

	//Check for max chars.
	if(strlen($input) > $max) {

		//We use the wordwrap function as a sneaky way of pulling a single
		//  "line" of content from the string (a regexp would be cooler).
		$temp = wordwrap($input, $max, "___");
		$input = substr($input, 0, strpos($temp, '___'));
	}

	//Send back the truncated string.
	return(htmlspecialchars($input.$more));
}

/**
 * Extract a reasonable title from a very plain (no head or body)
 * HTML/PHP document.
 */
function get_doc_title($filename = '', $separator = ' - ') {
	//Try to find our own title if no file is specified.
	if(!$filename) { $filename = $_SERVER['SCRIPT_FILENAME']; }
	$fh = @fopen($filename, 'r');
	if(!$fh) { return(false); }
	//Scan first 10 lines for title.
	for($i = 0; $i < 10; ++$i) {
		$line = fgets($fh, 64);
		//Look for heading tags.
		if(preg_match('/<h\d[^>]*>([^<]+)<\/h\d>/i', $line, $m)) {
			fclose($fh);
			return(trim($m[1]).$separator);
		}
		//Look for HTML comment title.
		if(preg_match('/<\!-- (.+) -->/', $line, $m)) {
			fclose($fh);
			return(trim($m[1]).$separator);
		}
		//Look for PHP comment title.
		if(preg_match('#<\?(?:php)? //(.+)\n#i', $line, $m)) {
			fclose($fh);
			return(trim($m[1]).$separator);
		}
	}
	//No title found.
	fclose($fh);
	return(false);
}

?>