<?php

/*
 * Server-side script to generate image data.
 */

$data = array();
$dir = './path/to/images';
$path = 'path/to/images';
$dh = opendir($dir);

while(($node = readdir($dh)) !== false) {
	$file = $dir.'/'.$node;
	if(substr($node,0,1) != '.' && is_file($file)
		&& preg_match('/\.(jpg|gif|png)$/i', $node)) {

		list($w, $h) = getimagesize($file);
		$data[] = array(
			'path' => $path.'/'.$node,
			'size' => filesize($file),
			'width' => $w,
			'height' => $h,
			'node' => $node
		);

	}
}

closedir($dh);

function file_list_compare($left, $right) {
	return(strnatcasecmp($left['node'], $right['node']));
}
usort($data, 'file_list_compare');

header('Content-Type: application/json');
echo json_encode($data);

?>