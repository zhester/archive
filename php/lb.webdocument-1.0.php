<?php
/****************************************************************************
	Web Document Library
	Version 1.0.0
	Zac Hester <zac@planetzac.net>
	2005-07-02

	The idea is to build a nice, clean class that can handle taking most
	text and some HTML/XML and turn it into more web-friendly document
	components.  It's like the ultimate nl2br(htmlspecialchars()).

	This class is simple enough to be used on it's own, but can be even more
	powerful when combined with a templating tool or a separate document
	parser.  The intention is to be a central library for things like CMS
	front ends, message boards, blogs, etc.  Then, you can just inform your
	users what things will be altered in their input (or give them a list of
	checkboxes for the features they want) and use this library to do all
	the "magic."

	This library was designed with the idea that we can store a lot of
	content as plain text or very simple HTML documents and then feed other
	applications with more HTML-enabled documents with things laid out the
	way they _should_ be.  Just think of the limited amount of formatting
	most email clients provide even though the user is typing plain text.

	It's important to note that although this class will generate a lot of
	HTML, none of it describes any style information.  Everything is designed
	to compliment a very standard style sheet.  Furthermore, all code
	generated internally (that isn't overridden by the user) conforms to
	the XHTML 1.0 Strict DTD.  Therefore, you can use it in absolutely any
	target DTD and it will render perfectly.

	Simple Example:

	require('lb.webdocument-1.0.php');
	$wd = new webdocument();
	$wd->load_file('mycontentfile.txt');
	echo $wd->get_document(WEBDOC_STANDARD);

	To Do:
	Finish document and toc methods
	Create allowsimple method to re-filter certain tags that may have been
		converted during htmlenchars/htmlspchars.
	Create user interface to adjust the list of tags allowed by allowsimple.
	Put all default format strings in define()s and change output to constants
	Write driver script.
	Test all methods.
	Create bbcode
	Create newsys

	Later:
	Create a set of custom modifiers that can take some SGML-syntax markup
	and convert using builtin functions.  Big example is a custom image
	tag like (closing tags are shown spaced here for PHP's sake)
		<?xtx image,images/image.gif,"Image Caption" ? > --> image tag
		<?xtx link,folder/document.html,"Link Label" ? > --> anchor tag
		<?xtx source,folder/somefile.ext ? > --> readfile()s the target
		<?xtx inline,folder/externalpage.html ? > --> iframe
		<?xtx include,folder/script.php ? > --> PHP include???
		<?xtx swf,folder/flashfile.swf ? > --> media or object tag
	The format is SGML on the outside, and CSV on the inside.
	The parser is going to be very dumb, so don't try nesting or anything
	crazy (it's just going to look for SGML blocks and grab entire blocks).
	These are only meant to be replacers and not regular document region
	tags (that's what BB code or simplified HTML is good at).

	Much Later:
	Create an encoding based on the XTX format that works closely with
	HTML forms (and possibly Xforms).


	Changes:
	2005-07-02 - Initial code design and layout.
****************************************************************************/


/**
 * Output control constants.
 * These are used when specifying how you want the document to display.
 * Note: HTMLENCHARS and HTMLSPCHARS do about the same things, so expect
 * strange things to happen if you put both of them in a feature list.
 */

//Individual features.
define('WEBDOC_HTMLENCHARS',	1);		//Replace all HTML entities.
define('WEBDOC_HTMLSPCHARS',	2);		//Replace common HTML entities.
define('WEBDOC_BREAKS',			4);		//Insert HTML line breaks.
define('WEBDOC_LINKS',			8);		//Markup hyperlinks.
//define('WEBDOC_BBCODE',			16);	//Convert BBcode to HTML.
define('WEBDOC_AUTOMODS',		32);	//Automatically run any modifiers.
define('WEBDOC_TEXTFORMAT',		64);	//Convert text formatting to HTML.
//define('WEBDOC_ALLOWSIMPLE',	128);	//Allow a few, choice tags through.
define('WEBDOC_DOCUMENT',		256);	//Formats an entire document.
//define('WEBDOC_NEWSYS',			4096);	//Formats an entire document based
										// on the NewSys source file format.

