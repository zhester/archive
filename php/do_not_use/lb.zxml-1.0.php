<?
/****************************************************************************
	ZXML 1.0
	2004-08-13 - Zac Hester

	This is a library that I developed to help with CMS software where I
	use XML tags mixed in amongst HTML content.  Of course, this will work
	with XML-conforming HTML tags, but it should be used only on known
	XML-conforming structures.

	Future plans:
		1. Provide an interface ($obj->set_index(int)) that allows a user
			to tell the parser what tag set to use as the current working
			tag set (all operations will affect only that set and leave
			all other matching sets alone).
			->This can be done passing in the "offset" parameters to most
				regexp functions.
		2. Somehow make an adjustment to user interaction that allows the
			system to signal the user that one or more of the original
			number of tag sets has been eliminated through replacement
			of other tag sets.  The only way this is accomplished right
			now is to continually poll the get_num_tagsets() method.
			->Some kind of token-style interface might work for this.
		3. Figure out some kind of caching mechanism to reduce the number
			of invocations of the regular expression functions.
		4. Integrate support for descension into a tree structure.

	The library is used in a conversational mode:
	-> 1. Initialize class (import source string).
	-> 2. Do you have a "name" tag set?
	<- 3. Yes, 4 of them.
	(begin loop)
	-> 4. What is CDATA of the current tag set?
	<- 5. "32145"
	-> 6. Replace tag set with "STRING"
	(return to top of loop until all tags are handled)
	-> 7. Give me final string.
	<- 8. "FINAL STRING"

	In code, this might look like this:
	$sf = new zxml($input);
	$num_tags = $sf->get_num_tagsets('replace');
	for($i = 0; $i < $num_tags; $i++) {
		switch($sf->get_cdata()) {
			case 'year': $sf->replace_tagset(date('Y')); break;
			case 'file': $sf->replace_tagset($_SERVER['REQUEST_URI']); break;
		}
	}
	$output = $sf->get_final();
****************************************************************************/

class zxml {

	var $doc;			//The source document.
	var $target;		//The tag we're currently working with.
	var $filter_list;	//The list of allowed attributes in a tag.
	var $delimiter;		//Regular expression delimiter.
	var $patterns;		//Some pre-defined patterns.
	var $tag_patterns;	//A list of patterns specific to the working tag.
	var $tag_cdata;		//The captured CDATA from a tag.
	var $tag_attributes;//The captured attribute list (string).

	/**
	 * Constructor.
	 */
	function zxml($source) {

		//Set source document.
		$this->doc = $source;

		//Set list of allowed tag attributes.
		$this->filter_list = array('id', 'name', 'class', 'style');

		//Set pattern delimiter.
		$this->delimiter = '/';

		//Set global patterns.
		$this->patterns = array(
			'left_ab' => preg_quote('<', $this->delimiter),
			'right_ab' => preg_quote('>', $this->delimiter),
			'left_ab_ent' => preg_quote('&lt;', $this->delimiter),
			'right_ab_ent' => preg_quote('&gt;', $this->delimiter),
			'fwd_slash' => preg_quote('/', $this->delimiter),
			'quote' => preg_quote('"', $this->delimiter),
			'quote_ent' => preg_quote('&quot;', $this->delimiter)
		);
	}

/**
 * Public Interface
 */

