<?php


echo '<h4>Testing get_size()</h4><p>';
$tests = array(-8,0,1,10,100,1000,10000,345094,3948304985,4,569.138);
foreach($tests as $test) {
	echo $test.' --> '.get_size($test).'<br />';
}
echo '</p>';


echo '<h4>Testing get_plural()</h4><p>';
$tests = array('phone','daisy','octopus','church','key',
	'stereo','potato','synopsis','piano','self','knife','SUV','8');
foreach($tests as $test) {
	echo $test.' --> '.get_plural($test).'<br />';
}
echo '</p>';


echo '<h4>Testing get_timeago()</h4><p>';
$tests = array(
	mktime(date('H'),date('i'),date('s')-10,date('n'),date('j'),date('Y')),
	mktime(date('H'),date('i')-13,date('s'),date('n'),date('j'),date('Y')),
	mktime(date('H')-5,date('i'),date('s'),date('n'),date('j'),date('Y')),
	mktime(date('H'),date('i'),date('s'),date('n'),date('j')-3,date('Y')),
	mktime(date('H'),date('i'),date('s'),date('n')-2,date('j'),date('Y')),
	mktime(date('H'),date('i'),date('s'),date('n'),date('j'),date('Y')-27)
);
foreach($tests as $test) {
	echo $test.' --> '.get_timeago($test).'<br />';
}
echo '</p>';


echo '<h4>Testing num2text()</h4><p>';
$tests = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10',
	'11', '20', '21', '50', '100', '101', '152', '352', '1000',
	'-1001', '1020', '1021', '1432', '-20000', '30123',
	'46345', '100000', '600345', '2,543,876',
	'5 481 168 651 567 683 189 873 518 798', '023400000004',
	1.25e10);
foreach($tests as $test) {
	echo $test.' --> '.num2text($test).'<br />';
}
echo '</p>';


?>