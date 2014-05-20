<?
//Web based file editor.

if($_POST['file']) {
    $fh = fopen('db.txt', 'w');
    if($fh) {
        fputs($fh, infilter($_POST['file']));
        fclose($fh);
    }
}

function infilter($input) {
    return(stripslashes(str_replace("\r\n", "\n", $input)));
}

$filecontents = file_get_contents('db.txt');
?>
<html>
<head>
<title>Web Temp</title>
</head>
<body>
<form action="webtemp.php" method="post">
<textarea name="file" rows="20" cols="80"><?=$filecontents?></textarea>
<br />
<input type="submit" value="Save" />
</form>
</body>
</html>
