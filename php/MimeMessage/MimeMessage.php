<?php
/****************************************************************************
	MimeMessage
	Zac Hester
	2009-01-22
	Version 1.0.0

	This class provides a well-abstracted MIME format message parser.
	I found several MIME parser classes with terrible interfaces.  This
	provides a powerful and more simplified interface that actually
	uses OOP methodology (not just a pile of associative arrays).

	I've read through RFC2822 (http://www.faqs.org/rfcs/rfc2822.html)
	and this class should decode a modern, compliant message.  With
	the possible variations in message generators, I can't be sure this
	is a one-size-fits-all solution, and it, honestly, isn't my intention
	to make something that universal.  I will continue to tweak and adjust
	the implementation as I see more message formats that break the current
	(reasonably robust) parsing methods.

	Known non-conformance with RFC2822:
		-- Header data fields:
		- Nested '(' or ')' inside comments will break comment stripping
		- Allows nonstandard line endings (for compatibility)
		- Obsolete forms may impact parsing (untested)

	Usage:
	$email = file_get_contents('my_message.eml');
	$mm = new MimeMessage($email);
	$from = $mm->getHeader('From')->data;
	if($mm->hasParts()) {
		$num_parts = $mm->getPartCount();
		for($i = 0; $i < $num_parts; ++$i) {
			$p = $mm->getPart($i);
			$part_type = $p->getHeader('Content-Type')->getMeta('type');
		}
	}
	//Dump parsed info:
	print_r($mm->dumpArray());
****************************************************************************/

/**
 * MimeMessageHeader
 * Deals with the subtleties of various message and part headers.
 */
class MimeMessageHeader {

	//Header metadata (extra descriptors and refined parsing)
	protected $meta = array();

	//Header name field
	protected $name;

	//Header data field (unparsed)
	protected $data;


	/**
	 * Constructor.
	 * Creates a new message header object.
	 *
	 * @param name The value of the header name field
	 * @param data The value of the header data field
	 */
	public function __construct($name, $data) {
		$this->name = $name;
		$this->data = trim($data);
	}


	/**
	 * append
	 * Appends more data to this header (from whitespace folding).
	 *
	 * @param data An extra line of data to append to the data field
	 * @return true on success
	 */
	public function append($data) {
		$this->data .= preg_replace('/^\s+/', ' ', $data);
		return(true);
	}


	/**
	 * finalize
	 * Called when header data is complete.  This parses header data
	 * fields a little more and needs to be done after all additional
	 * data lines (from MimeMessageHeader::append()) have been appended.
	 *
	 */
	public function finalize() {

		//Strip comments.
		$data = preg_replace('/\([^)]*\)/', '', $this->data);

		//Special fields.
		$address_headers = array('To','Cc','Bcc','Reply-To');

		//Complex headers.
		if(strpos($data, ';') !== false) {

			//Begin tokenized processing.
			$nflags = 0;
			$chunk = trim(strtok($data, ';'));
			if(strpos($chunk, '=') === false) {
				$this->meta['type'] = $chunk;
				if(strpos($chunk, '/') !== false) {
					list($generic, $specific) = explode('/', $chunk, 2);
					$this->meta['genus'] = $generic;
					$this->meta['species'] = $specific;
				}
				$chunk = strtok(';');
			}

			//The first one wasn't special, reset tokenizer.
			else {
				$chunk = strtok($data, ';');
			}

			//Scan all later chunks.
			while($chunk !== false) {
				$chunk = trim($chunk);

				//Might be field pair (key=value).
				if(strpos($chunk, '=') !== false) {
					list($k, $v) = explode('=', $chunk, 2);
					if(preg_match('/^".*"$/', $v)) {
						$this->meta[$k] = trim($v, '"');
						$this->meta[$k] = preg_replace(
							'/\\"/', '"', $this->meta[$k]
						);
					}
					else {
						$this->meta[$k] = $v;
					}
				}

				//Flag-style chunk.
				else if(strlen($chunk)) {
					$this->meta[$nflags] = $chunk;
					++$nflags;
				}

				//Advance to next token.
				$chunk = strtok(';');
			}
		}

		//Date header.
		else if($this->name == 'Date') {
			$this->meta['timestamp'] = strtotime($data);
		}

		//Some headers may contain multiple addresses separated by ","
		else if(in_array($this->name, $address_headers)) {
			if(strpos($data, ',') !== false) {
				$addresses = explode(',', $data);
				array_walk($addresses, 'trim');
				$this->meta['addresses'] = $addresses;
			}
		}
	}


