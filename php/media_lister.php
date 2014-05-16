<?

function normal_name($input) {
	return(str_replace('_',' ',substr($input,0,-4)));
}
function show_bytes($input) {
	if($input < 1024) {
		return($input.' B');
	}
	if($input < (1024*1024)) {
		return(number_format(($input/1024),3).' kB');
	}
	return(number_format(($input/(1024*1024)),3).' MB');
}

$dh = opendir('.');
$num_nodes = 0; $fnames = array(); $fsizes = array(); $ftimes = array();
while(($node = readdir($dh)) !== false) {
	if(substr($node,0,1) != '.' && substr($node,-4) == '.mp3') {
		$fnames[] = $node;
		$fsizes[] = filesize($node);
		$ftimes[] = filemtime($node);
		++$num_nodes;
	}
}
closedir($dh);

if($_REQUEST['sort'] == 'size') {
	array_multisort($fsizes, SORT_NUMERIC, SORT_ASC,
		$fnames, SORT_STRING, SORT_ASC,
		$ftimes);
}
else if($_REQUEST['sort'] == 'name') {
	array_multisort($fnames, SORT_STRING, SORT_ASC,
		$fsizes, SORT_NUMERIC, SORT_ASC,
		$ftimes);
}
else {
	array_multisort($ftimes, SORT_NUMERIC, SORT_DESC,
		$fnames, SORT_STRING, SORT_ASC,
		$fsizes);
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="en">
<head>
<title>Audio Files</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta name="Content-Language" content="english" />
<meta name="author" content="Zac Hester" />
<meta name="generator" content="vi" />
<meta name="description" content="" />
<meta name="keywords" content="" />
<meta name="audience" content="all" />
<meta name="robots" content="all" />
<meta name="revisit" content="14 days" />
<link href="../media.css" rel="stylesheet" type="text/css" />
</head>
<body>

<h1>Audio Files</h1>

<p>Note: All audio files require MP3 playback software such as Windows
Media Player or Winamp.</p>

<table>
 <tr>
  <th><a href="index.php?sort=name" title="Sort by File Name">File</a></th>
  <th><a href="index.php?sort=size" title="Sort by File Size">Size (bytes)</a></th>
  <th><a href="index.php?sort=time" title="Sort by Date Uploaded">Date Uploaded</a></th>
 </tr>
<?
for($i = 0, $tsize = 0; $i < $num_nodes; ++$i) {
	if($i%2) { $rc = 'even'; } else { $rc = 'odd'; }
	$tsize += $fsizes[$i];
	echo '<tr class="'.$rc.'"><td><a href="'.$fnames[$i].'" title="'
		.$fnames[$i].'">'.normal_name($fnames[$i]).'</a></td><td>'
		.show_bytes($fsizes[$i]).'</td><td>'
		.date('l, g:i a, F j, Y',$ftimes[$i]).'</td></tr>';
}
echo '<tr><td>'.$num_nodes.' Audio Files Available</td><td>'
	.show_bytes($tsize).' Total Usage</td><td>&nbsp;</td></tr>';
?>
</table>

</body>
</html>