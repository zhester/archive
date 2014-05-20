<?
/****************************************************************************
	Sequential Web Media Downloader
	Zac Hester - 2004-12-27

	This is a little object that can download a lot of media files (music,
	images, documents, whatever) from a web site where the files have been
	named using some kind of sequential scheme (like numbering the files) to
	the local file system.  Files can then be transferred wherever using a
	local protocol (such as FTP).

	The really really horrible part of this object is that it relies on
	operating only on the local filesystem with the same credentials as the
	web server.  Future versions may implement an FTP layer to allow the
	script to read and write files under a given user's credentials.  For
	now, this means the web server user needs to be able to write to the
	directory to which $target points.

	more to come...

	URL expansion syntax:
	http://example.com/dir/file0.png -> http://example.com/dir/file9.png
	  http://example.com/dir/file[0,9].png
	Or, simply, tell it where to start:
	http://example.com/dir/file0.png
	  http://example.com/dir/file[0,].png
	Or, more simply, tell it the same information and it will make a guess:
	http://example.com/dir/file0.png
	  http://example.com/dir/file0.png
****************************************************************************/

//Shortcut function interface for basic use.
function dlwebmedia($subject, $target = '.') {
	$wm = new webmedia($subject, $target);
	return($wm->loadmedia());
}

class webmedia {

	var $subject;
	var $target;
	var $limit = 25;	//Number of attempts before failing.

	var $scheme;
	var $baseurl;
	var $filename;

	/**
	 * Constructor
	 */
	function webmedia($subject, $target = '.') {

		//Set up the class.
		$this->subject = $subject;
		if($target == '.') {
			$this->target = dirname($_SERVER['SCRIPT_FILENAME']);
		}
		else { $this->target = $target; }

		//Set up URL information.
		$bits = explode('/', $subject);
		if($bits[0] == 'http:' || $bits[0] == 'https:') {
			$this->scheme = $bits[0];
			array_shift($bits); array_shift($bits);
		}
		else {
			$this->scheme = 'http:';
		}
		$this->filename = array_pop($bits);
		$this->baseurl = implode('/', $bits);
	}

	/**
	 * Initializes the transfer.
	 */
	function loadmedia() {

		//Download statistics.
		$stats = array();

		//Check URL for expansions.
		if(strstr($this->filename, '[') === false) {
			$filepattern = $this->get_expansionpattern($this->filename);
		}
		else {
			$filepattern = $this->filename;
		}

		//Rip apart the expansion pattern.
		if(preg_match('/^([^\[]*)\[([^\]]+)\]([^\.]*)\.(.*)$/',
			$filepattern, $m)) {

			//Set up ranges.
			$bits = explode(',', $m[2]);
			$initpad = strlen($bits[0]);
			if($bits[1]) { $stop = intval($bits[1]); }
			else { $stop = 65535; }

			//Begin download loop.
			for($i = intval($bits[0]), $failures = 0; $i < $stop; ++$i) {

				//Build next sequental item number.
				if(strlen($i) < $initpad) {
					$nextnum = sprintf('%0'.$initpad.'d', $i);
				}
				else { $nextnum = $i; }

				//Attempt to download the file.
				$stat = $this->get_file($m[1].$nextnum.$m[3].'.'.$m[4]);

				//Append to stats array.
				$stats[] = $stat;

				//Check for failure to download.
				if($stat['size']) { $failures = 0; }
				else { ++$failures; }

				//Implicit stopping point.
				if(!$bits[1] && ($failures > $this->limit)) { break; }
			}
		}

		else { echo("BAD PATTERN ($filpattern)\n"); }

		//Send back the statistics of our operation.
		return($stats);
	}


	/**
	 * Downloads a file to the specified directory.
	 */
	function get_file($filename) {

		//Set the complete local file name.
		$localfile = $this->target.'/'.$filename;

		//Build URL.
		$url = $this->scheme.'//'.$this->baseurl.'/'.$filename;

		//Use our HTTP client front-end to grab the file.
		$status = http_copy($url, $localfile);

		//Check our destination file.
		if(file_exists($localfile)) {

			//Send back the results of the attempted download.
			return(array(
				'filename' => $filename,
				'status' => $status,
				'path' => $localfile,
				'size' => filesize($localfile)
			));
		}
		return(array('filename' => $filename, 'status' => $status));
	}