	/**
	 * __toString
	 * Returns the header's raw string value.
	 *
	 * @return The complete header data field (without parsing).
	 */
	public function __toString() {
		return($this->data);
	}


	/**
	 * __get
	 * Accesses several common properties for the header.
	 *
	 * @param key The key given to us from the magic method invocation
	 * @return The value of the specified property
	 */
	public function __get($key) {
		switch($key) {
			case 'name': case 'key':
				return($this->name);
			break;
			case 'data': case 'value':
				return($this->data);
			break;
			case 'meta':
				return($this->meta);
			break;
		}
		return(false);
	}


	/**
	 * getMeta
	 * Returns a metadata field value or the list of metadata.  If no field
	 * is specified with the key parameter, the entire list of metadata is
	 * returned.
	 *
	 * @param key An optional metadata field specifier
	 * @return A metadata value or the list of metadata
	 */
	public function getMeta($key = false) {
		if($key && isset($this->meta[$key])) {
			return($this->meta[$key]);
		}
		else if($key === false) {
			return($this->meta);
		}
		return(false);
	}


	/**
	 * dumpArray
	 * Dumps an array of info for diagnostics.
	 *
	 * @return A tree structure representing this header's parsed info
	 */
	public function dumpArray() {
		$array = array(
			'name' => $this->name,
			'data' => $this->data
		);
		if(count($this->meta)) {
			$array['meta'] = $this->meta;
		}
		return($array);
	}
}


/**
 * MimeMessagePart
 * Provides storage and specific methods for a MIME message part.
 *
 */
class MimeMessagePart {

	//Raw message data.
	protected $data;

	//Non-MIME metadata.
	protected $meta = array();

	//Part headers.
	protected $headers = array();

	//Raw content body (normal message for terminal parts).
	protected $body;

	//Component parts (if this isn't a terminal part).
	protected $parts = array();

	//Detected line-ending (for compatibility).
	private $nl = false;

	//Helps continued headers.
	private $parse_last_header = '';


	/**
	 * Constructor.
	 * Sets up and begins parsing the message data.
	 *
	 * @param data The entire contents of the message or part.
	 */
	public function __construct($data) {
		$this->data = $data;
		$this->parse();
	}


	/**
	 * getMeta
	 * Tells the user derived information about the message part.
	 * Returns a metadata field value or the list of metadata.  If no field
	 * is specified with the key parameter, the entire list of metadata is
	 * returned.
	 *
	 * @param key An optional metadata field specifier
	 * @return A metadata value or the list of metadata
	 */
	public function getMeta($key = false) {
		if($key && isset($this->meta[$key])) {
			return($this->meta[$key]);
		}
		else if($key === false) {
			return($this->meta);
		}
		return(false);
	}


	/**
	 * getHeader
	 * Returns a header object (or list of header objects) to the user.
	 *
	 * @param label The header name field/label
	 * @return A MimeMessageHeader object representing this header
	 */
	public function getHeader($label = false) {
		if($label && isset($this->headers[$label])) {
			return($this->headers[$label]);
		}
		else if($label === false) {
			return($this->headers);
		}
		return(false);
	}


	/**
	 * isTerminal
	 * Indicates if this message part contains no other message parts.
	 *
	 * @return true if this part contains no other parts
	 */
	public function isTerminal() {
		return(!$this->hasParts());
	}


	/**
	 * getContent
	 * Returns the message content.
	 *
	 * @return The unparsed contents of this message or part
	 */
	public function getContent() {
		$type = $this->getHeader('Content-Type');
		if($type && $type->getMeta('type') == 'text/html') {
			return(preg_replace(
					'/=3D/',
					'=',
					preg_replace(
						'/=\r?\n/',
						'',
						$this->body
					)
				)
			);
		}
		return($this->body);
	}


	/**
	 * hasParts
	 * Indicates if this message or part contains other parts.
	 *
	 * @return true if this consists of multiple parts, false otherwise
	 */
	public function hasParts() {
		if(count($this->parts) > 0) {
			return(true);
		}
		return(false);
	}