	/**
	 * Sets the tag information.
	 */
	function set_tag($tagname) {

		//Set tag name.
		$this->target = $tagname;

		//Base pattern (tag name).
		$base = preg_quote($this->target, $this->delimiter);

		$this->tag_patterns = array(
			//Store base pattern in object array.
			'tagname' => $base,
			//Beginning of an open tag (before attribute list).
			'open_start' => '(?:'.$this->patterns['left_ab']
				.$base.'|'.$this->patterns['left_ab_ent'].$base.')',
			//Ending of an open tag (after attribute list).
			'open_stop' => '(?:'.$this->patterns['right_ab']
				.'|'.$this->patterns['right_ab_ent'].')',
			//Closing tag.
			'close' => '(?:'.$this->patterns['left_ab']
				.$this->patterns['fwd_slash'].$base
				.$this->patterns['right_ab'].'|'
				.$this->patterns['left_ab_ent']
				.$this->patterns['fwd_slash'].$base
				.$this->patterns['right_ab_ent'].')'
		);

		//Build a match-only pattern for the opening tag.
		$this->tag_patterns['open'] = $this->tag_patterns['open_start']
			.'.*?'.$this->tag_patterns['open_stop'];

		//Build a match-only pattern for the whole tag set.
		$this->tag_patterns['match'] =
			$this->tag_patterns['open_start']
			.'.*?'.$this->tag_patterns['open_stop']
			.'.*?'.$this->tag_patterns['close'];

		//Build a capture pattern for the whole tag set.
		$this->tag_patterns['capture'] =
			$this->tag_patterns['open_start']
			.'(.*?)'.$this->tag_patterns['open_stop']
			.'(.*?)'.$this->tag_patterns['close'];
	}

	/**
	 * Removes the tag information from the class (cleans the slate).
	 */
	function unset_tag() {
		unset($this->tag_patterns);
		unset($this->target);
	}

	/**
	 * Returns the number of "tagname" tags found in the string.
	 */
	function get_num_tagsets($tagname = '') {

		//Set tag name.
		if($tagname) { $this->set_tag($tagname); }

		//Try to find a match for the tag.
		if(preg_match_all($this->wrap_pattern($this->tag_patterns['match']),
			$this->doc, $matches)) {
			return(count($matches[0]));
		}

		//Sorry, no tag by that name.
		else {
			return(0);
		}
	}

	/**
	 * Returns the CDATA from the next tag of previously set "tagname."
	 */
	function get_cdata() {

		//Capture information from tag set.
		$this->capture();

		//Send back the captured CDATA.
		return($this->tag_cdata);
	}

	/**
	 * Returns the value of an attribute specified by "attribute" from
	 *  the next tag set.
	 */
	function get_attribute($attribute) {

		//Capture information from tag set.
		$this->capture();

		//Return whatever we find for this attribute.
		return($this->get_attval($this->tag_attributes, $attribute));
	}

	/**
	 * Returns an associative array describing all tag sets found and their
	 *  data.  This should only be used when multiple tags of the same
	 *  name will have different replacement values when their information
	 *  is compared to one another (i.e. inter-dependency inferred).
	 */
	function get_tag_info($tagname = '') {

		//Set tag name.
		if($tagname) { $this->set_tag($tagname); }

		//Do a full capture for everything in document.
		preg_match_all($this->wrap_pattern($this->tag_patterns['capture']),
			$this->doc, $matches);

		//Run through each tag set and build info array.
		for($i = 0; $i < count($matches[0]); $i++) {

			//Extract attributes.
			$attkeys = $this->get_attkeys($matches[1][$i]);

			//Line up their values in the array.
			for($j = 0; $j < count($attkeys); $j++) {
				$tag_info[$i][$attkeys[$j]] =
					$this->get_attval($matches[1][$i], $attkeys[$j]);
			}

			//Add in the CDATA.
			$tag_info[$i]['cdata'] = $matches[2][$i];
		}

		//Send back our results.
		return($tag_info);
	}

	/**
	 * Replaces the next tag set with contents of newstring.
	 */
	function replace_tagset($newstring) {

		//Replace the tagset with user-specified string.
		$this->doc = preg_replace(
			$this->wrap_pattern($this->tag_patterns['match']),
			$newstring, $this->doc, 1);
	}

