<?php

/**
 * FilePath
 * A file path abstraction.
 *
 * MS people should note that I could care less if any of my code runs
 * on a VMS-based platform.  If you're stuck on such a system (I'm sorry),
 * you should probably extend this class to include stuff that cares about
 * "volumes" and which way the path separator leans.
 *
 * This class also implements a few "convenience" methods.  I don't
 * consider them central to working with files, but they're so common,
 * I figured I might as well build some shortcuts to the PHP stuff.
 * They could also be used to ensure that a path is valid before using
 * them in a web reference or file operation.
 *
 * @author Zac Hester
 * @date 2006-04-14
 */
class FilePath {


/**
 * FilePath
 * While building the object, this allows the user to set a path.
 * If no default path is specified, it will default to the current script.
 * @param path A string representing the file system path
 * @param context Specifies a path relativity context 
 */
function FilePath($path = '', $context = '') {

	//The default path is to the current script.
	if($path == '') {
		$path = $_SERVER['SCRIPT_FILENAME'];
	}

	//Set this path.
	$this->setPath($path, $context);
}


/**
 * setPath
 * @param path A string representing a path to a file over the file system
 * @param context Specifies a path relativity context 
 */
function setPath($path, $context = '') {

	//Sanitize user input.
	$path = $this->sanitizePath($path);

	//Set absolute path.
	if(substr($path,0,1) == '/') {
		$this->fileSystemPath = $path;
	}

	//Set relative path.
	else {

		//Check for file context.
		if($context) {

			//Check directory context.
			$context = is_dir($context) ? $context : dirname($context);

			//Set path relative to calling script's context.
			$this->fileSystemPath = $context.'/'.$path;
		}

		//Otherwise, assume they want it relative to the requested script.
		else {

			//Set path relative to requested script.
			$this->fileSystemPath
				= dirname($_SERVER['SCRIPT_FILENAME']).'/'.$path;
		}
	}

	//Set the URI to this file.
	$this->updateURI();
}


/**
 * setPathURI
 * @param path A string representing a path to a file over HTTP
 * @param context Specifies a path relativity context 
 */
function setPathURI($path, $context = '') {

	//Set absolute path.
	if(substr($path,0,1) == '/') {
		$this->setPath($_SERVER['DOCUMENT_ROOT'].$path);
	}

	//Set absolute path from full URL.
	else if(preg_match('#^https?://#', $path)) {
		$info = parse_url($path);
		$this->setPath($_SERVER['DOCUMENT_ROOT'].$info['path']);
	}

	//Set relative path.
	else {

		//Check for file context.
		if($context) {

			//Check directory context.
			$context = is_dir($context) ? $context : dirname($context);

			//Set path relative to calling script's context.
			$this->setPath($context.'/'.$path);
		}

		//Otherwise, assume they want it relative to the requested script.
		else {

			//Set path relative to requested script.
			$this->setPath(dirname($_SERVER['SCRIPT_FILENAME']).'/'.$path);
		}
	}
}


/**
 * getPath
 * @return A string representing a path to the file over the file system
 */
function getPath() {
	return($this->fileSystemPath);
}


/**
 * getPathURI
 * @return A string representing a path to the file over HTTP
 */
function getPathURI() {

	//Reference the URI.
	$path = $this->uriPath;

	//A URL to a directory should end with a '/'
	if($this->isDirectory()) {
		$path .= '/';
	}

	//Return the URI.
	return($path);
}


/**
 * getPathFullURI
 * @return A string representing a full path to the file over HTTP
 */
function getPathFullURI() {

	//Check for SSL.
	$proto = $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';

	//Construct and return the full URI.
	return($proto.'://'.$_SERVER['HTTP_HOST'].$this->getPathURI());
}


/**
 * fileExists
 * @return True if the file exists
 */
function fileExists() {
	return(file_exists($this->getPath()));
}


/**
 * isFile
 * @return True if the current path points to a file 
 */
function isFile() {
	return(is_file($this->getPath()));
}


/**
 * isDirectory
 * @return True if the current path points to a directory
 */
function isDirectory() {
	return(is_dir($this->getPath()));
}


/**
 * getMime
 * @return Mime content type (example: 'image/jpg')
 */
function getMime() {
	return(mime_content_type($this->getPath()));
}


/**
 * getSize
 * @return Number of bytes representing the file's size
 */
function getSize() {
	return(filesize($this->getPath()));
}


/**
 * getModifiedTime
 * @return Time stamp of file's last modification
 */
function getModifiedTime() {
	return(filemtime($this->getPath()));
}


/*---------------------------------------------------------------------------
	All methods and fields below this point are considered private.
---------------------------------------------------------------------------*/


	//Maintains an absolute path to the file on the system.
	var $fileSystemPath;

	//Maintains an absolute path to the file over HTTP.
	var $uriPath;


/**
 * updateURI
 * Attempts to set the URI for the given file.
 *
 * @return True on success
 */
function updateURI() {

	//Make sure the path is within the web site's document root.
	if(strpos($this->fileSystemPath, $_SERVER['DOCUMENT_ROOT']) == 0) {
		$this->uriPath = substr(
			$this->fileSystemPath,
			strlen($_SERVER['DOCUMENT_ROOT'])
		);
		return(true);
	}

	//This is an unservable file.
	else {
		$this->uriPath = false;
		return(false);
	}
}


/**
 * sanitizePath
 * Used to correct for any path problems coming in from users.
 *
 * @param path The path string to check
 * @return A properly formatted path string
 */
function sanitizePath($path) {

	//Strip possible trailing slash.
	return(rtrim($path, '/'));
}


/*
 * End of class definition.
 */
}



/**
 * Check for the PHP function and define if it isn't available.
 */
if(!function_exists('mime_content_type')) {
	function mime_content_type($f) {
		return(trim(exec('file -bi '.escapeshellarg($f))));
	}
}

?>