<?php
/****************************************************************************
	Useful Document Handling Functions
	Zac Hester - 2005-06-28
****************************************************************************/


/**
 * Returns some reasonable markup for a plain text string.
 */
function get_html($input) {
	return(
		//Link really obvious hyperlinks.
		preg_replace('#(\s)(http://\S+)#i', '\1<a href="\2">\2</a>',
			//Link more subtle hyperlinks.
			preg_replace('/(\s)(www\.\S+)/i', '\1<a href="http://\2">\2</a>',
				//Link email addresses.
				preg_replace('/(\s)([\w\._-]+?@\S+)/', '\1<a href="mailto:\2">\2</a>',
					//Through in some breaks.
					str_replace("\n", " <br />\n",
						//Get rid of any carriage returns.
						str_replace("\r", '', $input)
					)
				)
			)
		)
	);
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