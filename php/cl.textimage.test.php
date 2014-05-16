<?php

session_start();

require('cl.textimage.php');

$ti = new TextImage();

if($_REQUEST['show']) {
	$ti->sendImage();
	$ti->resetText();
}
else {
?>
	<p>
		The generated image:<br />
		<img src="<?php echo $_SERVER['PHP_SELF']; ?>?show=true" alt="" />
	</p>
	<p>
		The actual text:<br />
		<input type="text" value="<?php echo $ti->text; ?>" />
	</p>
<?php
}

?>