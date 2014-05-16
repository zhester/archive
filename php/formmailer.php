<?
/****************************************************************************
	Super-Generic Form Mailer 1.0
	E-Net Information Services - Zac Hester - August 8th - 11th, 2003

	Special Form Element Names:
		sg_mailto		Email Recipient's Email Address (REQUIRED)
		sg_nextpage		The next page to display after the form has been
						sent.
		sg_errorpage	The generic error page that is displayed if
						something goes wrong (after deployment).
		sg_htmlemail	If set, the form information will be sent as an
						HTML email to the recipient.
		sg_sortalpha	If set, the form will be sorted alphabetically
						based on the "name" attribute values of each field.
		sg_mailsubject	If set, the emails will have this as their subject.
		sg_mailfrom		If set, this is the name/email of the "From" field
						in the email.
		sg_*			All fields filtered from final email.

	Version History:

	Version 1.01		2003-08-11
		Bug Fix: Strip slashes from mail subject.
		Added "sg_" field name filtering.
	Version 1.0 		2003-08-11
		First working version (Not Released).
****************************************************************************/

//Trap debugging request.
if($_GET['sg_debug'] != '') { render_debug_form(); }

//Check request method.
if($_SERVER['REQUEST_METHOD'] == 'GET') {
	$form_data = $_GET;
}
else if($_SERVER['REQUEST_METHOD'] == 'POST') {
	$form_data = $_POST;
}
else {
	render_error('Unknown Request Method.  Please use either "post" or "get"'
		.' as your form tag\'s "method" attribute\'s value.');
}

//Set default settings.
$config['nextpage'] = false;
$config['errorpage'] = false;
$config['sort'] = false;
$config['html'] = false;
$config['longdatetime'] = 'l g:i:s a, F j, Y';

//Set referer.
if(isset($_SERVER['HTTP_REFERER'])) {
	$config['referer'] = $_SERVER['HTTP_REFERER'];
	$config['referer_label'] = preg_replace('#^http(s)?://#i', '',
		$_SERVER['HTTP_REFERER']);
}
else if(isset($_SERVER['SERVER_NAME'])) {
	$config['referer'] = 'http://'.$_SERVER['SERVER_NAME'];
	$config['referer_label'] = $_SERVER['SERVER_NAME'];
}
else {
	$config['referer'] = './';
	$config['referer_label'] = 'Home Page';
}

//Check if a mail recipient is indicated.
if($form_data['sg_mailto'] == '') {
	render_error('No email recipient was specified.  Please use a hidden'
		.' form element named "sg_mailto" with the value set to the email'
		.' address of the form\'s recipient.');
}
else {
	$config['mailto'] = $form_data['sg_mailto'];
}

//Check next page setting.
if($form_data['sg_nextpage'] != '') {
	$config['nextpage'] = $form_data['sg_nextpage'];
}

//Check error page setting.
if($form_data['sg_errorpage'] != '') {
	$config['errorpage'] = $form_data['sg_errorpage'];
}

//Check if from is set.
if($form_data['sg_mailfrom'] != '') {
	$config['mailfrom'] = stripslashes($form_data['sg_mailfrom']);
}
else {
	$config['mailfrom'] = $config['referer_label'].' <formmailer@'
		.$_SERVER['SERVER_NAME'].'>';
}

//Check if a subject is set.
if($form_data['sg_mailsubject'] != '') {
	$config['subject'] = stripslashes($form_data['sg_mailsubject']);
}
else {
	$config['subject'] = 'Form Results from '.$config['mailfrom'];
}

//Check if sorting is desired.
if($form_data['sg_sortalpha'] != '' &&
	!preg_match('/(false|no|off|0)/i',
	$form_data['sg_sortalpha'])) {
	$config['sort'] = true;
}

//Check if HTML email is desired.
if($form_data['sg_htmlemail'] != '' &&
	!preg_match('/(false|no|off|0)/i',
	$form_data['sg_htmlemail'])) {
	$config['html'] = true;
}

//Obliterate settings from form data.
$dump_list = array();
foreach($form_data as $k => $v) {
	if(preg_match('/^sg_/', $k)) {
		$dump_list[] = $k;
	}
}
for($i = 0; $i < count($dump_list); $i++) {
	unset($form_data[$dump_list[$i]]);
}

//Sort.
if($config['sort']) {
	ksort($form_data);
}