	/**
	 * Modifies the next tag set using user-specified information.
	 */
	function modify_tagset($newname, $newcdata = '') {

		//Capture tag information.
		$this->capture();

		//Check for CDATA update.
		if($newcdata) { $cdata = $newcdata; }
		else { $cdata = $this->tag_cdata; }

		//Filter attribute list.
		$newatts = $this->filter_attributes($this->tag_attributes);

		//Capture bracket types.
		preg_match($this->wrap_pattern(
			'('.$this->patterns['left_ab'].'|'
			.$this->patterns['left_ab_ent'].')'
			.$this->tag_patterns['tagname']
			.'.*?('.$this->patterns['right_ab'].'|'
			.$this->patterns['right_ab_ent'].')'), $this->doc, $match);

		//Replace the old tag set with our new stuff.
		$this->replace_tagset($match[1].$newname.$newatts.$match[2]
			.$cdata.$match[1].'/'.$newname.$match[2]);
	}

	/**
	 * Returns the constructed string.
	 */
	function get_final() {

		//Return string whenever user wants it.
		return($this->doc);
	}

/**
 * Internal Methods
 */

	/**
	 * This function captures the two substrings (attributes and CDATA)
	 * from the next target.
	 * The two strings will then be available through the object
	 * properites "tag_cdata" and "tag_attributes"
	 */
	function capture() {
		preg_match($this->wrap_pattern($this->tag_patterns['capture']),
			$this->doc, $match);
		$this->tag_attributes = $match[1];
		$this->tag_cdata = $match[2];
	}


	/**
	 *  This function screens out all unwanted attributes from a given
	 *  attribute list string.  The list is rebuilt, but all value data
	 *  is preserved for the allowed attributes.
	 */
	function filter_attributes($attstring) {

		//New attribute list buffer.
		$buffer = '';

		//Scan for only the allowed attributes.
		for($i = 0; $i < count($this->filter_list); $i++) {

			//Find any values for the given attribute name.
			$attval = $this->get_attval($attstring, $this->filter_list[$i]);

			//If a value is found, add it to the new list.
			if($attval) {
				$buffer .= ' '.$this->filter_list[$i].'="'.$attval.'"';
			}
		}

		//Return the newly constructed attribute list.
		return($buffer);
	}

	/**
	 *  This function returns the value of an attribute by passing an XML
	 *  attribute string and the name of the attribute to query.  If no
	 *  attribute name is specified the value of the first attribute is
	 *  returned.
	 */
	function get_attval($attstring, $attname = '') {

		if($attname != '') {

			//Quote the attribute name.
			$attname = preg_quote($attname, '/');

			//Set the capturing pattern.
			$cpattern = '/ *'.$attname.' *= *"(.*?)"/i';

			/*
			 * Match one or more spaces
			 *  followed by the attribute name
			 *  followed by 0 or more spaces
			 *  followed by an equals sign
			 *  followed by 0 or more spaces
			 *  followed by a double quote
			 *  followed by 0 or more characters (non-greedy)
			 *  followed by a double quote
			 *  (case-insensitive match)
			 * -> capture the string between the quotes
			 */
		}
		else {

			//Set the capturing pattern.
			$cpattern = '/ *\w+ *= *"(.*?)"/i';

			/*
			 * Match one or more spaces
			 *  followed by one or more word characters
			 *  followed by 0 or more spaces
			 *  followed by an equals sign
			 *  followed by 0 or more spaces
			 *  followed by a double quote
			 *  followed by 0 or more characters (non-greedy)
			 *  followed by a double quote
			 *  (case-insensitive match)
			 * -> capture the string between the quotes
			 */
		}

		//Attempt the capture.
		preg_match($cpattern, $attstring, $cap);

		//If the query is successful, send back the attribute value.
		if($cap[1]) { return($cap[1]); }
		return(false);
	}

	/**
	 * This function returns a list (array) of all the keys (names)
	 * found in an attribute list string (from a tag capture).
	 */
	function get_attkeys($attstring) {

		//Attempt generic attribute field capture.
		if(preg_match_all('/ *(\w+) *= *".*?"/i', $attstring, $matches)) {

			//Return a list of all key names (first capture list).
			return($matches[1]);
		}

		//Return an empty array if nothing was found.
		return(array());
	}