//Shortcuts to common feature sets.
define('WEBDOC_INLINE',		WEBDOC_SPCHARS|WEBDOC_LINKS);
define('WEBDOC_CDATA',		WEBDOC_HTMLSPCHARS);
define('WEBDOC_QUOTEVALUE',	WEBDOC_HTMLENCHARS);
define('WEBDOC_BLOCK',		WEBDOC_SPCHARS|WEBDOC_BREAKS|WEBDOC_LINKS);
define('WEBDOC_STANDARD',	WEBDOC_BLOCK|WEBDOC_DOCUMENT);
define('WEBDOC_ALL',		8191);


/**
 * A few settings that might be nice to tweak.
 * (If you don't understand what's going on here, don't edit these.)
 */

//XTX (eXtended Text) Format configuration.
define('WDCN_XTX_TOPHEADING', 2);	//Topmost heading level to use.

//Default markup (it's all XHTML, use CSS to style).
define('WDCN_MU_BR', 		'<br />');
define('WDCN_MU_HR', 		'<hr />');
define('WDCN_MU_UL', 		'<ul>%s</ul>');
define('WDCN_MU_OL', 		'<ol>%s</ol>');
define('WDCN_MU_LI', 		'<li>%s</li>');
define('WDCN_MU_P', 		'<p>%s</p>');
define('WDCN_MU_BQ', 		'<blockquote>%s</blockquote>');
define('WDCN_MU_STRONG',	'<strong>%s</strong>');
define('WDCN_MU_EM',		'<em>%s</em>');
define('WDCN_MU_AH',		'<a href="%s">%s</a>');
define('WDCN_MU_AN', 		'<a name="%s">%s</a>');
define('WDCN_MU_H', 		'<h%1$d>\2%s</h%1$d>');
define('WDCN_MU_IMG', 		'<img src="%1$s" alt="%2$s" title="%2$s"%3$s />');
define('WDCN_MU_IMGWH',		' style="width:%dpx; height:%dpx;"');
define('WDCN_MU_PARENT',	'<div class="%1$s">%2$s</div>');
define('WDCN_MU_HL', 		'<span class="highlight">%1$s</span>');


/**
 * Web Document Class
 *
 * @author Zac Hester <zac@planetzac.net>
 * @version 1.0.0
 */
class webdocument {

	var $buffer;			//The document with which we are working.
	var $container_class;	//A possible class value for a parent container.
	var $mod_regexps;		//Modifier regular expression list.
	var $mod_replacements;	//Modifier replacement string list.
	var $mod_run;			//Set to schedule modifier execuation on output.
	var $method_registry;	//Registry of document feature methods.
	var $argument_registry;	//Registry of argument lists for those methods.


	/**
	 * Constructor.
	 *
	 * @param newbuffer Optional initial text buffer provided by user.
	 * @param container_class Optional class attribute value for HTML.
	 */
	function webdocument($newbuffer = '', $container_class = '') {

		//User gave us an initial text document or string.
		if($newbuffer) {

			//Import text.
			$this->import_text($newbuffer);
		}

		//Set container class (if specified).
		$this->container_class = $container_class;

		//Initialize.
		$this->mod_regexps = array();
		$this->mod_replacements = array();
		$this->mod_run = false;
		$this->register_methods();
	}


	/**
	 * Imports a file into the internal buffer.
	 *
	 * @param filename The name of the file to load.
	 * @param append Optional buffer loading mode.
	 * @return Success of loading.
	 */
	function import_file($filename, $append = false) {

		//Make sure the file is there.
		if(file_exists($filename)) {

			//Bring in contents of file.
			$this->import_text(file_get_contents($filename), $append);
			return(true);
		}
		return(false);
	}