//Generate email content string.
if($config['html']) {
	$mail_content = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"'
		.' "http://www.w3.org/TR/html4/strict.dtd">'."\r\n"
		.'<html><head><title>'.$config['subject'].'</title>'."\r\n"
		.'<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'
		.'<meta http-equiv="Content-Script-Type" content="text/javascript">'
		."\r\n<style type=\"text/css\">\r\n"
		.'body { margin:10px; padding:0px; }'."\r\n"
		.'div { font-family:Verdana,Arial,sans-serif; font-size:11px; }'
			."\r\n"
		.'table.t { background-color:#000000; width:500px; margin:0px auto; }'
			."\r\n"
		.'tr.t_head { background-color:#0077CC; }'."\r\n"
		.'div.t_head { font-weight:bold; color:#FFFFFF; text-align:center; }'
			."\r\n"
		.'tr.odd { background-color:#FFFFFF; }'."\r\n"
		.'tr.even { background-color:#EEEEEE; }'."\r\n"
		.'div.t_cell_k { font-weight:bold; color:#000000; text-align:left; }'
			."\r\n"
		.'div.t_cell_v { color:#000000; text-align:left; }'."\r\n"
		.'div.footer { width:500px; margin:0px auto; }'."\r\n"
		."</style>\r\n"
		."</head><body>\r\n"
		.'<table cellpadding="3" cellspacing="1" border="0" class="t">'
			."\r\n"
		.'<tr class="t_head">'
		.'<td><div class="t_head">Field Name</div></td>'
		.'<td><div class="t_head">Field Data</div></td>'
		.'</tr>'."\r\n";
	$i = 0;
	foreach($form_data as $k => $v) {
		if(($i++)%2) { $rowclass = 'odd'; } else { $rowclass = 'even'; }
		$mail_content .= '<tr class="'.$rowclass.'">'
			.'<td><div class="t_cell_k">'.strip_underscores($k).'</div></td>'
			.'<td><div class="t_cell_v">'
				.nl2br(htmlentities(stripslashes($v))).'</div></td>'
			.'</tr>'."\r\n";
	}
	$mail_content .= "</table>\r\n"
		.'<div class="footer">Date Sent: '.date($config['longdatetime'])
		."</div>\r\n</body></html>";
}
else {
	$mail_content = "Form Results\r\n==================================\r\n";
	foreach($form_data as $k => $v) {
		$mail_content .= '['.strip_underscores($k).'] '
			.stripslashes($v)."\r\n";
	}
	$mail_content .= "==================================\r\n"
		.'Date Sent: '.date($config['longdatetime']);
}

//Construct mail headers.
$mail_headers = 'From: '.$config['mailfrom']."\r\n"
	."X-Mailer: SG Form Mailer/1.0\r\n";
if($config['html']) {
	$mail_headers .= "Content-Type: text/html; charset=iso-8859-1\r\n";
}

//Send email.
if(!mail($config['mailto'], $config['subject'],
	$mail_content, $mail_headers)) {
	if($config['errorpage']) {
		header('Location: '.$config['errorpage']);
		exit();
	}
	else {
		render_error('An error has occurred while processing your'
			.' information.  Please contact the owner of the web site'
			.' for assistance.');
	}
}

//Redirect.
if($config['nextpage']) {
	header('Location: '.$config['nextpage']);
}
else {
	render_page('Form Processing...',
		'Thank you for completing this form.  Your information is being'
			.' processed.  You may return to the main page at this URL:'
			.' <a href="'.$config['referer'].'">'.$config['referer_label']
			.'</a>');
}

//Bail.
exit();

//Strips underscores from field names.
function strip_underscores($input) {
	return(str_replace('_', ' ', $input));
}

//Renders an error page.
function render_error($message) {
	render_page('Form Processor Error', '<div style="color:#CC0000;">'
		.$message.'</div>');
	exit();
}

//Renders a form for debugging purposes.
function render_debug_form() {
	render_page('Debug Form', '<form action="'.$_SERVER['SCRIPT_NAME'].'"'
		.' method="post">'
		.'<table>'
		.'<tr><td>Mail To</td><td><input type="text"'
			.' name="sg_mailto" /></td></tr>'
		.'<tr><td>Next Page</td><td><input type="text"'
			.' name="sg_nextpage" /></td></tr>'
		.'<tr><td>Error Page</td><td><input type="text"'
			.' name="sg_errorpage" /></td></tr>'
		.'<tr><td>Sort</td><td><input type="text"'
			.' name="sg_sortalpha" /></td></tr>'
		.'<tr><td>HTML Email</td><td><input type="text"'
			.' name="sg_htmlemail" /></td></tr>'
		.'<tr><td>Mail Subject</td><td><input type="text"'
			.' name="sg_mailsubject" /></td></tr>'
		.'<tr><td>Mail From</td><td><input type="text"'
			.' name="sg_mailfrom" /></td></tr>'
		.'<tr><td>Some Data</td><td><input type="text"'
			.' name="Some Data" /></td></tr>'
		.'<tr><td>Some Data 2</td><td><input type="text"'
			.' name="Some Data 2" /></td></tr>'
		.'<tr><td>Some Data 3</td><td><input type="text"'
			.' name="Some Data 3" /></td></tr>'
		.'<tr><td>&nbsp;</td><td><input type="submit" />'
		.'</td></tr></table></form>');
	exit();
}

//Renders a basic page (if one is not supplied).
function render_page($title, $content) {
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Cache-Control: no-cache, must-revalidate');
	header('Pragma: no-cache');
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
	"http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title><? echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<style type="text/css">
body {
	color:#000000; background-color:#FFFFFF; margin:10px; padding:0px;
}
div,span,a,p,li,ol,ul,h1,h2,h3,h4,h5,h6 {
	font-family:Verdana,Arial,Helvetica,sans-serif;
}
div,span,p,li,ol,ul {
	color:#000000; background-color:transparent;
	font-size:11px; font-weight:bold;
}
h1,h2,h3,h4,h5,h6 {
	color:#0077CC; background-colot:transparent;
	margin:12px auto 1px auto; padding:0px;
}
h1 { font-size:24px; }
h2 { font-size:20px; }
h3 { font-size:16px; }
h4 { font-size:14px; }
h5 { font-size:12px; }
h6 { font-size:10px; }
a:link, a:active, a:visited {
	color:#0077CC; background-color:transparent;
	text-decoration:underline;
}
a:hover {
	color:#0D9AFF; background-color:transparent;
	text-decoration:underline;
}
#body {
	width:450px;
	margin:0px auto;
}
#title {
	font-size:20px;
	color:#0077CC; background-color:transparent;
}
</style>
</head>
<body>
<div id="body">
<div id="title"><? echo $title; ?></div>
<div id="content">
<? echo $content; ?>
</div>
</div>
</body>
</html><? } ?>