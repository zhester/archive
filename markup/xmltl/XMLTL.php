<?php
/****************************************************************************
	XMLTL - XML Transport Layer
	Version: 0.0.0
	2009-02-10
	Zac Hester

	An increasing transport need is for object serialization.  Since nearly
	everything on the planet understands XML, this is a logical way to
	transport serialized data (similar to the mechanics behind JSON).
	In the end, my hope is to be able to access node data in target
	platforms as native objects--unaware if they were transported as
	JSON, XML, or carrier pidgeon.

	//Example encoding (simple):
	$msg = new XMLTL(array('k'=>'v','k1'=>'v1','k2'=>'v2'));
	echo $msg;

	//Example encoding (complex):
	class Example { public $k='v';public $k1='v1';public $k2='v2'; }
	$msg = new XMLTL(new Example());
	echo $msg->export();

	//Example decoding:
	$tl = new XMLTL('<'.'?xml version="1.0"?'.'><xmltl> ... </xmltl>');
	//Provide a native PHP object.
	$php_object = $tl->import();
	//Provide a PHP associative array.
	$php_assoc_array = $tl->import(false, true);
****************************************************************************/

class XMLTL {


	public $error = '';
	public $pretty = false; //Not functional at this time.
	protected $src = false;
	protected $xml = false;
	private $d = 0;
	private $assoc = false;


	/**
	 * __construct
	 * Allows the user to initialize the object for either encoding or
	 * decoding.
	 *
	 * @param arg An initial array, object, or XMLTL string
	 */
	public function __construct($arg = false) {
		if($arg && (is_array($arg) || is_object($arg))) {
			$this->src = $arg;
		}
		else if($arg && is_string($arg)) {
			$this->xml = $arg;
		}
		else if($arg) {
			$this->error = 'Invalid source type specified.';
		}
	}


	/**
	 * export
	 * Exports the data representation as an XMLTL string.
	 *
	 * @param src Specify an immediate source object for exporting
	 * @return An XMLTL string representing the provided object
	 */
	public function export($src = false) {
		if($src) {
			$this->src = $arg;
		}
		$this->buildXML();
		return($this->xml);
	}


	/**
	 * import
	 * Imports and XMLTL string for translating into a PHP data type.
	 *
	 * @param xml Specify an immediate XMLTL string for importing
	 * @param assoc Set to true to get an associative array instead of
	 *     a PHP object.
	 * @return A native PHP data representation of the XMLTL string
	 */
	public function import($xml = false, $assoc = false) {
		if($xml) {
			$this->xml = $xml;
		}
		$this->assoc = $assoc;
		$this->importXML();
		return($this->src);
	}


	/**
	 * __toString
	 * Handles string requests by sending back the XMLTL string.
	 *
	 * @return The output of the export method
	 */
	public function __toString() {
		return($this->export());
	}


	/*---------------------------------------------------------------------*/


	/**
	 * buildXML
	 * Constructs a complete XMLTL document using known data.
	 */
	protected function buildXML() {
		$stype = $this->getType($this->src);
		$this->xml = '<'.'?xml version="1.0"?'
			.">\n".'<!DOCTYPE xmltl PUBLIC "-//NRR//XMLTL//EN"'
			.' "http://rushmoreradio.net/public/xmltl.dtd">'
			."\n".'<xmltl bt="'.$stype.'">';
		if($stype == 'a') {
			foreach($this->src as $v) {
				$this->xml .= $this->getTag($v);
			}
		}
		else if($stype == 'h') {
			foreach($this->src as $k => $v) {
				$this->xml .= $this->getTag($v, $k);
			}
		}
		else {
			$this->xml .= $this->getTagData($this->src);
		}
		$this->xml .= "\n</xmltl>";
	}


	/**
	 * getType
	 * Determines the XMLTL type for any PHP variable.
	 *
	 * @param node Any PHP variable
	 * @return The XMLTL type string for the variable
	 */
	private function getType($node) {
		if(is_array($node)) {
			$ks = array_keys($node);
			if(is_string($ks[0])) {
				return('h');
			}
			return('a');
		}
		else if(is_int($node)) { return('i'); }
		else if(is_null($node)) { return('n'); }
		else if(is_bool($node)) { return('b'); }
		else if(is_float($node)) { return('f'); }
		else if(is_string($node)) { return('s'); }
		else if(is_object($node)) { return('h'); }
		return('');
	}