	/**
	 * getPartCount
	 * Returns the number of message parts within this message or part.
	 *
	 * @return The number of message parts, 0 if none
	 */
	public function getPartCount() {
		return(count($this->parts));
	}


	/**
	 * getPart
	 * Retrieves a message part by index within the current message.
	 *
	 * @param index The index of a part within the current part
	 * @return A MimeMessagePart object representing this message part
	 */
	public function getPart($index) {
		if(isset($this->parts[$index])) {
			return($this->parts[$index]);
		}
		return(false);
	}


	/**
	 * getEncodedFiles
	 * As this class is extremely useful for extracting file attachments,
	 * and otherwise encoded chunks of a message, this function builds a
	 * list of all the parts encoded in the given format (base64 by default).
	 *
	 * @param encoding The encoding method of the desired message parts
	 * @return A list of MimeMessagePart objects representing all message
	 *    parts that are encoded as specified
	 */
	public function getEncodedFiles($encoding = 'base64') {
		return(
			$this->getPartsByHeader(
				'Content-Transfer-Encoding',
				$encoding
			)
		);
	}


	/**
	 * getPartsByHeader
	 * Allows users to query for message parts based on the value of
	 * any of their headers.
	 *
	 * @param name The header name
	 * @param value The header value
	 * @return A list of MimeMessagePart objects representing the match
	 * 	  matched parts, or false if none are found to match
	 */
	public function getPartsByHeader($name, $value) {

		//If not a terminal part, scan component parts.
		if($this->hasParts()) {
			$test = false;
			$files = array();
			$nparts = $this->getPartCount();
			for($i = 0; $i < $nparts; ++$i) {
				$test = $this->getPart($i)->getPartsByHeader($name, $value);
				if(is_array($test)) {
					$files = array_merge($files, $test);
				}
			}
			return($files);
		}

		//This is a terminal part, check for the header.
		else if($this->getHeader($name) == $value) {
			return(array($this));
		}

		//No parts match this encoding.
		return(false);
	}


	/**
	 * dumpArray
	 * Diagnostic tree output.
	 *
	 * @return A tree structure representing all the parsed information
	 */
	public function dumpArray() {
		$array = array(
			'meta' => $this->meta,
			'headers' => array()
		);
		foreach($this->headers as $k => $v) {
			if(is_array($v)) {
				foreach($v as $v1) {
					$array['headers'][$k][] = $v1->dumpArray();
				}
			}
			else {
				$array['headers'][$k] = $v->dumpArray();
			}
		}
		if($this->hasParts()) {
			$array['parts'] = array();
			$n = $this->getPartCount();
			for($i = 0; $i < $n; ++$i) {
				$p = $this->getPart($i);
				$array['parts'][] = $p->dumpArray();
			}
		}
		else {
			$array['body'] = $this->getContent();
		}
		return($array);
	}

	/*--------------------------------------------------------------------*/

	/**
	 * parse
	 * Parses the message part headers and content.
	 *
	 */
	protected function parse() {

		//Separate header and body (account for crazy people).
		if(strpos($this->data, "\r\n\r\n") !== false) {
			list($head, $body) = explode("\r\n\r\n", $this->data, 2);
		}
		else if(strpos($this->data, "\n\n") !== false) {
			list($head, $body) = explode("\n\n", $this->data, 2);
		}
		else if(strpos($this->data, "\r\r") !== false) {
			list($head, $body) = explode("\r\r", $this->data, 2);
		}
		else {
			$head = '';
			$body = $this->data;
		}

		//Parse and store all current headers.
		$this->parseHeaders($head);

		//Check for multipart message.
		$type = $this->getHeader('Content-Type');
		if($type && $type->getMeta('genus') == 'multipart') {

			//Set the label string.
			$label = '--'.$type->getMeta('boundary');

			//Remove the first label from this part.
			$part = substr($body, strlen($label));

			//Run through each section.
			while(($offset = strpos($part, $label)) !== false) {

				//Get everything up to the next label.
				$current = substr($part, 0, $offset);

				//Create a new message part with this information.
				$this->parts[] = new MimeMessagePart($current);

				//Remove this message part.
				$part = substr($part, $offset+strlen($label));

				//Check for closing label.
				if(substr($part,0,2) == '--') { break; }
			}
		}

		//This is a terminal message part.
		else {
			$this->body = $body;
		}
	}


