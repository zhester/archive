<?

$fc = ftp_connect('localhost');
ftp_login($fc, 'planetzac', 'admiral');
ftp_chdir($fc, 'web');
$ls = ftp_rawlist($fc, '.');
ftp_quit($fc);

header('Content-Type: text/plain');

//echo basename('/usr/local/apache')."\n\n";

//Local stat
//$sts = stat('browseremulator.txt');
//print_r($sts);
//echo 'Octal mode: '.sprintf('%o', $sts['mode'])."\n\n";

//Remote stat
echo 'Line: '.$ls[1]."\n";
$sts = statfromls($ls[1]);
print_r($sts);
echo 'Octal mode: '.sprintf('%o', $sts['mode'])."\n\n";


//converting octal string to real mode value (integer)
//$octal = '0755';
//echo 'Octal: '.$octal.'; Decimal: '.intval($octal, 8).";\n";


/**
 * Parses a line from ls and converts it to a stat-like array.
 *
 * Example lines that can be parsed:
 * -rw-r--r-- 1 1001 1001 1024 Dec 3 18:45 testfile.txt
 * -rwxr-xr-x 1 1001 1001 1024 Dec 3 18:46 testbin.exe
 */
function statfromls($line) {
	$parts = preg_split('/\s+/', $line);
//print_r($parts);
	$buff = array();
	$monthnum = array('jan'=>1,'feb'=>2,'mar'=>3,'apr'=>4,'may'=>5,
	'jun'=>6,'jul'=>7,'aug'=>8,'sep'=>9,'oct'=>10,'nov'=>11,'dec'=>12);
	//Non-POSIX File Info
	if(substr($parts[0], 0, 1) == 'd') { $buff['type'] = 0; }
	else { $buff['type'] = 1; }
	$buff['name'] = $parts[8];
	//POSIX-Like File Info
	$buff['nlink'] = $parts[1];
	$buff['uid'] = $parts[2];
	$buff['gid'] = $parts[3];
	$buff['size'] = $parts[4];
	//Old file string.
	if(strpos($parts[7],':') === false) {
		$buff['date'] = $parts[5].' '.$parts[6].' '.$parts[7];
	}
	//New file string.
	else {
		$fmnum = $monthnum[strtolower($parts[5])];
		if($fmnum <= date('j')) { $year = date('Y'); }
		else { $year = date('Y') - 1; }
		$buff['date'] = $parts[5].' '.$parts[6].', '.$year.' '.$parts[7];
	}
	//Timestamp for mtime.
	$buff['mtime'] = strtotime($buff['date']);
	//Remove plus signs, take off the first char, and reverse.
	$ms = strrev(substr(str_replace('+','',$parts[0]),1));
	for($i = 0, $mode = 0; $i < strlen($ms); ++$i) {
		if($ms[$i] != '-') { $mode += pow(2,$i); }
	}
	$buff['mode'] = $mode;
	return($buff);
}

?>