	/**
	 * getTag
	 * Builds a complete XMLTL tag for any PHP variable.
	 *
	 * @param v The PHP variable for which to build a tag.
	 * @param k The key name for named variables.
	 * @return An XMLTL string representing the PHP variable.
	 */
	private function getTag($v, $k = false) {
		$t = $this->getType($v);
		$n = $k ? ' n="'.htmlentities($k).'"' : '';
		$in = str_repeat("\t", $this->d+1);
		switch($t) {
			case 'n':
				return("\n$in".'<s'.$n.' t="n"/>');
			case 'b':
				return(
					"\n$in".'<s'.$n.' t="b">'
						.$this->getTagData($t,$v).'</s>'
				);
			case 'f': case 'i':
				return(
					"\n$in".'<s'.$n.' t="'.$t.'">'
						.$this->getTagData($t,$v).'</s>'
				);
			case 's':
				return(
					"\n$in".'<s'.$n.'>'
						.$this->getTagData($t,$v).'</s>'
				);
			case 'a':
				$b = "\n$in".'<a'.$n.'>';
				++$this->d;
				foreach($v as $value) {
					$b .= $in.$this->getTag($value);
				}
				--$this->d;
				return($b."\n$in</a>");
			case 'h':
				$b = "\n$in<h$n>";
				++$this->d;
				foreach($v as $key => $value) {
					$b .= $in.$this->getTag($value, $key);
				}
				--$this->d;
				return($b."\n$in</h>");
		}
		$this->error = 'Invalid type found in array/object.';
		return('<!-- XMLTL Unknown Data -->');
	}


	/**
	 * getTagData
	 * Builds the CDATA value for a scalor tag.
	 *
	 * @param type The XMLTL data type.
	 * @param value A scalor variable value.
	 * @return A valid XMLTL scalor value string.
	 */
	private function getTagData($type, $value) {
		switch($type) {
			case 'n':
				return('');
			case 'b':
				return($value?'true':'false');
			case 'f': case 'i': case 's':
				return(htmlspecialchars($value));
		}
		$this->error = 'Internal logic error.';
		return('<!-- XMLTL Internal Error -->');
	}


	/**
	 * importXML
	 * Imports the provided XML document into native PHP data types.
	 */
	private function importXML() {
		$sx = new SimpleXMLElement($this->xml);
		if($this->assoc) {
			$this->src = $this->getArray($sx);
		}
		else {
			$this->src = $this->getObject($sx);
		}
	}


	/**
	 * getArray
	 * Builds an appropriate array out of a SimpleXML object.
	 *
	 * @param sxe The SimpleXMLElement instance
	 * @return An array representing the SimpleXMLElement
	 */
	private function getArray($sxe) {

		//Current tag name.
		$ctag = $sxe->getName();

		//Scalor tags.
		if($ctag == 's') { return($this->getScalor($sxe)); }

		//Array buffer.
		$arr = array();

		//Run through each child node.
		foreach($sxe as $node) {

			//Keyed node.
			if(isset($node['n'])) {

				//Assign with associative index.
				$arr[strval($node['n'])] = $this->getArray($node);
			}

			//Anonymous node.
			else {

				//Assign with numeric index.
				$arr[] = $this->getArray($node);
			}
		}
		return($arr);
	}


	/**
	 * getObject
	 * Builds an appropriate PHP object out of a SimpleXML object.
	 *
	 * @param sxe The SimpleXMLElement instance
	 * @return An object representing the SimpleXMLElement
	 */
	private function getObject($sxe) {

		//Current tag.
		$ctag = $sxe->getName();

		//Check for root node.
		if($ctag == 'xmltl') {

			//Get base type.
			$bt = isset($sxe['bt']) ? strval($sxe['bt']) : 'h';

			//Pretend to be one of the other tags.
			$ctag = ($bt != 'h' && $bt != 'a') ? 's' : $bt;
		}

		//Scalor.
		if($ctag == 's') {
			return($this->getScalor($sxe));
		}

		//Array.
		else if($ctag == 'a') {
			$arr = array();
			foreach($sxe as $node) {
				$arr[] = $this->getObject($node);
			}
			return($arr);
		}

		//Hash.
		else if($ctag == 'h') {
			$obj = new stdClass();
			$anon = 0;
			foreach($sxe as $node) {

				//Keyed node is assigned to a named property.
				if(isset($node['n'])) {
					$obj->{strval($node['n'])} = $this->getObject($node);
				}

				//Anonymous node is assigned to a list of anonymous nodes.
				else {
					if($anon == 0) { $obj->ANONYMOUS = array(); }
					$obj->ANONYMOUS[$anon] = $this->getObject($node);
					++$anon;
				}
			}
			return($obj);
		}

		//Conforming XML documents won't get here.
		$this->error = 'Invalid XML document.';
		return(false);
	}


	/**
	 * getScalor
	 * Returns an appropriate scalor value for an end-point SimpleXMLElement
	 *
	 * @param sxe The SimpleXMLElement instance
	 * @return A properly-typed PHP scalor value
	 */
	private function getScalor($sxe) {
		$str = strval($sxe);
		if(isset($sxe['t'])) {
			switch($sxe['t']) {
				case 'n': return(NULL);
				case 'b': return($str=='true'?true:false);
				case 'i': return(intval($str));
				case 'f': return(floatval($str));
			}
		}
		return($str);
	}
}

?>