	/**
	 * Creates a file expansion pattern from a normal file name.
	 */
	function get_expansionpattern($subject) {
		$exppatt = '';
		//Look for query-style URLs.
		//if(strstr($subject, '?') !== false) {
			//QUERY STUFF
		//}
		//This is a normal, file name.
		//else {
			//Find the LAST solid numeric sequence.
			if(preg_match('/(\D*)(\d+)(\D*)\.([A-Za-z]{1,5})$/',
				$subject, $m)) {
				//Construct the expansion string.
				$exppatt = $m[1].'['.$m[2].',]'.$m[3].'.'.$m[4];
			}
		//}
		return($exppatt);
	}
}

/**
 * Front-ends the HTTP client class.
 */
function http_copy($remotefile, $localfile) {

	//Set up the HTTP client.
	$hc = new http_client($remotefile);

	//Check connection.
	if($hc->error) {
		$bits = parse_url($remotefile);
		return('Unable to connect to host ('.$bits['host'].'): '.$hc->error);
	}

	//Set referer (this usually works).
	$hc->set_header('Referer', $remotefile);

	//Copy file and send back the copy status.
	return($hc->copyto($localfile));
}


/**
 * HTTP Client
 * Zac Hester - 2004-12-27
 * Version 1.0 - 2004-12-28
 *
 * This is a very simple HTTP client that operates on raw sockets instead of
 * of the PHP "fopen_url_wrappers."  This allows us to use more advanced,
 * run-time configuration to open remote files.  Basically, the biggest
 * advantage and my reason for doing this is to be able to set the HTTP
 * Referer header.
 *
 * Future modifications may make this more versatile.  Ideas would be to
 * implement POST requests and HTTP authentication.  I should also spend some
 * time reading the HTTP specs.  It would also be nice to figure out how to
 * handle Cookies (in case we need access to PHP's sessions).
 *
 * This class was inspired by a similar piece of PHP code written by
 * Kai Blankenhorn <kaib@bitfolge.de>
 * Kai's class simply opened the socket and allowed a PHP programmer to
 * use a remote file over HTTP like any other file handle.  This class was
 * built from the ground up using that idea as a starting point.
 */
class http_client {

	var $conn;		//The socket handle of the connection.
	var $error;		//The last error generated by the client.
	var $replies;	//The reply headers.
	var $url;		//The target URL.
	var $urlbits;	//The parsed URL.
	var $headers;	//The list of headers to send.
	var $request;	//The last request.
	var $status;	//The response status.
	var $response;	//The HTTP response message.

	/**
	 * Constructor.
	 */
	function http_client($url) {
		$this->set_url($url);
	}

	/**
	 * Sets the URL to something new.
	 * Sets the target URL of whatever request we will be issuing.
	 * The URL just has to be understandable to PHP's parse_url() function.
	 */
	function set_url($url) {

		//Reset instance data.
		$this->conn = false;
		$this->error = '';
		$this->replies = array();
		$this->headers = array();
		$this->request = '';
		$this->status = '';
		$this->response = '';

		//Set the new URL.
		$this->url = $url;

		//Parse the URL.
		$this->urlbits = parse_url($url);

		//Default to standard web port.
		if(!$this->urlbits['port']) { $this->urlbits['port'] = 80; }
	}

	/**
	 * Sets an HTTP header.
	 */
	function set_header($key, $value) {
		$this->headers[$key] = $value;
	}

	/**
	 * Simply copies the file specified in the URL to a file specified by the
	 * argument.
	 */
	function copyto($localfile) {

		//A return message.
		$message = '';

		//Open local file.
		$fh = fopen($localfile, 'wb');

		//Check file.
		if($fh) {

			//Open connection to HTTP host.
			if($this->open()) {

				//Reset some instance data.
				$this->replies = array();
				$this->status = '';
				$this->response = '';
				$this->error = '';

				//Send HTTP headers for request.
				$this->send_headers();

				//Check for host response.
				if($this->conn) {

					//Pull off first reply.
					$this->response = trim(fgets($this->conn, 1024));

					//Store reply.
					$this->replies[] = $this->response;

					//Grab HTTP response status.
					$this->status = substr($this->response, 9, 3);

					//Read the rest of the response headers.
					while(($reply = trim(fgets($this->conn, 1024))) != '') {
						$this->replies[] = $reply;
					}

					//Make sure the HTTP host agrees with our request.
					if($this->status == '200') {

						//Dump remote file into local file.
						while(!feof($this->conn)) {
							$block = fread($this->conn, 4096);
							fwrite($fh, $block, strlen($block));
						}

						//Everything worked.
						$message = '';
					}

					//Unhandled response.
					else {

						//Close our file and delete it.
						fclose($fh); $fh = false;
						unlink($localfile);

						//Just set the response message for now.
						$message = $this->response;
					}
				}

				//Close everything.
				if($fh) { fclose($fh); } $this->close();

				//The client is working, but something else may be wrong.
				return($message);
			}

			//We couldn't connect to the remote host.
			fclose($fh);
			return('Unable to connect to remote host ('.$this->urlbits['host'].').');
		}

		//We couldn't open the specified target file.
		return('Unable to write to local file ('.$localfile.').');
	}