	/**
	 * Imports a plain text document into internal buffer.
	 *
	 * @param input Text string/document to load into internal buffer.
	 * @param append Optional buffer loading mode.
	 * @return Success of loading.
	 */
	function import_text($input, $append = false) {

		//Check for possible old Mac text format.
		if(strpos($input, "\n") === false) {
			$input = str_replace("\r", "\n", $input);
		}

		//Check for possible Windows/VMS text format.
		if(strpos($input, "\r") !== false) {
			$input = str_replace("\r", '', $input);
		}

		//Check for append mode.
		if($append) {

			//Add to the buffer.
			$this->buffer .= $input;
		}
		else {

			//Load the buffer.
			$this->buffer = $input;
		}

		return(true);
	}


	/**
	 * Retrieves the final document.
	 *
	 * @param features List of features as a bit field.
	 * @return The final web document with markup.
	 */
	function get_document($features = 0) {

		//Run through features.
		for($i = 0; $i < 8192; $i = pow(2, $i)) {

			//Check the registry for a method on this bit.
			if($this->method_registry[$i] &&
				method_exists($this, $this->method_registry[$i])) {

				//Call the method to operate on the buffer.
				call_user_func(array($this, $this->method_registry[$i]));
			}
		}

		//Check for scheduled modifier execution.
		if($this->mod_run) { $this->run_modifiers(); }

		//Check for container class.
		if($this->container_class) {
			return(sprintf(WDCN_MU_PARENT,
				$this->container_class, $this->buffer));
		}

		//Send back the buffer.
		return($this->buffer);
	}


/****************************************************************************
 * Custom text/(X)HTML filters.
 * These are used internally, but their interface is made such that any
 * user can benefit from their functionality.
 ***************************************************************************/

	/**
	 * Home brewed nl2br implementation.
	 *
	 * @param input Document string.
	 * @return Document string with (X)HTML newlines.
	 */
	function nl2br($input) {
		return(str_replace("\n", ' '.WDCN_MU_BR."\n", $input));
	}

	/**
	 * Try to hyperlink anything that looks hyperlinkable while
	 * avoiding things that are already hyperlinked.
	 *
	 * @param input Document string.
	 * @return Document string with hyperlinked text.
	 */
	function hyperlink($input) {
		$patterns = array(
			'#(\s)(http://\S+)#i',		//Link really obvious hyperlinks.
			'/(\s)(www\.\S+)/i',		//Link more subtle hyperlinks.
			'/(\s)([\w\._-]+?@\S+)/'	//Link email addresses.
		);
		$replacements = array(
			'\1'.sprintf(WDCN_MU_AH, '\2', '\2'),
			'\1'.sprintf(WDCN_MU_AH, 'http://\2', '\2'),
			'\1'.sprintf(WDCN_MU_AH, 'mailto:\2', '\2')
		);
		return(preg_replace($patterns, $replacements, $input));
	}

	/**
	 * Runs simple formatting on a non-document-level string.
	 *
	 * @param input The input string.
	 * @return String with basic markup.
	 */
	function markup_string($input) {
		return($this->hyperlink($this->htmlspchars($input)));
	}

	/**
	 * Use the newline formatted text to display an (X)HTML list.
	 *
	 * @param input Local document string.
	 * @param ordered Option to build an ordered list instead of an U.L.
	 * @return Local document string as a list.
	 */
	function get_list($input, $markup = WDCN_MU_UL) {

		//String buffer.
		$buffer = '';

		//Break apart list items.
		$lines = explode("\n", $input);

		//Make sure we found some list items.
		if(count($lines)) {

			//Run through each list item.
			foreach($lines as $line) {
				$buffer .= sprintf(WDCN_MU_LI, $this->markup_string($line));
			}
			return(sprintf($markup, $buffer));
		}
		return(false);
	}

