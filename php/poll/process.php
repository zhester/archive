<?
session_start();
mysql_connect('localhost','hbomb','iluvshannon');
mysql_select_db('planetzac');

//Handles voting.
if($_REQUEST['vote']) {
	if(!isset($_SESSION['poll_vote_'.$id])) {
		mysql_query('update zz_pl_polls set votes = (votes+1) where id = '
			.$_REQUEST['vote']);
		list($id) = mysql_fetch_row(mysql_query(
			'select poll_id from zz_pl_options where id = '
			.$_REQUEST['vote']));
		$_SESSION['poll_vote_'.$id] = true;
		$_SESSION['poll_message'] = 'Thanks for voting.';
	}
	header('Location: '.$_SERVER['HTTP_REFERER']); exit();
}

else if($_REQUEST['id']) {
	$p = parse_poll($_REQUEST['poll']);
	$np = count($p);
	mysql_query('update zz_pl_polls set title = \''.$p[0].'\' where id = '
		.$_REQUEST['id']);
//////////// DECIDE if we delete old options and replace
//	for($i = 1; $i < $np; ++$i) {
//		mysql_query();
//	}
}

else {
	$p = parse_poll($_REQUEST['poll']);
	$np = count($p);
	mysql_query('insert into zz_pl_polls (posted,title) values ('.mktime()
		.',\''.$p[0].'\')');
	$newid = mysql_insert_id();
	if(!$newid) { die('oops, no new id'); }
	for($i = 1; $i < $np; ++$i) {
		mysql_query('insert into zz_pl_options (poll_id,title,rank) values ('
			.$newid.',\''.$p[$i].'\','.($i-1).')');
	}
	$_SESSION['message'] = 'poll added';
}

header('Location: managepoll.php');

function parse_poll($input) {
	if(get_magic_quotes_gpc()) { $input = stripslashes($input); }
	$input = str_replace("\r", '', $input);
	$comps = explode("\n", $input);
	$ncomps = count($comps);
	$final = array();
	for($i = 0; $i < $ncomps; ++$i) {
		$comps[$i] = trim($comps[$i]);
		if($comps[$i]) { $final[] = addslashes($comps[$i]); }
	}
	return($final);
}


?>