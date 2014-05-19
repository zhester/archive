<?
/****************************************************************************
	Web Site Content Handler (AKA: Micro Web Server)
	Zac Hester - 2004-09-16

	This script is used through creating a document alias in the Apache
	global server configuration (or just the Virtual Host configuration)
	(don't ask me why it doesn't work in an .htaccess file, it just doesn't).

	An example of the alias line is here:

AliasMatch ^/\S+?(.html|.htm) "/usr/local/apache/htdocs/page.php"

	For some reason, when this method is used in a local context (viz.
	.htaccess file), it behaves like an Action/AddHandler pair.  This
	doesn't work because there are probably not any documents to be
	served in the request-mapped directory.  The whole reason for this
	method is to allow our content to be abstract of the URI scheme.
****************************************************************************/

//Site configuration.
$conf = array(
	'content_prefix' => 'content',
	'error_document' => 'error404.html',
	'ignore_dir' => array('stats')
);

//Find requested document.
$request = $_SERVER['REQUEST_URI'];

//Strip any GET data.
if($qmark_pos = strpos($request, '?')) {
	$request = substr($request, 0, $qmark_pos);
}

//Strip any page link names.
if($hash_pos = strpos($request, '#')) {
	$request = substr($request, 0, $hash_pos);
}

//Check for ignored directories.
preg_match('#^/(\S+?)/(\S*)$#', $request, $match);
$directory = $match[1];
if(in_array($directory, $conf['ignore_dir'])) {

	//Let's see if a file is specified.
	if(!preg_match('/\.(html|htm)$/', $request)) {
		$request .= find_index('.'.$request);
	}

	//Dump the file and exit.
	readfile('.'.$request);
	exit();
}

//Check for root request.
if($request == '/') {

	//Map this to an index file.
	$request .= find_index($conf['content_prefix']);
}

//Check for document existence.
if(!file_exists($conf['content_prefix'].$request)) {

	//File doesn't exist, use error page.
	header('HTTP/1.0 404 Not Found');
	$request = '/'.$conf['error_document'];
}

//Look for a title.
$title = get_title($conf['content_prefix'].$request);

//Bring in necessary page rendering library.
require('rs.html.php');

//Display the header.
print_head($title);

//Send the file to the user.
echo output_filter(file_get_contents($conf['content_prefix'].$request));


//Display the footer.
print_foot();
exit();

//This is a wrapper to include any content replacement filters.
function output_filter($input) {

	//Grab the XML handler.
	require('lb.zxml-1.0.php');

	//Start a new parser.
	$sf = new zxml($input);

	//Check for replacement tags.
	$num_tags = $sf->get_num_tagsets('replace');
	for($i = 0; $i < $num_tags; $i++) {

		//Determine what to replace.
		$cdata = $sf->get_cdata();
		if($cdata == 'year') {
			$sf->replace_tagset(date('Y'));
		}
		else if($cdata == 'file') {
			$sf->replace_tagset($_SERVER['REQUEST_URI']);
		}
		else if(substr($cdata, 0, 11) == 'years_since') {
			list($tagdata, $limit) = explode(':', $cdata);
			$format = $sf->get_attribute('format');
			if($format == 'words') {
				require('fn.num2text.php');
				$sf->replace_tagset(num2text(date('Y') - $limit));
			}
			else if($format == 'ucwords') {
				require('fn.num2text.php');
				$sf->replace_tagset(ucwords(num2text(date('Y') - $limit)));
			}
			else {
				$sf->replace_tagset(date('Y') - $limit);
			}
		}
	}

	//Send back the processed document.
	return($sf->get_final());
}

//This function seeks for an index page when a root request is handled.
//  This works like most would expect unless you have a different idea
//  about how to name index files.
function find_index($prefix) {
	global $conf;

	//This is the list of index files I will use to look.
	//  The order is how I determine the precedence of the files if multiple
	//  files are found.
	$files = array('index.html','index.htm','default.htm');
	$to_use = 99;

	//Let's look at the directory where content should be.
	if(!is_dir($prefix)) { return($conf['error_document']); }
	$dh = opendir($prefix);
	if(!$dh) { return($conf['error_document']); }

	//Scan for available index-looking files.
	while(($item = readdir($dh)) !== false) {
		if(in_array($item, $files)) {
			$test = array_search($item, $files);
			//Assign any higher-precedence file names.
			$to_use = ($test < $to_use)? $test : $to_use;
		}
	}
	closedir($dh);

	//No index file could be found.
	if($to_use == 99) {
		return($conf['error_document']);
	}
	return($files[$to_use]);
}

//This function scans the input file for a title to use on the page.
function get_title($document) {

	//Some patterns for title scanning.
	$op_com = preg_quote('<!-- ', '/');
	$cl_com = preg_quote(' -->', '/');
	$op_hdr = preg_quote('<h1>', '/');
	$cl_hdr = preg_quote('</h1>', '/');

	//Pull the first line off the file.
	$fh = fopen($document, 'rb');
	$title_line = fgets($fh);
	fclose($fh);

	//Check for a first-line header.
	if(strpos($title_line, '<h1>') !== false ||
		strpos($title_line, '<H1>') !== false) {
		preg_match('/'.$op_hdr.'(.+?)'.$cl_hdr.'/i', $title_line, $matches);
		return($matches[1]);
	}

	//Check for someone using a comment to specify the title.
	else if(strpos($title_line, '<!--') !== false) {
		preg_match('/'.$op_com.'(.+?)'.$cl_com.'/', $title_line, $matches);
		return($matches[1]);
	}

	//No title available, :,(
	return('');
}
?>