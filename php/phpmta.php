<?php
/**
 * PHP MTA
 *
 * This function operates as a decent stand-in for PHP's mail() function.
 * For 90% of users of mail(), this should operate identically.  The reason
 * to use an MTA script is to subvert a hosting provider's lockdown on
 * mail(), or if they try to intercept the MTA process and "help" you send
 * your message.  (This situation usually involves "safe mode," so using
 * sendmail through a shell may not be possible, either.)
 * The idea for this comes from iain@monitormedia.co.uk (on php.net).
 *
 * Change Log:
 *   2007-05-23
 *     - Fixed a small bug that added an unneeded carriage return when
 *       the "From:" header was used without additional headers.
 *   2005-09-20
 *     - First working version.
 *
 * @author Zac Hester <zac@zacharyhester.com>
 * @date 2005-09-20 - 2007-05-23
 *
 * @param to The email recipient (address only).
 * @param subject The message subject.
 * @param message The message body.
 * @param headers Additional message headers.
 */
$phpmta_message = '';
function phpmta($to, $subject, $message, $headers) {
	global $phpmta_message;

	//Set host name.
	$hostname = $_SERVER['HTTP_HOST'];

	//Check headers for a "from"
	if(preg_match('/From\: ([^\n]+)(\n|$)/', $headers, $m)) {
		$from = trim($m[1]);
		$headers = trim(preg_replace('/From\: ([^\n]+)(\n|$)/', '', $headers));
	}

	//Default to a good guess.
	else {
		$from = $_SERVER['SERVER_ADMIN'];
	}

	//Build the message.
	if($headers) {
		$data = "To: $to\r\nFrom: $from\r\nSubject: $subject\r\n$headers"
			."\r\n\r\n$message\r\n.\r\n";
	}
	else {
		$data = "To: $to\r\nFrom: $from\r\nSubject: $subject"
			."\r\n\r\n$message\r\n.\r\n";
	}

	//Check for an "extravagant" from field.
	if(preg_match('/[^<]+(<[^>]+>)/', $from, $m)) {
		$from = trim($m[1]);
	}

	//Search for MX records.
	list($user, $domain) = explode('@', $to, 2);
	if(getmxrr($domain, $mx, $weight) === false) { return(false); }

	//Sort by weight (low -> hi).
	array_multisort($mx, $weight);
	$success = false;

	//Run through each MX until we're able to send the message.
	foreach($mx as $host) {

		//Open an SMTP connection
		$c = fsockopen($host, 25, $errno, $errstr, 30);

		//If the connection fails, go to next MX.
		if(!$c) { continue; }

		//Look for "Service Ready" or go to next MX.
		$res = fgets($c, 256);
		if(substr($res, 0, 3) != '220') {
			socket_smtp_close($c);
			$phpmta_message = 'Connection Response: '.$res;
			continue;
		}

		//Introduce ourselves and check for "Action Okay."
		fputs($c, "HELO $hostname\n");
		$res = fgets($c, 256);
		if(substr($res, 0, 3) != '250') {
			socket_smtp_close($c);
			$phpmta_message = 'HELO Response: '.$res;
			continue;
		}

		//Set envelope from and check for "Action Okay."
		fputs($c, "MAIL FROM: $from\n");
		$res = fgets($c, 256);
		if(substr($res, 0, 3) != '250') {
			socket_smtp_close($c);
			$phpmta_message = 'MAIL Response: '.$res;
			continue;
		}

		//Set envelope to and check for "Action Okay."
		fputs($c, "RCPT TO: <$to>\n");
		$res = fgets($c, 256);
		if(substr($res, 0, 3) != '250') {
			socket_smtp_close($c);
			$phpmta_message = 'RCPT Response: '.$res;
			continue;
		}

		//Prepare to send the message and check for a go-ahead.
		fputs($c, "DATA\n");
		$res = fgets($c, 256);
		if(substr($res, 0, 3) != '354') {
			socket_smtp_close($c);
			$phpmta_message = 'DATA Response: '.$res;
			continue;
		}

		//Send message data and make sure it went through.
		fputs($c, $data);
		$res = fgets($c, 256);
		if(substr($res, 0, 3) != '250') {
			socket_smtp_close($c);
			$phpmta_message = 'Message Response: '.$res;
			continue;
		}

		//End session.
		fputs($c, "QUIT\n");
		//response should be 221, but we don't care if it worked otherwise.

		//Mail sent successfully (no need to try other MXs).
		fclose($c);
		return(true);
	}

	//If we make it here, none of the MXs worked.
	return(false);
}

//Requests a formal close, then closes the socket.
function socket_smtp_close(&$handle) {
	fputs($handle, "QUIT\n");
	fclose($handle);
	$handle = false;
}
?>