	/**
	 * DOCUMENT XTX FORMAT HERE!
	 * @param
	 * @param
	 * @return
	 */
	function get_document($input) {
		$blocks = explode("\n\n", $input);
		$buffer = '';
		foreach($blocks as $block) {

			//Block with internal breaks.
			if(strpos($block, "\n") !== false) {
				if(false) {
//////////// UC
					//we can check for bulleted lists based on tabs
					//blockquotes?
				}
				else {
					$buffer .= sprintf(WDCN_MU_P, ltrim($block));
				}
			}

			//Short, single-line block (headings).
			else if(strlen(trim($block)) < 64) {
//////////// label headings with <a name=""></a>
				$level = substr_count($block, "\t") + WDCN_XTX_TOPHEADING;
				$buffer .= sprintf(WDCN_MU_H. $level, ltrim($block));
			}

			//Single-line block (normal).
			else {
///////////// check for lines made up entirely of non-alpha chars <hr />
				$buffer .= sprintf(WDCN_MU_P, ltrim($block));
			}
		}
		return($input);
	}

	/**
	 * @param
	 * @param
	 * @return
	 */
	function get_toc($input) {
/////////////////////////////// UC
		return($input);
	}

	/**
	 * Takes most kinds of strings and converts into a human-friendly
	 * string that could be used as a title or short description.
	 *
	 * @param input The input string.
	 * @param type The type of string sent (filename,)
	 * @return A human-friendly string.
	 */
	function get_human_string($input, $type = 'unknown') {
		if($type == 'filename') {
			$input = preg_replace('/\.[A-Za-z0-9_]{1,5}$/', '', $input);
		}
		return(
			ucwords(
				str_replace('_', ' ',
					preg_replace('/[^A-Za-z0-9_-]/', '', $input)
				)
			)
		);
	}

	/**
	 * Converts old-school text formatting from Usenet and other ASCII-only
	 * formats into HTML.
	 *
	 * @param input The input string.
	 * @return A string with text formatting marked up.
	 */
	function textformat($input) {
		$patterns = array(
			'/\*{1,3}([^\*]{1,64})\*{1,3}/',
			'/_{1,3}([^_]{1,64})_{1,3}/'
		);
		$replacements = array(
			sprintf(WDCN_MU_STRONG, '\1'),
			sprintf(WDCN_MU_EM, '\1')
		);
		return(preg_replace($patterns, $replacements, $input));
	}

	/**
	 * Highlights all the words in a list of words in the document.
	 * The markup is specified using an sprintf format string.
	 * (It uses place holders to keep the arguments in line.)
	 * (You can also use a '%2$d' somewhere if you want to specify the
	 * number of the word which correlates to the index of the word
	 * in the words list array.)
	 *
	 * @param input Input string.
	 * @param words List of words to highlight.
	 * @param markup (X)HTML to use for highlighting.
	 * @return Document with highlighting.
	 */
	function highlight($input, $words, $markup = WDCN_MU_HL) {
		$newwords = array();
		foreach($words as $i => $word) {
			$newwords = @sprintf($markup, $word, $i);
		}
		return(str_replace($words, $newwords, $input));
	}


/****************************************************************************
 * File-level access and sensing functions.
 ***************************************************************************/

	/**
	 * Extract a reasonable title from a very plain (no head or body)
	 * HTML/PHP document.
	 *
	 * @param filename
	 * @param separator
	 * @return A string containing the title or boolean falst on failure.
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


/****************************************************************************
 * Custom HTML-specific text filters.
 ***************************************************************************/

	/**
	 * We intend to use htmlentities() for all HTML quoted value
	 * (attribute value) formatting.
	 *
	 * @param input Document string.
	 * @return Document string with HTML entity encoding.
	 */
	function htmlenchars($input) {
		return($this->htmlmbchars(htmlentities($input, ENT_QUOTES)));
	}

	/**
	 * We intend to use htmlspecialchars() for all HTML used as CDATA.
	 *
	 * @param input Document string.
	 * @return Document string with some special characters encoded.
	 */
	function htmlspchars($input) {
		return($this->htmlmbchars(htmlspecialchars($input, ENT_NOQUOTES)));
	}

