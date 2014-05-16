<?php
/****************************************************************************
	Single Script File Encoder
	Zac Hester - 2006-12-15

	One of my single-script solutions to encode a binary file into a
	base64 string that's ready to roll for PHP code insertion.
****************************************************************************/

//Process encode request.
if(is_uploaded_file($_FILES['file64']['tmp_name'])) {

	//Check for the PHP function and define if it isn't available.
	if(!function_exists('mime_content_type')) {
	    function mime_content_type($f) {
	        return(trim(exec('file -bi '.escapeshellarg($f))));
	    }
	}

	//Output PHP code that can be used to display the image.
	header('Content-Type: text/plain');
	echo "\$php_array = array(\n\t'type' => '"
		.mime_content_type($_FILES['file64']['tmp_name'])."',\n\t'size' => '"
		.filesize($_FILES['file64']['tmp_name'])."',\n\t'name' => '"
		.$_FILES['file64']['name']."',\n\t'data' =>\n";
	$buffer = base64_encode(file_get_contents($_FILES['file64']['tmp_name']));
	$pointer = 0;
	$jump = 60;
	while($pointer < strlen($buffer)) {
		echo "\t";
		if($pointer != 0) {
			echo '.';
		}
		echo "'".substr($buffer, $pointer, $jump)."'\n";
		$pointer += $jump;
	}
	echo ');';
}

//Deliver binary data.
else if($_REQUEST['binary'] == 'ssfe_logo') {
	$php_array = array(
		'type' => 'image/png',
		'size' => '2255',
		'name' => 'ssfe_logo.png',
		'data' =>
		'iVBORw0KGgoAAAANSUhEUgAAAIAAAACACAMAAAD04JH5AAAABGdBTUEAAK/I'
		.'NwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAGA'
		.'UExURdDQ0NjY2N3d3ezs7Hx8fAoKCr2Njb6+vszMzCwsLMfHx3R0dF1dXd/f'
		.'3+Dg4NY4OMrKypqamuMjIx4eHrW1tdTU1JWVlbOzs2BgYHBwcCYmJsHBwaGh'
		.'octLS2ZmZmxsbNra2rm5uaysrIWFhYyMjIGBgaWlpcTExEpKSmRkZDk5OYmJ'
		.'iclpac7OzmlpacjIyBYWFp2dnbq6usZGRjY2NhAQEK+vr15eXtpaWgQEBLCw'
		.'sE9PT0FBQZCQkKmpqXl5eTMzM8hYWExMTFRUVPkJCd0tLURERD8/P8yMjJiY'
		.'mL9/fyIiIhoaGvT09Obm5vz8/Pv7+9LS0v7+/mJiYmNjY/39/TAwMDw8PPf3'
		.'9/r6+u7u7vHx8erq6uLi4vj4+PX19fLy8unp6e/v79bW1tfX1+Xl5UdHR+jo'
		.'6FpaWlFRUVhYWFZWVvPz8/n5+fb29uTk5Ofn5+Pj4+vr69PT0/EREfDw8Ooa'
		.'GqJycsJycpODg8CgoLBgYOsrK9h4eP8AAP///zpCJY0AAAbVSURBVHja7Jrr'
		.'V9NIGIfTBG1taWhZCu2CvYhQFQtrWUVRBBVkveRCIGvS0tyakL30XlDX3YV/'
		.'fVNa6KS5NZ6i5+zJ88nznl9nHpvJzNs5QGffGcgT8AQ8AU/AE/AEPAFPwBPw'
		.'BDwBT8AT8AQ8gf+BAMVWEBUHEQsMfUaRfEFfVhGMpEYvQDFqe3ZqBiQfaPJU'
		.'FfFlM7pyJggL9OgFWDF48/h0gKBYRaDPhvIvzeroBbDYi9NT40wC/NZYvg8z'
		.'oxdAZnv/0Y+Tl2z/q7QiO93yMVB+jHKjF1Az3Ykmbs/dOufRTH4lXFT9Xa+d'
		.'2w965fVMMC2RoxfAZwYE1vMraYTDp071AutTWV+Loa5OoP8IJt6u4JxyUb58'
		.'BBP3s83aVQoATESQ+pSxvJMVr+ARNExmOr2ONjOn3+gtaPm3jTO9iCnZSWP5'
		.'JoSNXqCYmzDOtJoTzbaH1YAwegGuNPPRZKZCY3by2wjQWDj79uZqj+2LmYp8'
		.'M3j9snx8lQJ8Q47485kOM9FPlwJMKx2Y7Zan5v46vjoBRmxnM73T8Nb03xer'
		.'rYDIwXyv/Cj6qfvNrF7JIoxt93f7i8f+OY34/gHKvSfzQr6C1/AkZ1xsx35V'
		.'au8YyttfmuzoBSqy4TXc+QJjWPrxYHnyujx0Q0JRNEmyDpAETVEUJ+75Zx4B'
		.'dE49hOHVdr+8vr4+M5VfgY2HoYWANj3HCydSwQapiNVYkqarghpOy+U+vjTa'
		.'QAqIWkr7ugXZl4bRkiJKDEENK0AyQgsvhWEb0KbWZHIkyQmIQzJcaiACZ+xH'
		.'bQRoThDD5VwkaM1KAJLRFsZxlRYqQ5EVm2gEKocPhSrtQoCoIVDSmSiKYFgB'
		.'HSI5H0Z4woUAiam+hPOwY7JaFA7TY87JUEwRSDcCFTy2cOBIAmoWTlR5wzl5'
		.'kEOlKuVGQIFu7zuTQxGpEdsYIjkVLnBuBeLOTJUKmsDiEMlbqCsBAtME1gCW'
		.'QwYSWvk5Lp2o5UUweWCejNZP3Ange0vjAEsZfzagIxIJ5OfWkYogyotgMpTJ'
		.'ZyODyczzxRbGuhX4DWDJn/OFS31QFA2jJRwRGEwTAJOJfESGB5LhktISGNKt'
		.'wB8AS5F0o1AEkTSKPMfyom8RTCaych0xJrU9k3YpMP8nwDxUOtG2XQCWrVa1'
		.'44jkD32LYDIRgRHGmGQt5rcTeAUwv4djhOHE1KAZTQBMpgKlImmWpM7cCvwO'
		.'YCbQu57QBMCkmYD7K5pzgacA9gJgcnlkAgsvARZMH0FPYA5MJkcncAdgAWoW'
		.'WaJPd3nR3TUwByaTAW3THUx2Wif3AkcACwFYLGJ9KoIgYDXt1ToXAJMHWV/j'
		.'xJCsWr0ENgJjrwHGZiG42b9wU+rNuqIWKppBRwBMxv25dAlM1uu4WMA4wr3A'
		.'M4CxzGwA2rsE0oj58wjGEh0BMBnP+CODyWAWsdiIbQV2AcbHBhl/urvUlDiy'
		.'IwAmn5om6xJHuxXY/ODEPNyqnQsMlXQvcM8JrSPjOwIPhkiKvHuBn53YLPcE'
		.'hkoSbgVCW05s+sSuwFBJ199A6Fcnxnpr4MEQScT9GgjdAIhPT7+J9pkPhfaP'
		.'bizUu29BFEweTU9PA8mNy6RrgQQ4bCIThMq+c2RZLsfa0F4sG+jtAzqBl5ls'
		.'LqZLtstQAOHd7wOJHwES2RiqHvYQRRVXlO7+1tkJo2Dyjh+CcX2ycSh9zU6o'
		.'FwiEEYzpUeN5rFKpYAzbPQv0AsG0KOiTGM+wX3EWpN4BpCDtp1W3saFoDeLi'
		.'jOsIvAGTRwFUYql+lPjq0zD1A0DKriF5AyZfj6wfWAaHXf4eAu8BbAWmweSz'
		.'0Qn8BGAvACZ3RyaQBIdNuhDQt2RA++ZW4CGAvQCY/DDQkp03ZRXrpsxa4C44'
		.'7F0bAVkncE/fkmlNmVJXbJoya4EDcNgDKwHaIKBvyc6bsr0yNFfg3f06Vvb2'
		.'rwHsQ4qFQE2UN8DkO5PrkZc3roXwopsrGqKiQHFw2DhUN79konm1rBOwYB6V'
		.'XF3RCHW9wCuoaSkQ2xxCIOr6jmjvzhOALatvgNAE9p84E3B5S4Y1ylvg59/H'
		.'cCsB0bfrPP/7zk87NwL8YUA/gl/EzAVqLfihs8BW2uLzVouQkVZSyfha995p'
		.'LZ5MBQuM+VvAnSip5H583Ia1/eS0YtWTWVxWsxiiwL5Yu/Mit2MyXEcw88tm'
		.'iuUL+EXSnHbMF8Ylq57M6rqe4yVEbHT3sobY0noq80suiqjyxcukOaqIFGus'
		.'qzsiiiZYrsZf7OZ8jWMJ8wEoiiA5hsfs4HnG7c/zs1471UP7p+Ul05kuaYrt'
		.'x70/ZPIEPAFPwBPwBDwBT8AT8AQ8AU/guwv8J8AAuDc4cH8eXegAAAAASUVO'
		.'RK5CYII='
	);
	header('Content-Type: '.$php_array['type']);
	header('Content-Length: '.$php_array['size']);
	header('Content-Disposition: inline; filename="'.$php_array['name'].'"');
	echo base64_decode($php_array['data']);
	exit();
}

