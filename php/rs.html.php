<?
/****************************************************************************
	HTML Resource Script
	Zac Hester - 2004-09-16
****************************************************************************/

//Print the page's header.
function print_head($title = '') {
	if($title) { $pagetitle = ' - '.$title; }
	else { $pagetitle = ''; }
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Alex Johnson Hotel<?=$pagetitle?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta name="author" content="Zac Hester" />
<meta name="robots" content="all" />
<meta name="revisit" content="14 days" />
<meta name="description" content="A Historic Hotel located in downtown
	Rapid City.	Close to the Black Hills, Deadwood, Sturgis, Crazy Horse
	Monument and Mount Rushmore. Walking distance to Rapid City Convention
	Center and The Journey Museum." />
<meta name="keywords" content="Alex Johnson, hotel, motel, lodging, south
	dakota, blackhills, rapid city, midwest, dakota, sioux, lakota,  mount
	rushmore, crazy horse, badlands, native, american, indian, historic,
	register, culture, history, landmark, restaurant, Paddy O'Neill's,
	pub, Pahasapa, dakota territory, dakota country, state parks,
	national monuments, national parks, Rushmore, Mt. Rushmore, Borglum,
	Crazy Horse, Badlands, caves, antiques, collectibles, RV parks, lakes,
	hiking, skiing, swimming, boating, vacation, snowmobiling, gold,
	goldmine, homestake, blackhills gold, rodeos, fairs, stock show,
	motorcycle rally, sturgis, sturgisrally, gambling, casinos, buffets,
	historical landmarks, western, western art, indian, indian art,
	Sioux, sioux art, reptile gardens, stock show, journey" />
<link href="/style.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="header"><img src="/imgs/coinhead.gif" style="width:456px; height:59px;" alt="">
<h3>Historic</h3>
<h1>Hotel Alex Johnson</h1>
</div>
<div id="content">
<?
}

//Print the page's footer.
function print_foot() { ?>
</div>
<p>523 Sixth Street, Rapid City, SD 57701<br />
605-342-1210, 800-888-ALEX(2539)<br />
E-mail: <a href="mailto:info@alexjohnson.com">Info@AlexJohnson.com</a></p>

<ul class="flat_list">
	<li>[ <a href="/hotelinfo.html">Information</a> ]</li>
	<li>[ <a href="/history.html">History</a> ]</li>
	<li>[ <a href="/areaattractions.html">Attractions</a> ]</li>
	<li>[ <a href="/mercantile.html">Mercantile</a> ]</li>
	<li>[ <a href="/packages.html">Packages</a> ]</li>
<!--	<li>[ <a href="/contact.php">Contact Us</a> ]</li>-->
</ul>

<div><img src="/imgs/exlgborder.gif" style="width:461px; height:31px;" alt="" /></div>

</body>
</html><? } ?>