	/**
	 * Attempt to replace extended and multibyte characters with HTML
	 * encoded equivalents.
	 *
	 * @param input Document string.
	 * @return Document string with MB entity encoding.
	 */
	function htmlmbchars($input) {
   		$chars = array(128 => '&#8364;', 130 => '&#8218;', 131 => '&#402;',
			132 => '&#8222;', 133 => '&#8230;', 134 => '&#8224;',
			135 => '&#8225;', 136 => '&#710;', 137 => '&#8240;',
			138 => '&#352;', 139 => '&#8249;', 140 => '&#338;',
			142 => '&eacute;', 145 => "'", 146 => "'", 147 => '&quot;',
			148 => '&quot;', 149 => '&#8226;', 150 => '&#8211;',
			151 => '&#8212;', 152 => '&#732;', 153 => '&#8482;',
       		154 => '&#353;', 155 => '&#8250;', 156 => '&#339;',
       		158 => '&#382;', 159 => '&#376;');
		return(
			str_replace(
				array_map('chr', array_keys($chars)),
				$chars,
				$input
			)
		);
	}

	/**
	 * Utility function to remove HTML encoding.
	 * @param input Document string.
	 * @return Document string without entity encoding.
	 */
	function unhtmlenchars($input) {
		$trans_tbl = get_html_translation_table(HTML_ENTITIES);
		$trans_tbl = array_flip($trans_tbl);
		return preg_replace('/&#(\d+);/me', "chr('\\1')",
			strtr($input, $trans_tbl)
		);
	}


/****************************************************************************
 * Image tag utilities.
 ***************************************************************************/

	/**
	 * Builds an image tag based on some very basic information.
	 *
	 * @param nodename The file name of the image.
	 * @param webpath The path to find the image via HTTP.
	 * @param title A caption to use for the image.
	 * @param fspath The path to find the image via the local filesystem.
	 * @return An image tag suitable for an (X)HTML document.
	 */
	function get_image_tag($nodename,$webpath='.',$title='',$fspath=false) {

		//Try to locate file on local file system.
		if($fspath && file_exists($fspath.'/'.$nodename)) {
			list($w, $h) = @getimagesize($fspath.'/'.$nodename);
		}

		//Try to locate file using web path (either local or HTTP).
		else if(file_exists($webpath.'/'.$nodename)) {
			list($w, $h) = @getimagesize($webpath.'/'.$nodename);
		}

		//Check for user-specified title.
		if($title) {
			$title = $this->htmlenchars($title);
		}
		else {
			$title = $this->get_human_string($nodename, 'filename');
		}

		//Build the image tag.
		return(
			sprintf(
				WDCN_MU_IMG,
				(!$webpath || $webpath == '.' ? '' : $webpath.'/').$nodename,
				$title,
				($w && $h ? sprintf(WDCN_MU_IMGWH, $w, $h) : '')
			)
		);
	}

