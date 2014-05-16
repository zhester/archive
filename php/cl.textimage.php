<?php
/****************************************************************************
	TextImage Verification Utilities
	Zac Hester
	2007-05-23
	1.0.0

	This is my first stab at making something for verifying human-entered
	forms using an image with some text.

	Dependencies:
		PHP 4.3+
		GD2+
		session_start()

	Usage:
		Because this solution is integrated between three requests, it can
		be somewhat complex to implement this effectively.  The basic model
		is this:
			1. Implement an image request script like:
				session_start();
				require('cl.textimage.php');
				$ti = new TextImage();
				$ti->sendImage();
				exit();
			2. Add a form field to your form to accept the user's input
				and display the generated image somewhere near there.
				I would also recommend running "resetText()" somewhere
				above your form so people can regenerate the string if
				they get a confusing one.
			3. Add a check in the form processing code that checks their
				input using "verifyText()."  After verifying a proper
				entry, be sure to call "resetText()" again to prevent an
				automated script from using the same string to submit
				multiple forms (since those scripts typically don't even
				request your form).

	Change Log:
		2007-05-23, 1.0.0:
			- First tested/working version.
****************************************************************************/


class TextImage {

	//Stores the key used to read/write to the session
	var $key;

	//The number of characters used in the random string
	var $length;

	//The random string
	var $text;

	//A list of valid characters used when generating the string
	var $charlist;


	/**
	 * TextImage
	 * The constructor.
	 *
	 * @param key The key used for the session
	 */
	function TextImage($key = 'TextImage') {

		//Set the key.
		$this->key = $key;

		//Set the length of the string.
		$this->length = 6;

		//Generate the list of valid characters.
		$this->generateCharList();

		//Check for existing string.
		if(!isset($_SESSION[$this->key])) {

			//Generate a new one and save it.
			$this->resetText();
		}

		//A string is already available.
		else {
			$this->text = $_SESSION[$this->key];
		}
	}


	/**
	 * resetText
	 * Resets the text string used to generate and validate.  Call this when
	 * you want a string to "expire" so it can't be used for multiple form
	 * entries.
	 *
	 * @return The new string that was generated
	 */
	function resetText() {

		//Generate a new string.
		$this->text = $this->generateText();

		//Save it.
		$_SESSION[$this->key] = $this->text;

		//Return it.
		return($this->text);
	}


	/**
	 * verifyText
	 * Verifies a provided string against the stored string.  Usually, the
	 * value of a form field is passed to this function to ensure the user
	 * correctly entered the characters.
	 *
	 * @param text The user-supplied verification string
	 * @return True if it matches, otherwise false
	 */
	function verifyText($text) {

		//Make sure this is available in the session.
		if(isset($_SESSION[$this->key])) {

			//Test the server-side string against the submitted string.
			return(
				$_SESSION[$this->key] == $text
			);
		}
		return(false);
	}


	/**
	 * sendImage
	 * Directly outputs the binary image data to display the string as
	 * a PNG image.  You can't use this inside of a regular page, but
	 * as the sole output of a separate request.
	 *
	 */
	function sendImage() {

		//Test image stream
		$ih = imagecreatetruecolor(150,40);

		//Set the background.
		$bg = imagecolorallocate($ih, 0xFF, 0xFF, 0xFF);
		imagefilledrectangle ($ih, 0, 0, 149, 39, $bg);

		//Set the OCR-spoofing grid.
		$grid = imagecolorallocate($ih, 0xB0, 0xB0, 0xB0);
		imagesetthickness($ih, 1);
		imageline($ih, 10, 20, 139, 20, $grid);
		for($i = 0; $i < 6; ++$i) {
			imageline($ih, ($i*20)+20, 5, ($i*20)+20, 35, $grid);
		}

		//Break up the string with some spaces (easier to read).
		$string = preg_replace('(\w)', '\\0 ', $this->text);

		//Place the text over the grid.
		$fg = imagecolorallocate($ih, 0x33, 0x33, 0x33);
		imagestring($ih, 5, 21, 12,  $string, $fg);

		//Output the image's binary data.
		header('Content-Type: image/png');
		header('Content-Disposition: inline; filename="'.$this->key.'.png"');
		imagepng($ih);
		imagedestroy($ih);
	}


/*-- Private Methods ------------------------------------------------------*/


	/**
	 * generateText
	 * Generates a new random string.
	 *
	 * @return The generated string
	 */
	function generateText() {
		$text = '';

		//Generate all necessary characters.
		for($i = 0; $i < $this->length; ++$i) {
			$text .= $this->getRandChar();
		}
		return($text);
	}


	/**
	 * getRandChar
	 * Returns a single, random character.
	 *
	 * @return A random character
	 */
	function getRandChar() {

		//Randomly pick one character from the list of valid characters.
		return(
			$this->charlist[array_rand($this->charlist)]
		);
	}


	/**
	 * generateCharList
	 * When the object is instantiated, this method builds a list of valid
	 * characters to use in the random string.  This is to avoid managing
	 * a large list of alpha-numeric characters while still providing
	 * custom exclusion of characters that could be visually confusing.
	 *
	 */
	function generateCharList() {

		//Initialize the list.
		$this->charlist = array();

		//List of excluded characters
		$badchars = array('0','O','o','q','9','l','1','I');

		//Numeric
		for($i = ord('0'); $i <= ord('9'); ++$i) {
			if(!in_array(chr($i), $badchars)) {
				$this->charlist[] = chr($i);
			}
		}

		//Lowercase alpha
		for($i = ord('a'); $i <= ord('z'); ++$i) {
			if(!in_array(chr($i), $badchars)) {
				$this->charlist[] = chr($i);
			}
		}

		//Uppercase alpha
		for($i = ord('A'); $i <= ord('Z'); ++$i) {
			if(!in_array(chr($i), $badchars)) {
				$this->charlist[] = chr($i);
			}
		}
	}
}

?>