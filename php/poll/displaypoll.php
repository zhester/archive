<?php
/****************************************************************************
	Poll Display Script
	Zac Hester - 2005-05-24
	
	This script is intended to be "include()d" from another script to
	display a small box poll.
	
	Requirements/Dependencies:
	-A session (long-life cookies) started in HTTP headers.
	-MySQL database connection started in parent script.
	-Style sheet definitions based on the root element: class="pollbox"
	-Configuration of target links in this script.
****************************************************************************/

//TESTING CODE
session_start();
mysql_connect('localhost','','');
mysql_select_db('');
///////////////

$poll_conf = array(
	'history' => 'poll_history.php',
	'processor' => 'poll_process.php'
);

//Fetch the specified poll.
if($_REQUEST['poll_id']) {
	$poll_id = $_REQUEST['poll_id'];
	list($poll_title,$poll_posted) = mysql_fetch_row(mysql_query(
		'select title,posted from zz_pl_polls where id = '.$poll_id));
	if(!$poll_title) { $poll_title = 'Unable to fetch poll.'; }
}
//Fetch the latest poll.
else {
	list($poll_id,$poll_title,$poll_posted) = mysql_fetch_row(mysql_query(
		'select id,title,posted from zz_pl_polls where suspended == 0'
		.' order by posted desc limit 1'));
}

echo '<div class="pollbox><h4>'.$poll_title.'</h4><h5>Started: '
	.date('M j, Y',$poll_posted).'</h5>';

if(isset($_SESSION['poll_message'])) {
	echo '<h6>'.$_SESSION['poll_message'].'</h6>';
	unset($_SESSION['poll_message']);
}

//If we've voted, display results.
if(isset($_SESSION['poll_vote_'.$poll_id])) {
	$poll_res = mysql_query('select id,title,votes from zz_pl_options'
	.' where poll_id = '.$poll_id.' order by rank');
	list($poll_total,$poll_max) = mysql_fetch_row(mysql_query(
		'select sum(votes),max(votes) from zz_pl_options where poll_id = '
		.$poll_id));
	echo '<ul>';
	$poll_li_num = 0;
	while($poll_rec = mysql_fetch_assoc($poll_res)) {
		$poll_option_fraction = round(($poll_rec['votes']/$poll_total),3);
		echo '<li class="option_'.$poll_li_num
			.'" style="background-position:-'
			//This tells how far to extend the background image.
			.(100-(round($poll_rec['votes']/$poll_max,2)*100))
			.'% 0%;"><span>'.$poll_rec['title'].' '
			.($poll_option_fraction*100).'% ('
			.$poll_rec['votes'].')</span></li>';
		++$poll_li_num;
	}
	echo '<li class="total">Total Votes: '.$poll_total.'</li></ul>';
}
//We haven't voted, so display vote form.
else {
	$poll_res = mysql_query('select id,title from zz_pl_options'
	.' where poll_id = '.$poll_id.' order by rank');
	echo '<form action="'.$poll_conf['processor'].'" method="post"><ul>';
	while($poll_rec = mysql_fetch_assoc($poll_res)) {
		echo '<li><label><input type="radio" name="vote" value="'
			.$poll_rec['id'].'" onclick="submit();" /> '
			.$poll_rec['title'].'</label></li>';
	}
	echo '</ul></form>';
}

echo '<p><a href="'.$poll_conf['history'].'">View Past Polls</a></p></div>';
?>