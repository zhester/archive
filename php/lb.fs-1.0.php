<?
/****************************************************************************
	Filesystem Access Library
	Zac Hester - 2004-12-11
	
	This library provides a simple, OOP-based interface to working with the
	filesystem.  It basically just hides the directory handles and provides
	some array-buiding features.
****************************************************************************/

class fs {
	var $base;		//The current base path.
	var $start;		//Never allow someone to chdir above this path. **ENFORCE**
	var $dh;		//The directory handle.
	var $conf;		//Configuration array.
	var $error;		//Error string (if one occurs). **DO IT**
	function fs($basepath = '.', $conf = array()) {
		$this->start = $basepath;
		$this->base = $basepath;
		$this->dh = @opendir($basepath);
		$this->conf = $conf;	
	}
	function close() { @closedir($this->dh); }
	function set_config($target, $value) {
		$this->conf[$target] = $value;	
	}
////
	function chdir($newpath = '.') {
		if($newpath == '.') { $this->base = '.'; }
		else if(substr($newpath, 0, 1) == '/') { $this->base = $newpath; }
		else { $this->base .= $newpath; }
		$this->close();
		$this->dh = @opendir($this->base); 	
	}
////
	function ls($newpath = '', $moreinfo = false) {
		$ls = array();
		if($moreinfo) {
			while(($entry = readdir($this->dh)) !== false) {
				if($entry != '.' && $entry != '..') {
    				$ls[] = array(
    					'name' => $entry,
    					'size' => filesize($entry)
    				);
				}
			}
		}
		else {
			while(($entry = readdir($this->dh)) !== false) {
				if($entry != '.' && $entry != '..') {
					$ls[] = $entry;
				}
			}
		}
		return($ls);
	}
}
?>