//Display form.
else {
	?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<title>Base 64 File Encoder</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta name="Content-Language" content="english" />
<meta name="author" content="Zac Hester" />
<meta name="generator" content="vi" />
<style type="text/css">
body {
	font-family: Arial,Verdana,sans-serif;
	font-size: 12pt;
}
h1 {
	margin: 0 0 0.5em 0;
}
h3 {
	margin: 0.5em 0 0 0;
}
div#page_root {
	width: 640px;
	margin: 0.5em auto;
	padding: 10px;
	border: solid 1px #CCCCCC;
	color: #000000;
	background-color: #FFFFFF;
	background-position: right top;
	background-repeat: no-repeat;
	background-image: url(file64.php?binary=ssfe_logo);
}
pre {
	margin: 0.25em 0;
	font-size: 10pt;
}
</style>
</head>
<body>
<div id="page_root">
<form action="file64.php" method="post" enctype="multipart/form-data">
	<h1>Base 64 File Encoder</h1>
	<p>
		File to Encode<br />
		<input type="file" name="file64" />
		<input type="submit" value="Upload" />
	</p>
</form>
<h3>Example Code for Output</h3>
<pre>
header('Content-Type: '.$php_array['type']);
header('Content-Length: '.$php_array['size']);
header('Content-Disposition: inline; filename="'.$php_array['name'].'"');
echo base64_decode($php_array['data']);
exit();
</pre>
</div>
</body>
</html>
	<?php
}

?>