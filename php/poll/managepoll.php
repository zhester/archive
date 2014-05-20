<?
session_start();
mysql_connect('localhost','','');
mysql_select_db('');
if(isset($_SESSION['message'])) {
	echo '<p>'.$_SESSION['message'].'</p>'; unset($_SESSION['message']);
} ?>

<? if($_REQUEST['form'] == 'addpoll' || $_REQUEST['form'] == 'editpoll') { ?>
<form action="process.php" method="post">
<? if($_REQUEST['form'] == 'editpoll') {
	list($poll) = mysql_fetch_row(mysql_query(
		'select title from zz_pl_polls where id = '.$_REQUEST['id']));
	$res = mysql_query('select title from zz_pl_options where poll_id = '
		.$_REQUEST['id']);
	while($rec = mysql_fetch_row($res)) {
		$poll .= "\n".$rec[0];
	}
?>
<input type="hidden" name="id" value="<?=$_REQUEST['id']?>" />
<? } ?>

<p><textarea name="poll" rows="8" cols="50"><?=$poll?></textarea></p>
<p><input type="submit" value="Do It" /></p>
</form>
<? } else {
	$res = mysql_query('select id,title,posted from zz_pl_polls'
		.' order by posted desc'); ?>
<table>
<? while($rec = mysql_fetch_assoc($res)) { ?>
	<tr>
		<td><a href="managepoll.php?form=editpoll&amp;id=<?=$rec['id']?>"><?=$rec['title']?></a></td>
		<td><? echo date('H:i F j, Y', $rec['posted']); ?></td>
	</tr>
<? } ?>
</table>
<p><a href="managepoll.php?form=addpoll">Add New Poll</a></p>
<? } ?>