	/**
	 * parseHeaders
	 * Parses the header section of a message or part and stores the info
	 * in the headers list.
	 *
	 * @param head The message before the body delimiter
	 */
	protected function parseHeaders($head) {

		//Detect line ending for compatibility (non-standard).
		if($this->nl == false) {
			if(substr($head, strpos($head,"\n")-1, 1) != "\r") {
				$this->nl = "\n";
			}
			else {
				$this->nl = "\r\n";
			}
		}

		//Line-based parsing.
		$hlines = explode($this->nl, $head);
		foreach($hlines as $line) {

			//Message delimiter.
			if(preg_match('/^From /', $line)) {
				$this->meta['delimiter'] = $line;
			}

			//Continued header.
			else if(preg_match('/^\s/', $line)) {
				$this->appendHeader($line);
			}

			//Normal header or beginning of header.
			else if(strpos($line, ':') !== false) {
				list($name, $data) = explode(':', $line, 2);
				$this->setHeader($name, $data);
			}
		}

		//Send a signal to indicate we are finished extracting headers.
		$this->finishHeaders();
	}


	/**
	 * setHeader
	 * Attempts to intelligently add new headers to the headers list.
	 *
	 * @param name The header name field value
	 * @param data The header data field value
	 */
	protected function setHeader($name, $data) {

		//List of current headers.
		$hkeys = array_keys($this->headers);

		//Minor amount of header field name normalization.
		$hkey = trim($name);

		//Repeated header.
		if(in_array($hkey, $hkeys)) {

			//Already a list of repeated headers.
			if(is_array($this->headers[$hkey])) {
				$this->headers[$hkey][] = new MimeMessageHeader($hkey,$data);
			}

			//Convert to list of repeated headers.
			else {
				$buffer = $this->headers[$hkey];
				$this->headers[$hkey] = array(
					$buffer,
					new MimeMessageHeader($hkey,$data)
				);
			}
		}

		//Normal header.
		else {
			$this->headers[$hkey] = new MimeMessageHeader($hkey,$data);
		}

		//Remember the last header in case of continuation.
		$this->parse_last_header = $hkey;
	}


	/**
	 * appendHeader
	 * Appends data to the "last" header.  Useful when single header data
	 * fields span multiple lines.
	 *
	 * @param data The additional header data field information
	 */
	protected function appendHeader($data) {

		//Shortcut, shhh.
		$k = $this->parse_last_header;

		//If it's a repeated header, append to the last one.
		if(is_array($this->headers[$k])) {
			$this->headers[$k][count($this->headers[$k])-1]->append($data);
		}

		//Regular header.
		else {
			$this->headers[$k]->append($data);
		}
	}


	/**
	 * finishHeaders
	 * Informs each header that it is complete.
	 *
	 */
	protected function finishHeaders() {
		foreach($this->headers as $header) {
			if(is_array($header)) {
				foreach($header as $subheader) {
					$subheader->finalize();
				}
			}
			else {
				$header->finalize();
			}
		}
	}
}


/**
 * MimeMessage
 * The root user interface for MIME formatted messages.
 */
class MimeMessage extends MimeMessagePart {


	/**
	 * Constructor.
	 *
	 * @param message The entire RFC2822 formatted message.
	 */
	public function __construct($message) {
		parent::__construct($message);
	}


	/**
	 * __toString
	 * Makes some assumptions about what the user would want when asking
	 * for just a string representation of the message.  It pretty much
	 * always returns the plain text message body (which can depend on
	 * the original construction of the message).
	 *
	 * @return A string representation of the message
	 */
	public function __toString() {

		//Look for multipart message.
		if($this->hasParts()) {

			//Grab all plain text parts.
			$parts = $this->getPartsByHeader('Content-Type', 'text/plain');

			//Make sure the message has a plain text part.
			if(is_array($parts) && count($parts)) {

				//Send back the first plain text part.
				return($parts[0]->getContent());
			}

			//No plain text parts, send back the first part.
			return($this->getPart(0)->getContent());
		}

		//Simple message, send back the body.
		return($this->getContent());
	}
}

?>