<?
/****************************************************************************
	Environment Checking Script
****************************************************************************/

//Specific Info Request
if($_GET['showme']) {
	phpinfo($_GET['showme']);
	exit();
}

//Query Submission Request
else if($_GET['arg'] || $_POST['arg']) {
	phpinfo(32);
	exit();
}

//Display an Index
else { ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>PHP Environment Test Page</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
</head>
<body>
<h1>Check the Environment</h1>
<h2>Basic Queries</h2>
<p><a href="env.php?arg=test0&arg1[]=test10&arg1[]=test11">GET Query</a></p>
<form action="env.php" method="post">
<input type="hidden" name="arg1[]" value="test0" />
<input type="hidden" name="arg1[]" value="test1" />
<input type="hidden" name="arg1[]" value="test2" />
<p><input type="text" name="arg" value="test" /></p>
<p><input type="submit" value="POST Query" /></p>
</form>
<h2>Specific Information</h2>
<ul>
	<li><a href="env.php?showme=32">Predefined Variables</a></li>
	<li><a href="env.php?showme=4">PHP Settings (Local &amp; Global)</a></li>
	<li><a href="env.php?showme=8">Loaded Modules</a></li>
	<li><a href="env.php?showme=-1">Everything</a></li>
</ul>
</body>
</html><? } ?>