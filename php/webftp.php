<?
//FTP Test of Technology

$conf = array(
      'tempdir' => '/tmp',
      'username' => $_SERVER['PHP_AUTH_USER'],
      'password' => $_SERVER['PHP_AUTH_PW'],
      'ftphost' => '127.0.0.1',
      'ftpremotedir' => 'public_html/web'
);

$target = 'userfile.txt';
$source = $conf['tempdir'].'/tempfile';
$fh = fopen($source, 'w');
fputs($fh, "This is\n\ta test of\n\t\tFTP file management.");
fclose($fh);

//connect
$ftph = ftp_connect($conf['ftphost']);
$login = ftp_login($ftph, $conf['username'], $conf['password']);
if(!$ftph || !$login) {
	echo 'Unable to log into FTP host.';
}

else {
	ftp_chdir($ftph, $conf['ftpremotedir']);
	if(ftp_put($ftph, $target, $source, FTP_BINARY)) {
		echo 'File uploaded successfully.';     
	}
	else {
		echo 'File not uploaded.';     
	}
}
?>