	/**
	 * This function just wraps up a pattern for use with the regular
	 * expression functions.
	 */
	function wrap_pattern($pattern, $flags = 'i') {
		return($this->delimiter.$pattern.$this->delimiter.$flags);
	}
}

/**
 * The zxml_auto class is an extension of the zxml class.  This class
 * allows users to add a simple functionality to the zxml class that is
 * not always needed (hence the use of an extended class).  To speed up
 * processing, don't use this class unless you need the additional
 * functionality.
 *
 * What this class adds is a simple-to-use tool that allows a webmaster
 * to insert <replace></replace> tags within his content that are then
 * translated into a host of different things at the time of page
 * retreival.
 *
 * Of course, all the functions and features of the base class are
 * still available to the user.
 */
class zxml_auto extends zxml {

	var $conf;			//A configuration array to customize behavior.

	/**
	 * The zxml_auto constructor.
	 */
	function zxml_auto($source) {

		//Set base configuation
		$this->conf = array(
			'auto_rt_tag' => 'replace',
			'rt_with_containers' => false,
			'date_format' => 'F j, Y',
			'datetime_format' => 'g:i:s a, F j, Y',
		);

		//Call parent's constructor.
		$this->zxml($source);

		//Try to do any auto replacements on initial document.
		$this->set_tag($this->conf['auto_rt_tag']);
		$this->auto_replace_tagset_with();
		$this->unset_tag();
	}

	/**
	 * Alters default configuration to suit your needs.
	 */
	function set_config($target, $value) {
		$this->set_config[$target] = $value;
	}

	/**
	 * This function gives users a little shortcut to some commonly used
	 * replacement values on typical web sites.
	 *
	 * List of ideas: date,year,days_until(date),days_since(date),
	 *  years_to(date),years_since(date),random_number(range)
	 */
	function replace_tagset_with($replacement_type, $extra = '') {
		if($this->conf['rt_with_containers']) {
			switch($replacement_type) {
				case 'date':
					$this->modify_tagset('span',
						date($this->conf['date_format']));
				break;
				case 'year':
					$this->modify_tagset('span', date('Y'));
				break;
				case 'random_number':
					if(is_array($extra)) {
						$this->modify_tagset('span',
							rand($extra[0], $extra[1]));
					}
					else {
						$this->modify_tagset('span', rand(0,9));
					}
				break;
			}
		}
		else {
			switch($replacement_type) {
				case 'date':
					$this->replace_tagset(date($this->conf['date_format']));
				break;
				case 'year':
					$this->replace_tagset(date('Y'));
				break;
				case 'random_number':
					if(is_array($extra)) {
						$this->replace_tagset(rand($extra[0], $extra[1]));
					}
					else {
						$this->replace_tagset(rand(0,9));
					}
				break;
			}
		}
	}

	/**
	 * This is a nifty little programming hook that allows the parser to
	 * automatically detect tags that should be replaced without any
	 * user intervention.
	 */
	function auto_replace_tagset_with() {

		//Find number of replacement tags.
		$num_tags = $this->get_num_tagsets();

		//Loop through each replacement.
		for($i = 0; $i < $num_tags; $i++) {

			//Capture data.
			$this->capture();
			$type = $this->tag_cdata; $extra = '';

			//Check for special user data needed for this replacement.
			if(strpos($this->tag_cdata, ':')) {

				//Break up any user data supplied.
				$args = explode(':', $this->tag_cdata);
				$type = $args[0];
				$extra = array_slice($args, 1);
				if(count($extra) == 1) { $extra = $args[1]; }
			}

			//Call replace with method.
			$this->replace_tagset_with($type, $extra);
		}
	}
}
?>