	/**
	 * Opens the socket to the remote host.
	 */
	function open() {

		//Open a socket to the remote host.
		$this->conn = fsockopen($this->urlbits['host'],
			$this->urlbits['port'], $errno, $errstr, 15);

		//Test the connection.
		if(!$this->conn) {

			//Failed connection.
			$this->error = $errstr; return(false);
		}

		//Successful connection.
		return(true);
	}

	/**
	 * Closes the socket.
	 */
	function close() {
		fclose($this->conn);
	}

	/**
	 * Sends all the appropriate HTTP headers over the connection.
	 */
	function send_headers() {
		if($this->conn) {

			//Set the request.
			$this->request = 'GET '.$this->urlbits['path'].' HTTP/1.0';

			//Send the request (the first "header").
			fputs($this->conn, $this->request."\r\n");

			//Make sure to send a host header.
			if(!$this->headers['Host']) {
				$this->set_header('Host', $this->urlbits['host']);
			}

			//Make sure to send a user agent string.
			if(!$this->headers['User-Agent']) {
				//This UA is just some propoganda and an attempt to further
				// sway web usage statistics away from IE.
				$this->set_header('User-Agent', 'Mozilla/5.0 (X11; U; FreeBSD'
					.' i386; rv:1.7.3) Gecko/20041101 Firefox/0.10.1');
			}

			//Send all other headers.
			foreach($this->headers as $k => $v) {
				fputs($this->conn, "$k: $v\r\n");
			}

			//Finished sending headers.
			fputs($this->conn, "\r\n");
			return(true);
		}
		return(false);
	}
}

//TESTING
//dump_headers('http://planetzac.net/pz/cn/index.html');
function dump_headers($url) {
	$hc = new http_client($url);
	if($hc->error) {
		$bits = parse_url($url);
		return('Unable to connect to host '.$bits['host']);
	}
	$hc->set_header('Referer', $url);
	$error = $hc->copyto('/tmp/HTTPCLIENTTEST');

	//Debug output.
	header('Content-Type: text/plain');
	echo "URL: $url\n";
	echo 'Request: '.$hc->request."\n";
	echo 'Response: '.$hc->response."\n";
	echo 'Response Status: '.$hc->status."\n";
	echo "Headers Sent:\n"; print_r($hc->headers);
	echo "Headers Received:\n"; print_r($hc->replies);
	echo "Client errors encountered: $error\n";
	exit();
}

/*
SOME RANDOM HTTP PROTOCOL DOCUMENTATION

Request Format-> REQUEST_TYPE PATH PROTOCOL\r\n
  GET / HTTP/1.0\r\n
  POST /process.php HTTP/1.0\r\n
  HEAD / HTTP/1.0\r\n
Header Format-> HEADERNAME: HEADERVALUE\r\n
HTTP Authentication-> Authorization: Basic <?=base64_encode(USER:PASS)?>
HTTP Referer-> Referer: PREVIOUS_URL
Important headers-> Host,Accept,User-Agent
Useful headers-> Referer,Authorization

POST request format->
  #ALL REGULAR HEADERS#
  <?POSTDATA='key=value&key2=value2'?>
  Content-Length: <?=strlen(POSTDATA)?>\r\n
  \r\n
  <?=POSTDATA?>\r\n
(POST data is sent in the body of the request.)

Like most protocols, the head and body are separated by two newlines.
From the client's point of view, the request is over once the header is sent
(except for POST requests).
From the server's point of view, the response header is sent in the header
and the response data (the file) is sent in the body.  Of course, you won't
get a body in the response if a control header (redirect, auth request) is
sent.

Important Response Headers:
The first header is the response message-> HTTP/1.1 200 OK
Apache also gives us the following (a typical response)->
	Date: Tue, 28 Dec 2004 18:46:33 GMT
	Server: Apache/2.0.50 (Unix) PHP/5.0.1
	X-Powered-By: PHP/5.0.1
	Last-Modified: Tue, 28 Dec 2004 18:46:33 GMT
	Content-Length: 2089
	Connection: close
	Content-Type: text/html;charset=ISO-8859-1

Authentication Request (returns a 401 status)->
	WWW-Authenticate: Basic realm="HTTP AuthName"
Redirect (returns a 302 status)->
	Location: NEW_URL
*/
?>