	/**
	 * Builds a set of image tags based on a list of file names and other
	 * data.
	 * Most arguments can be either arrays or strings and the
	 * function should be able to gracefully handle each situation.
	 * When using arrays, each array must have the same number of elements
	 * as the first array ($nodes).
	 * Markup is handled by sprintf formatting strings.
	 *
	 * @param nodes An array of image file names.
	 * @param wpaths An array or string of web path info.
	 * @param titles An array of titles for each image.
	 * @param fpaths An array or string of FS path info.
	 * @param markup An array or string of HTML (sprintf).
	 * @param showtitles An array or string of HTML (sprintf).
	 * @return A snippet of HTML that will display all images.
	 * @see get_image_tag
	 */
	function get_image_tags($nodes, $wpaths = array(),
		$titles = array(), $fpaths = array(),
		$markup = WDCN_MU_P, $showtitles = WDCN_MU_BR.'%s') {

		//String buffer.
		$buffer = '';

		//Run through each image file.
		$i = 0;
		foreach($nodes as $node) {

			//Grab image tag.
			$buffer0 = $this->get_image_tag(
				$node,
				(is_array($wpaths) ? $wpaths[$i] : $wpaths),
				$titles[$i],
				(is_array($fpaths) ? $fpaths[$i] : $fpaths)
			);

			//Check to see if we display a title below the image.
			if($showtitles) {
				if($titles[$i]) {
					$title = $this->htmlenchars($titles[$i]);
				}
				else {
					$title = $this->get_human_string($node, 'filename');
				}
				if(is_array($showtitles)) {
					$buffer0 .= sprintf($showtitles[$i], $title);
				}
				else {
					$buffer0 .= sprintf($showtitles, $title);
				}
			}

			//Load outer buffer.
			if(is_array($markup)) {
				$buffer .= sprintf($markup[$i], $buffer0);
			}
			else {
				$buffer .= sprintf($markup, $buffer0);
			}
			++$i;
		}
		return($buffer);
	}


/****************************************************************************
 * Modifier control.
 * Modifiers are sets of regular expressions with counterpart replacement
 * strings.  These can be used internally or by the user application.
 * Additionally, they can be run manually at any time (in the case of
 * incremental conversion requirements), or run immediately before the
 * document is returned to the user.
 ***************************************************************************/

	/**
	 * Sets a modifier.
	 *
	 * @param regexp A regular expression used in replacement.
	 * @param replacement The replacement string to use with the regexp.
	 */
	function set_modifier($regexp, $replacement) {
		$this->mod_regexps[] = $regexp;
		$this->mod_replacements[] = $replacement;
	}

	/**
	 * Executes or schedules execution on any modifires on the buffer.
	 *
	 * @param onoutput Run modifiers on output (not now) (optional).
	 */
	function run_modifiers($onoutput = false) {

		//User is requesting modifiers be run during output.
		if($onoutput) {
			$this->mod_run = true;
		}

		//User wants this run now.
		else {
			$this->buffer = preg_replace($this->mod_regexps,
				$this->mod_replacements, $this->buffer);
		}
	}

	/**
	 * Clears out all the current modifiers.
	 */
	function clear_modifiers() {
		$this->mod_regexps = array();
		$this->mod_replacements = array();
		$this->mod_run = false;
	}


/****************************************************************************
 * Method registry and internal buffer operations.
 ***************************************************************************/

	/**
	 * Initializes feature methods in the method registry for use when
	 * the user application requests the final document.
	 */
	function register_methods() {
		$this->method_registry = array(
			1 => 'private_htmlenchars',
			2 => 'private_htmlspchcars',
			4 => 'private_nl2br',
			8 => 'private_hyperlinks',
			16 => 'private_bbcode',
			32 => 'private_run_modifiers',
			64 => 'private_textformat',
			128 => 'private_allowsimple',
			256 => 'private_document',
			4096 => 'private_newsys',
			8192 => '' //Unused
		);
	}

	/* @see htmlenchars */
	function private_htmlenchars() {
		$this->buffer = $this->htmlenchars($this->buffer);
	}
	/* @see htmlspchcars */
	function private_htmlspchcars() {
		$this->buffer = $this->htmlspchcars($this->buffer);
	}
	/* @see nl2br */
	function private_nl2br() {
		$this->buffer = $this->nl2br($this->buffer);
	}
	/* @see hyperlinks */
	function private_hyperlinks() {
		$this->buffer = $this->hyperlinks($this->buffer);
	}
	/* @see run_modifiers */
	function private_run_modifiers() {
		$this->run_modifiers(true);
	}
	/* @see textformat */
	function private_textformat() {
		$this->buffer = $this->textformat($this->buffer);
	}
	/* @see allowsimple */
	function private_allowsimple() {
		$this->buffer = $this->allowsimple($this->buffer);
	}
	/* @see get_document */
	function private_get_document() {
		$this->buffer = $this->get_document($this->buffer);
	}
}
?>