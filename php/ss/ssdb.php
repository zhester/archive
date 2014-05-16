<?php
/****************************************************************************
	Single Script Database Interrogator
	Zac Hester
	2007-11-30

	Quick, zero-install script that will allow you to interrogate any
	database to which you have access.

	Features:
		- Zero configuration.  DB credentials used in session.
		- Browsable table listings with table statistics.
		- Field listings with table creation query.
		- Timestamp translation and encoding/decoding tools.
		- Basic query interface, with editing existing set query.
		- Smart blob-type detection and display.

	TODO:
		- dynamic anchor event handler
			- timestamp tooltip
			- AJAX blob loading
		- advanced query
		- CSV export
			- link on all query pages for current query
		- edit links (when a primary key is found)
		- delete checkboxes (when a primary key is found)
		- large results paging
		- drop table "where" emulation
		- result set sorting
		- backup/restore utility
****************************************************************************/

/*---------------------------------------------------------------------------
	Configuration
	(You probably don't need to mess with it.)
---------------------------------------------------------------------------*/
$CONF = array(
	'script' => $_SERVER['SCRIPT_NAME'],
	'version' => '0.0.0'
);


/*---------------------------------------------------------------------------
	Non-HTML Output
---------------------------------------------------------------------------*/
if($_REQUEST['resource'] == 'stylesheet') {
	header('Content-Type: text/css');
?>
body {
	margin: 0;
	padding: 0;
	font: 10pt/130% Arial,Helvetica,sans-serif;
	color: #000000;
	background-color: #FFFFFF;
}
a {
	color: #0000CC;
	background-color: transparent;
}
a:hover {
	color: #CC0000;
	background-color: transparent;
}
h1, h2, h3, h4, h5, h6 {
	margin: 0.75em 0 0.25em 0;
	padding: 0;
}
h1 { font-size: 150%; }
h2 { font-size: 130%; }
h3 { font-size: 120%; }
h4 { font-size: 110%; }
h5 { font-size: 110%; }
h6 { font-size: 100%; }
p, ul, ol, blockquote {
	margin: 0.75em 0;
	padding: 0;
}
ul li, ol li {
	margin: 0;
	margin-left: 2em;
}
blockquote { padding: 0 2em; }
img { border: none; }
form { margin: 0; }

input[name=query] {
	font-family: "Courier New",Courier,monospace;
	color: #000000;
	background-color: #EEEEFF;
	border: solid 1px #CCCCCC;
}

.clear {
	clear: both;
}

table.data {
	width: 100%;
	margin: 0.75em 0;
	padding: 0;
	border-collapse: collapse;
}
	table.data tr.header th {
		color: #FFFFFF;
		background-color: #666666;
	}
	table.data tr.even td {
		color: inherit;
		background-color: #FFFFFF;
	}
	table.data tr.odd td {
		color: inherit;
		background-color: #FFFFEE;
	}
	table.data tr td, table.data tr th {
		margin: 0;
		padding: 2px 5px;
		border: solid 1px #CCCCCC;
	}

table.stats {
	margin: 0.75em 0;
	padding: 0;
	border-collapse: collapse;
	float: left;
}
	table.stats th, table.stats td {
		margin: 0;
		padding: 0 4px;
	}
	table.stats th {
		text-align: right;
	}
	table.stats td {
		text-align: left;
	}

p.subtext {
	color: #666666;
	background-color: transparent;
}

p.query {
	padding: 1px 3px;
	border: solid 1px #CCCCCC;
	color: #000000;
	background-color: #EEEEEE;
}

form.page {
	margin: 0.5em 0;
	padding: 3px 7px;
}
	form.page h2 {
		text-align: center;
	}
	form.page p {
		text-align: center;
	}

div#page_root {
	margin: 3px 7px;
	padding: 0;
}
div#header {
	height: 100px;
	padding: 3px 7px;
	background-image: url(<?php echo $CONF['script']; ?>?resource=ss_logo);
	background-position: right 50%;
	background-repeat: no-repeat;
	background-color: #FFFFFF;
	text-align: center;
}
ul#nav {
	list-style: none;
	margin: 1em 0 0 0;
	padding: 1px 7px;
	text-align: center;
}
	ul#nav li {
		display: inline;
		margin: 0;
		padding: 3px 7px;
		border-left: 1px solid #999999;
	}
	ul#nav li.first {
		border: none;
	}
		ul#nav li a {
		}
		ul#nav li a:hover {
		}
div#primary {}
div#quick_query {
	text-align: center;
}
	div#quick_query form {
	}
		div#quick_query form input[type=text] {
			width: 80%;
		}
div#message {
	margin: 0.5em 0;
	padding: 3px 7px 3px 45px;
	border: solid 1px #666666;
	background-image: url(<?php echo $CONF['script']; ?>?resource=exclame);
	background-position: 7px 50%;
	background-repeat: no-repeat;
	color: #660000;
	background-color: #FFFFCC;
	font-weight: bold;
}
div#footer {
	font-size: 85%;
	color: #666666;
	background-color: transparent;
	text-align: center;
}
div.tools {
	margin: 0.75em 2em;
	text-align: right;
}
	input#ts_stamp {
		width: 80px;
	}
	input#ts_long {
		width: 280px;
	}
<?php
	exit();
}
else if($_REQUEST['resource'] == 'script') {
	header('Content-Type: text/javascript');
?>
/*
 * Page Initialization
 */
function init() {
	ts_init();
	init_results();
}


/*
 * Results Table Initialization
 */
function init_results() {
	var tbl = document.getElementById('results');
	var rows, nrows, spans, as, idname;
	if(tbl) {

		//Scan table rows.
		rows = tbl.getElementsByTagName('tr');
		nrows = rows.length;
		for(var i = 0; i < nrows; ++i) {

			//Header row.
			if(rows[i].className == 'header') {
				spans = rows[i].getElementsByTagName('span');
				if(spans && spans.length) {
					//ID field name.
					idname = spans[0].innerHTML;
				}
			}

			//Footer row.
			else if(rows[i].className == 'footer') {
			}

			//Data rows.
			else {
				spans = rows[i].getElementsByTagName('span');
				if(spans && spans.length) {
					//ID.
					recid = spans[0].innerHTML;
				}
				as = rows[i].getElementsByTagName('a');
				if(as && as.length) {
					for(var j = 0; j < as.length; ++j) {
						if(as[j].hash) {
							bits = as[j].hash.split(':');
							if(bits[0] == '#tt') {
								as[j].onclick = function() {
									alert('hi');
////// set up a good handler to do neat stuff
									return(false);
								}
							}
						}
					}
				}
			}
		}
	}
}





/*
 * Timestamp Utility Functions
 */
function ts_enc() {
	var src = document.getElementById('ts_long');
	var tgt = document.getElementById('ts_stamp');
	tgt.value = Math.round((Date.parse(src.value))/1000);
}
function ts_dec() {
	var src = document.getElementById('ts_stamp');
	var tgt = document.getElementById('ts_long');
	var dt = new Date(src.value*1000);
	tgt.value = dt.toLocaleString();
}
function ts_init() {
	var stamp = document.getElementById('ts_stamp');
	if(stamp) {
		var dt = new Date();
		stamp.value = Math.round(dt.getTime()/1000);
		ts_dec();
	}
}

<?php
	exit();
}
else if($_REQUEST['resource'] == 'ss_logo') {
	$php_array = array(
		'type' => 'image/png',
		'size' => '4287',
		'name' => 'ss_logo.png',
		'data' =>
		'iVBORw0KGgoAAAANSUhEUgAAAIAAAABkCAMAAAB5NchtAAAABGdBTUEAAK/I'
		.'NwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAGA'
		.'UExURenp6dzc3D09PdbW1jk5OWNjY3t7e8jIyC0tLdra2uzs7N1dXbd4eOrq'
		.'6tDQ0NLS0uXl5eoqKpubm9/f3zIyMmVlZcXFxdjY2KioqM7OzrKyssrKypOT'
		.'k3R0dEZGRhwcHCkpKdSUlKGhobq6ura2tkpKSqWlpeJhYcDAwMLCwstLS66u'
		.'rrS0tLy8vL6+vouKiuJSUgkJCdNUVCMjIxMTE1ZWVrCwsIODg1JSUsTExE5O'
		.'Tri4uNl5ecuLi8KCguZmZllZWQQEBOuLi11dXdXFxdSkpDU1NdlpaeYmJtOz'
		.'s8pqaqurq9YxMa2ZmWtra6uLi91FRexsbLmpqbhoaOGhoWBgYOY5OUJCQu0d'
		.'HcS0tNSEhPQUFPoKCsd3d/AgIEBAQONzc8SUlLyMjPr6+vz8/Pn5+fT09Pf3'
		.'9/j4+O7u7l9fX9TU1Pb29vDw8PLy8vX19ebm5vPz8+Hh4ePj4+/v7+Dg4Ofn'
		.'59XV1eLi4v39/c3NzfHx8f7+/vv7+/8AAP///yxaHb8AAA7FSURBVHja7Jv7'
		.'d9rGtselKraORJCElYB5JE0QxmAebrmJHeLE6T19xA2tk542jzZuTlvxrADz'
		.'fjP51++ekQRCRraVe9e9v9zvWl4LhPbsj7ZGM3v2yNTH/2NR/w9g/TKbjcfF'
		.'+bVUHM9mtqZcWI+X1pTVfl6qnJebzemVapbr1fl4tuK9OK9ez7rZLFdKC+sF'
		.'wGysVso9nmpJ3dGVkgb8tK4uCcC9Wm/WeEq6hnVXovhmZW4DmM2r5dqgzzGi'
		.'EJxcpaDA0K1e3WwD4OeVJi/J3FC4jrXIyFSzWlwBmM0rU0oeTgqRyOb1lGxN'
		.'q+NF8M5rEi3m45H96xkf0lRZnVkAwH9P4vyR09vX1hbN1w0A8M+PNOXw+sa3'
		.'M6Ne1QIwG1enEpM+3XChQ61Vnpv0tY6w5cZ4Y5sz8XWAeZni0rddNbEZHE1V'
		.'PQClqaRFXBlvRLRBubgEGFdrsv/UXRMbYblWmhH6cy+Xcmm8KUjN+RIAAsBE'
		.'XrtURq6R2zhTp13hjkvjO0J3ugSAJkaT079dKtTXAcZVnlbcGv8tSk11CQBN'
		.'hL62aePOJdqAE7a6ekcen0P47Na3L7PGJySptj6MYIBxxRvYsrcQUvJhh5Ek'
		.'zLJJGDC8bfIoz8uSsGmz3lSy7CXWmehhgj+3AMA17H+1qkg2yMmdtUNppy/T'
		.'dL/rnerD6bzZDd6xWaeSAt0fOVrLfYlvV5dPwfh8oNkBomGa6jnMJo1erVbr'
		.'tSv6ZKA2+uHbNmtF6PONy6ynZZjMFgDFckvcf7iqKPSSksNkqqqlarWqGhOa'
		.'2pPDNuOHSY46Vx2tqxZrR4A4Q5kj7RrNdH00AFg7gJ+ulT5ey3oBcPhoVXHG'
		.'W5ldK6VRazRrM37EXgpwISNaBxC9NAJXAfg/AeDxqqAPTKvXSsYwgM34cTIA'
		.'0+16Y3sq5wSwBU9BbV0+1YZsTC1a0rF1AIogexvrkrH2eaU6L1oZFgA7qzrM'
		.'TgLrxoGu1AKw82VORwBsxjtxh3EAZ2O1ZkW1IDgBPCysHwmDgsjQMAgtM8J1'
		.'AKdK1h9eZyxonCzhjHA8swPctctxJo3EBK7Ll00CDHDB+LGD8enhVlIk+eTY'
		.'9hhu3nWhjbgoYwJngMt054jp1upzO8BbV7ojyOZsggHcGe9EtG6vUpz9dwCA'
		.'ANLKsTkSujR+G2FaTdUG8JlLRRiqTEKAAdwa3z2i+cp4ZTZ0DbAz6fRK5my4'
		.'49b6VGu1V/KB4eYNt9ri9IuYQz73lVvjt+G+jr/IiPZdA9wxMut5WxJcA9yI'
		.'GviLnLDwnVs9FLoks8b51KZr602GOl8CqI3O5LNbLvVZuEMy63EF8N0a3zoV'
		.'W/rKxFgXDIZ3XLcRlnuqkdQH37o1fmSuTPSVUYWXE9/9w6VYA2B+TnERt8aP'
		.'gl0LwExtQ27vGoCuqfrCrtEVNlwaP56MpnPr6rjX1fZv3XMlEwBCwMvCa3fG'
		.'qwDQRr3WZdJ/fxLAx7FaBoLIWzfGO6sAH8fzeq9Fi+nD2zvr9e//uKAFwAwT'
		.'dDlhe/ORg/WNC8Z2ALPKE9DWFnnC/uzBV44ApEbToEa0Q4EpnFfi39mM79oA'
		.'zDqXd7CuzNXpc0Iy5QyA+avnU4cSW0cOTLKHVwHoCNV6ub2u0NfwyoLyuU0W'
		.'AIKgVh3KhDWKDkdsxmsA9Frl+lKnWqYCyUsBLiuUVpuSeD0AZ5VqtP8KAGeN'
		.'6zDK2IzfugTAedcnA8wq3v8RgAc2uQEIRGzG//5fBmDsAJ/ZRkLoQcsVs0Mf'
		.'cAQwrC/vA5cCQOcnVYOS6lBVUGHCS14A0GdD/QnENQenkkQJPwV2gNXZEA8j'
		.'NVx3carx830hYwcw8wEYhMoNbO1UlOnBOLBvM76xmg+UyrVWB5eeOg51KVrM'
		.'p75c1YOJnhHNilUYxPs0LTtYd2QumDi0WX+1khEVKzX21macZcNhp9JaQtmw'
		.'NXHDyAln8zJ/+PAwdIm1P5O6YbO+bc0JP87PvTQ++vklWeQ9WwtfPjSy4pna'
		.'bMXxgXuXWD+wW0esWTGpFdsRr5TZBKS03bxb4wcr6wIAaGk7Lpv4fNJp6NVy'
		.'nFO7BdjAuw1LgGJ5oL3+T3faD1BtlQweamM0ueXO+EFWtq4NYaSAsdJdEzuw'
		.'OoYVNgGYSsJdl/QM1bbuGc2qNTnkqoXXgsy3S8UxltoeMBtujL/cH0qN6tgK'
		.'ABehuWjhRlSQqWlFLRJBQkhvuXD/OsNIPdu+IU6sH9+6Rjp/797Drw+VINPx'
		.'TuslY+hV6z0pc+s61v+48fVpJCzSUq0+H69s3Y5Lbb4TEIN40EhkMkkYU8Jh'
		.'9oKyf/leKeEhJ7f4ablSLenC2eCIEyfsWvkTitEeDFOCMORomm5NS+PVveNx'
		.'tY1HU47jaO1b3xdBhmOIhgwcYoYa1pBhwk+RLzHsd6QWxddqzfZ5najc8HZp'
		.'jsGnBALG2br1cMiIYd8XEzgeYALQVl9q9b/54xepWVVX+kCpJ8ngPhCgZe77'
		.'3SciTrBFURQEje7LAUGXGBAUAGBonQsX/KZtULMm0UN8sqBxNKfp5zI0PRSC'
		.'QWGohXF7nCaK2lAbct0BfR95hD7VqFoew+JU/mP3GdZPv9Dfe54I2q7n+e6z'
		.'3V3P7jeB4X2PZxf01HN/kgEA4Vv9u+f5N32+AeIl8bl+6NkvAeEJ+fjs2ffC'
		.'7pMnnt1n35P2/tj9DY7BD9/Iv7xCPs+zn/7ZtmzblVp/oPceAvBP7j7ysSxC'
		.'b8DA40Nv/Mkz9AX2d4Y8foiAkniP3oAPzwf0SujyoBbzHL3PEbe/BX9DZ+TT'
		.'7rd+hN77fGf3f8Xt+dAHDADtCfcNAMu+4azS96DfY0mYz0R6iO9z9gP6V5hh'
		.'2H8hX+wYvTkKixPlFcplYsiXTqN320o+GfsR+fJ0C9Sf5NDP8bSiZPyTZA69'
		.'yEwEUQuIiXfox8REFHDUsj5oj6OD0F44+BTlWJqCYcwEmM0qHQ/6cPwU9K0o'
		.'AEBG8SFF645EcFg4Rjm478NEDuViIeRLFdBJNKQoB1HkyzIdEM3m0LubN4+P'
		.'j/9MKDl07A9AR5eZjA+FJrSsAYAC7WUClJdOQDQE6AMaXy8ZFXcCUBo8QYZ+'
		.'8wOAcuRD2cCgxSjoZPsmyiUDfQYDhFLwfRudnfhAJwAg4M7IsC9M699TAMBy'
		.'siz3NXB6pEkSAwCxmA8l5F6j4wcAEQC48tjMIimcUs6bfehZHk/uA/JkjnUD'
		.'ZUgicBIFgIzIicoe2itso5N4FL3fyxG9SAr4iRPDmb9ye6B3aC+6h44TIsYS'
		.'YicoLciycIR8oTREgON5OWlEINAsmgQUeXekTHFB1p8M/YhyRwCQDvlQTJDp'
		.'YIgA7BUS/kQBAMD5SRT+ItFtUCqW1FMgNhsqxOPRrX20t7WHbqYSfj/LJlMn'
		.'KBXmuAm0kTqAzyx8jukAObbD411HKwCtCRNWeYE8Co5A6AzFghwXDqF30Zfo'
		.'3Y+pUGHrB7i+lwTg7OZWfDu+XUhn88ZYlypsb8e3fgaAHPohmgqF0kps+wyc'
		.'ahqL+w3AvIj52czvyDdh7qMPvwq0NC3pADipL01/WukDCQWhzITRwjGECoUT'
		.'87c9iAb0gVfm9zexTALrKGce+Tn+8sz4+HwboZBfENg/0fsQALzXDz8JyOIH'
		.'8kmuk+0jCi9q1TYlB4aiMAlPREZg2QmbSLBBUQgnYqBCdCsK8YVbEC0UCqHQ'
		.'dlRXPAWPHlYstW0eCcHHeHx7u3CQDh2EMv4wNKVkMgCwlU4m83mW6VIjBg+U'
		.'UqO0BMDVkYHU7UDv7fRpGM7xtCQE8cyUyB4BAGj/B7g+iG0sFkoVdKVCMaLQ'
		.'gX4kdRDCXw5CoBiAZRJ+fIf8bBL6QCEBQzXe7eF5ajCg+AbeNDEBirC0qZy3'
		.'pzX4TYL1AUwsuNgC86E/n419OCFC6GWB+Eun06E1gsPk56MjHJVMJgs3J5kH'
		.'hPDED0/VnzAmdCTwXJvirTPV3MEzAErVernZAIBWF5YY+HZg/35/MpmJ/XVT'
		.'10uIsOn6AJRaCH9bYBAIEyGZhADAquLp018Fju538TTawO9hlZwApFFfDwC5'
		.'/mQykVFCuI+DIOSG89RaGRBpMwwmAX5S8v6JCAAjyQYwuxoACBQSeHx1nwiA'
		.'UxGRuR7AhVsA3TADDMqRfvs/6Rbg3cZhgL7sFjh3QiAwEJSjo0/shOBfgwBc'
		.'1gnVEgbo8V5K6vaXBBiBRIFAZPTH3uC4IN0z9k2c61ePV6ym/35Xorx8DwOU'
		.'LgKY92AAvQATkCRrQhgAIp/UMRLZLCYxWZYiB+HHhO4aLh13Pj0RFcUh8T8i'
		.'AdDvgBVgNi4a98Ag6OJSAaSlGmbAmbKeIftBeUxiwqxIP57P47NY4jtMNosh'
		.'F2TAPcS/a/g37kBRnw6pj8sQkJvAU1SLBAGSVEhsNZKbGq8Jrk/WrdKzb+IZ'
		.'+xZxLs1h93D5LYriyQ2wBsAEIL2gXm5PIQZeCIKkv6yDE/UATrA1DCIKpoJE'
		.'i01xosWP+EySxZNEnCalk5EEl++F65+2y3XSA6wAeo0VE+C7AEHw4ihIXQwh'
		.'YwwCAkk7Y6wWiDSL9CPGzwHslyNmODXqjLoSvnqYBHo4/sT/3JKQmAQqIcBB'
		.'6NUIw6AFFLj83en0MYkJsxTHLVwtpZ/ZJwUjcC21WgPivdbDl0/8q0v/BoAZ'
		.'gypBaOoMhAI4AASTYBiQUXi6IOMVCZB+LhjBpEcR37r3JnFfXV7/okBhJdAR'
		.'MEOj16sRDEwCokwNTLUMLQ4sTsHnG6a1Wq/XwN5193b/xtJsZvREQIAoAAOB'
		.'IMX/BibBb18taK4UObmH/TbImyvEOXjHy1ns3up/WaolBEXynhaOQ6UOFOdl'
		.'EFn+NZvWrYjGWlnfliFewS8Imqlj59g7cb/if7FnRAj0KBAG/LYYYGAQrHND'
		.'ZavahlYOmmfqdrgFUgTWvZOrX/FvebN6gWBAYOnrf0MVu+r1pZcVmRbEXG+J'
		.'OL/ofqVcPzMZxnrpZfH2m10lB10803x/qmg6H8/sRXlqzWtuY1NFi+auZLVc'
		.'NLfyFp3T/xfMrBo7qbhGjievNHmtf3CYXakr/azV2t2M/xJgAOCXgCcw/XU2'
		.'AAAAAElFTkSuQmCC'
	);
	header('Content-Type: '.$php_array['type']);
	header('Content-Length: '.$php_array['size']);
	header('Content-Disposition: inline; filename="'.$php_array['name'].'"');
	echo base64_decode($php_array['data']);
	exit();
}

//Warning icon.
else if($_REQUEST['resource'] == 'exclame') {
	$php_array = array(
		'type' => 'image/png',
		'size' => '1022',
		'name' => 'exclame.png',
		'data' =>
		'iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAAAGXRFWHRTb2Z0'
		.'d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAZhQTFRFmwsLpRUVoxMTmgkJ'
		.'syMjlQUFuikpmAgIkwMDtSUlDw8PlgYGuysrtyYmkgICuCgovCwskQEBkAAA'
		.'vS0tb29vdXV19OHhKAEBnRYWBgYGkAUFNQwMsygomRISDAwMnxgYkQYGsSUl'
		.'CQkJkwgImxQUjgMDsicnIgAAJAICMgsLNAsLtCkptSoqoxsbNQsLpR0dlg4O'
		.'v0JCGxsbiwoKyHt7sSQkKgICtisrOA0NuSwsMAsLHh4elQkJuCsrjgEBiQAA'
		.'jQAAvUBAqyUlJAAAjwICLQMDx15enB0dLQkJJQEBlAoKyn19igEBy2FhryIi'
		.'KgAALAsL6sPDszY2yXx8lAwMtEFBx3p6t0REt05ONwsLh4eHhISExW9vjQIC'
		.'ryQkxG5uwUVFmBAQKQAAtDg4LwoK1ZubtyoqyV9frzIyoRkZwmtrw21tKQEB'
		.'KggIrSIilgkJKwICymBgLgsLuU9PqyAgz4yMwUNDJgEBNw0NwGBgvFJSoRER'
		.'nw8PqRkZnQ0NpxcXcnJyqxsbrx8frR0dsSEhAAAA////////CER6OgAAAIh0'
		.'Uk5T////////////////////////////////////////////////////////'
		.'////////////////////////////////////////////////////////////'
		.'////////////////////////////////////////////////////////////'
		.'////ABi31g8AAAFoSURBVHjahNNVU8NAFIbh4u5boEodWtzd3d3d3Z0KB2n5'
		.'2+REdjcpHd6rfJPnIjPZ1f38k44+6a1hmoFEA31pdz6tZ5poAZRO5HF17RE1'
		.'gP7NbFUDF8ADeHananIXAQNQZEyKyigJBNBky5A6jAgtycM2ChIgN84UuUcE'
		.'DcpylgGC9cHrBKV5BGd0dpTpBVBXr6ONIzhne8wqgPSFEG0bwQrboTCCmgBt'
		.'A8E92wER7PpprwgW2faLwBJPe0NwwrYlHYErjraMoI9tlwiK32lzCNh8LxZB'
		.'SZC2j4DNYAmC8AeX8L6Qm6ci+OJCwM1KEXxyFUYis9ysRuB4+I7ZcLsA4PIq'
		.'UWkIP7KXzqnVNfzdMLKVJneMoFZZjVVEPjAH9iypWwRP8rD7CD1yXk+O2CSC'
		.'HenZ4wPu0JrMyZrMXlAde9NRpqryNlBfHPLSmctVcQfaq0daWgtozTMQfXmJ'
		.'gV1eB/xxu2P0K8AAdbhXSwv7mDMAAAAASUVORK5CYII='
	);
	header('Content-Type: '.$php_array['type']);
	header('Content-Length: '.$php_array['size']);
	header('Content-Disposition: inline; filename="'.$php_array['name'].'"');
	echo base64_decode($php_array['data']);
	exit();
}


/*---------------------------------------------------------------------------
	Initialization and Environment Detection
---------------------------------------------------------------------------*/

//Session tracks authentication information.
session_start();

//Check and correct for register_globals (assume off).
scrub_globals();

//Check and correct for magic_quotes (assume on).
if(!get_magic_quotes_gpc()) {
	foreach($_REQUEST as $k => $v) {
		$_REQUEST[$k] = addslashes($v);
	}
}


/*---------------------------------------------------------------------------
	Authentication
---------------------------------------------------------------------------*/

//Check for login request.
if($_REQUEST['user'] && $_REQUEST['pass']) {

	//Check other details.
	$host = $_REQUEST['host'] ? $_REQUEST['host'] : 'localhost';
	$name = $_REQUEST['name'] ? $_REQUEST['name'] : false;

	//Attempt to connect.
	$test = mysql_connect($host, $_REQUEST['user'], $_REQUEST['pass']);

	//Check for connection.
	if(!$test) {
		$_SESSION['message'] = '<p>Unable to log into database host.'
			.'  Invalid host name ('.$host.'), user name ('
			.$_REQUEST['user'].'), and/or password.</p>';
		$error = mysql_error();
		if($error) {
			$_SESSION['message'] .= '<p class="subtext">['.$error.']</p>';
		}
		header('Location: '.$CONF['script']);
		exit();
	}

	//Store credentials.
	$_SESSION['auth'] = true;
	$_SESSION['host'] = $host;
	$_SESSION['user'] = $_REQUEST['user'];
	$_SESSION['pass'] = $_REQUEST['pass'];

	//Attempt selection.
	if(!($name && mysql_select_db($name))) {

		//Make them pick a DB.
		$_SESSION['message'] = '<p>You must select a'
			.' database to continue.</p>';
		print_header();
		print_form('select_db');
		print_footer();
		exit();
	}

	//Store selection.
	$_SESSION['name'] = $name;

	//Set message.
	$_SESSION['message'] = '<p>You have successfully logged into'
		.' the database host.</p>';

	//Hand them off to the table listing.
	header('Location: '.$CONF['script']);
	exit();
}


//Check for logout request.
if($_REQUEST['logout']) {
	unset($_SESSION['auth']);
	unset($_SESSION['user']);
	unset($_SESSION['pass']);
	unset($_SESSION['name']);
	unset($_SESSION['host']);
	header('Location: '.$CONF['script']);
	exit();	
}


//Check session for existing authentication.
if(!isset($_SESSION['auth'])) {

	//Login form.
	print_header();
	print_form('login');
	print_footer();
	exit();
}

//We should have valid connection credentials.
else {
	mysql_connect($_SESSION['host'], $_SESSION['user'], $_SESSION['pass']);
}


//Check for DB selection.
if($_REQUEST['select_db']) {

	//Check for valid selection.
	if(!mysql_select_db($_REQUEST['select_db'])) {

		//Make them pick a DB.
		$_SESSION['message'] = '<p>You must select a'
			.' database to continue.</p>';
		print_header();
		print_form('select_db');
		print_footer();
		exit();
	}

	//Store selection.
	$_SESSION['name'] = $_REQUEST['select_db'];

	//Set message.
	$_SESSION['message'] = '<p>You have successfully logged into'
		.' the database host.</p>';

	//Hand them off to the table listing.
	header('Location: '.$CONF['script']);
	exit();
}

//We should have a valid database name.
else {
	mysql_select_db($_SESSION['name']);
}


/*---------------------------------------------------------------------------
	Query Display
	(We know we're logged in with a selected DB at this point.)
---------------------------------------------------------------------------*/

//Check for advanced query form.
if($_REQUEST['page'] == 'advanced') {
	print_header();
////////////
echo 'advanced query form here';
	print_footer();
}

//Check for user query.
else if($_REQUEST['query']) {
	print_query(stripslashes($_REQUEST['query']));
}

//Check for export query.
else if($_REQUEST['csv']) {
	export_query(stripslashes($_REQUEST['csv']));
}

//Default query displays list of tables in the database.
else {
	print_query('show tables');
}


/*---------------------------------------------------------------------------
	Functions
---------------------------------------------------------------------------*/


/**
 * print_header
 */
function print_header() {
	global $CONF;
	echo '<?xml version="1.0" encoding="iso-8859-1"?'.">\n"
		.'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"'
		.' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
?>

<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<title>Single Script Database Interrogator</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="author" content="Zac Hester" />
<meta name="generator" content="vi" />
<link rel="stylesheet" type="text/css" href="<?php
	echo $CONF['script']; ?>?resource=stylesheet" />
<script type="text/javascript" src="<?php
	echo $CONF['script']; ?>?resource=script"></script>
</head>
<body onload="if(window.init){init();}">
	<div id="page_root">
		<div id="header">
			<h1>Single Script Database Interrogator</h1>
			<ul id="nav">
			<?php
			if($_SESSION['auth']) {
				echo '<li class="first"><a href="'.$CONF['script']
					.'">Home</a></li>';
				echo '<li><a href="'.$CONF['script']
					.'?page=advanced">Advanced</a></li>';
				echo '<li><a href="'.$CONF['script']
					.'?logout=true">Logout</a></li>';
			}
			?>
			</ul>
		</div>
		<div id="primary">
			<?php
				if($_SESSION['message']) {
					echo '<div id="message">'.$_SESSION['message'].'</div>';
					unset($_SESSION['message']);
				}
				if($_SESSION['auth']) {
					$q = trim(stripslashes($_REQUEST['query']));
					echo '<div id="quick_query"><form action="'
						.$CONF['script'].'" method="get">';
					if($q) {
						if(strpos($q, "\n") !== false) {
							$nrows = count(split("\n", $q));
							echo '<textarea name="query" rows="'.$nrows.'">'
								.htmlspecialchars($q).'</textarea><br />';
						}
						else {
							echo '<input type="text" name="query" value="'
								.htmlentities($q).'" /> ';
						}
					}
					else {
						echo '<input type="text" name="query" /> ';
					}
					echo '<input type="submit" value="Query" /></form></div>';
					if($_SERVER['HTTP_REFERER']) {
//////////////						//previous query/page
					}
				}
			?>
<?php
}


/**
 * print_footer
 */
function print_footer() {
	global $CONF;
?>
		</div>
		<div id="footer">
			<?php
			if($_SESSION['auth']) {
				echo '<table class="stats">
					<tr>
						<th>Connected To</th>
						<td>'.mysql_get_host_info().'</td>
					</tr>
					<tr>
						<th>Connected User</th>
						<td>'.$_SESSION['user'].'</td>
					</tr>
					<tr>
						<th>Current Database</th>
						<td>'.$_SESSION['name'].'</td>
					</tr>
					<tr>
						<th>MySQL Client Version</th>
						<td>'.mysql_get_client_info().'</td>
					</tr>
					<tr>
						<th>MySQL Server Version</th>
						<td>'.mysql_get_server_info().'</td>
					</tr>
					<tr>
						<th>MySQL Protocol</th>
						<td>'.mysql_get_proto_info().'</td>
					</tr>
				</table>';
				echo '<div class="tools">
					<p>
						<input type="text" id="ts_stamp" />
						<input type="button" value=" &gt; "'
							.' onclick="ts_dec();" />
						<input type="button" value=" &lt; "'
							.' onclick="ts_enc();" />
						<input type="text" id="ts_long" />
					</p>
				</div>';
			}
			?>
			<p class="clear">
				Single Script Database Interrogator version
				<?php echo $CONF['version']; ?><br />
				&copy;2007 <a href="http://zacharyhester.com/">Zac Hester</a>
			</p>
		</div>
	</div>
</body>
</html>
<?php
}


/**
 * print_form
 */
function print_form($identifier) {
	global $CONF;
	echo '<form action="'.$CONF['script'].'" method="post" class="page">';
	if($identifier == 'login') {
		?>
		<h2>Database Login</h2>
		<p>
			Database User<br />
			<input type="text" name="user" />
		</p>
		<p>
			Database Password<br />
			<input type="password" name="pass" />
		</p>
		<p>
			Database Host<br />
			<input type="text" name="host" value="localhost" />
		</p>
		<p>
			Database Name<br />
			<input type="text" name="name" />
		</p>
		<p>
			<input type="submit" value="Login" />
		</p>
		<?php
	}
	else if($identifier == 'select_db') {
		echo '<h2>Database Selection</h2>'
			.'<table class="data"><tr class="header"><th>Databases</th></tr>';
		$res = mysql_list_dbs();
		$i = 0;
		while(list($dbname) = mysql_fetch_row($res)) {
			echo '<tr class="'.($i%2?'odd':'even').'"><td><a href="'
				.$CONF['script'].'?select_db='.$dbname.'">'.$dbname
				.'</a></td></tr>';
			++$i;
		}
		echo '<tr class="footer"><td>'.mysql_num_rows()
			.' databases found</td></tr></table>';
	}
	else if($identifier == 'query') {
//////advanced query form
		echo '';
	}
	else {
		echo 'Invalid form request.';
	}
	echo '</form>';
}


/**
 * print_results
 */
function print_results($query) {
	global $CONF;

	//Run the query.
	$res = mysql_query($query);

	//Check the results.
	if(!$res) {
		echo '<p><em>There are no results available for this query.</em></p>';
		$error = mysql_error();
		if($error) {
			echo '<p class="subtext">['.$error.']</p>';
		}
		return(false);
	}

	//Basic query info.
	$num_cols = mysql_num_fields($res);
	$num_records = mysql_num_rows($res);

	//Check for 'show tables' query.
	if($query == 'show tables') {

		//Re-query for table info.
		$res = mysql_query('show table status');

		echo '<table class="data"><tr class="header"><th>Table</th>'
			.'<th>Records</th><th>Storage</th><th>Fields</th>'
			.'<th>Export</th></tr>';
		$crow = 0;
		$total_storage = 0;
		while($rec = mysql_fetch_assoc($res)) {
			$tsize = $rec['Data_length'] + $rec['Index_length'];
			$total_storage += $tsize;
			echo '<tr class="'.($crow%2?'odd':'even').'">'
				.'<td><a href="'.$CONF['script'].'?query='
				.urlencode('select * from '.$rec['Name']).'">'
				.$rec['Name'].'</a></td>'
				.'<td>'.$rec['Rows'].'</td>'
				.'<td>'.$tsize.'</td>'
				.'<td><a href="'.$CONF['script'].'?query='
				.urlencode('describe '.$rec['Name']).'">Fields<a></td>'
				.'<td><a href="'.$CONF['script'].'?csv='
				.urlencode('select * from '.$rec['Name']).'">CSV</a></td>'
				.'</tr>';
			++$crow;
		}
		echo '<tr class="footer"><td colspan="5">'
			.$num_records.' tables, '.$total_storage
			.' bytes</td></tr></table>';
		echo '</table>';
	}

	//Describe and create table queries.
	else if(
		preg_match('/^describe (\w+)/i', $query, $m)
		||
		preg_match('/^create table (\w+)/i', $query, $m)
	) {
		if(preg_match('/^create table/i', $query)) {
			$res = mysql_query('describe '.$m[1]);
			$num_cols = mysql_num_fields($res);
			$num_records = mysql_num_rows($res);
		}
		echo '<table class="data"><tr class="header">';
		for($i = 0; $i < $num_cols; ++$i) {
			echo '<th>'.htmlspecialchars(mysql_field_name($res, $i)).'</th>';
		}
		echo '</tr>';
		$crow = 0;
		while($rec = mysql_fetch_row($res)) {
			echo '<tr class="'.($crow%2?'odd':'even').'">';
			for($i = 0; $i < $num_cols; ++$i) {
				echo '<td>';
				print_field($rec[$i], mysql_field_type($res, $i));
				echo '</td>';
			}
			echo '</tr>';
			++$crow;
		}
		echo '<tr class="footer"><td colspan="'.$num_cols.'">'
			.$num_records.' fields</td></tr></table>';
		echo '</table>';
		list($toss, $sct) = mysql_fetch_row(mysql_query(
			'show create table '.$m[1]
		));
		echo '<div class="code"><pre>'.$sct.'</pre></div>';
	}

	//Delete/update/alter queries.
	else if(preg_match('/^(delete|update|alter)/i', $query)) {
		echo 'Query complete.  Affected '
			.mysql_affected_rows().' rows.';
	}

	//All other queries.
	else {
		$primary_key_offset = -1;
		$primary_key_name = '';
		echo '<table class="data" id="results"><tr class="header">';
		for($i = 0; $i < $num_cols; ++$i) {
			echo '<th>';
			$flags = mysql_field_flags($res, $i);
			if(strpos($flags, 'primary_key') !== false) {
				$primary_key_offset = $i;
				$primary_key_name = mysql_field_name($res, $i);
				echo '<span>'
					.$primary_key_name
					.'</span>';
			}
			else {
				echo htmlspecialchars(mysql_field_name($res, $i));
			}
			echo '</th>';
		}
		echo '</tr>';
		$crow = 0;
		$access = false;
		while($rec = mysql_fetch_row($res)) {
			echo '<tr class="'.($crow%2?'odd':'even').'">';
			for($i = 0; $i < $num_cols; ++$i) {
				echo '<td>';
				if($primary_key_offset == $i) {
					$access = $primary_key_name.'='.$rec[$i];
					echo '<span>';
					print_field($rec[$i], mysql_field_type($res,$i), $access);
					echo '</span>';
				}
				else {
					print_field($rec[$i], mysql_field_type($res,$i), $access);
				}
				echo '</td>';
			}
			echo '</tr>';
			++$crow;
		}
		echo '<tr class="footer"><td colspan="'.$num_cols.'">'
			.$num_records.' records returned</td></tr></table>';
		echo '</table>';
//paging for large result sets (more than 200)
	}
}


/**
 * print_field
 */
function print_field($value, $type, $access = false) {
	if($type == 'int') {
		//Length of time stamp integers.
		if(strlen($value) >= 9 && strlen($value) <= 11) {
			echo '<a href="#tt:date">'.$value.'</a>';
		}
		else {
			echo $value;
		}
	}
	else if($type == 'real') {
		echo htmlspecialchars($value);
	}
	else if($type == 'string') {
		echo htmlspecialchars($value); 
	}
	else if($type == 'blob') {
		if($value && strlen($value) > 16) {
			if($access) {
				echo htmlspecialchars(substr($value, 0, 16))
					.' <a href="#tt:blob:'.$access.'">&raquo;</a>';
			}
			else {
				echo htmlspecialchars(substr($value, 0, 16))
					.'...';
			}
		}
		else if($value) {
			echo htmlspecialchars($value);
		}
	}
	else {
		echo htmlspecialchars($value);
	}
}


/**
 * print_query
 */
function print_query($query) {
	print_header();
	print_results($query);
	print_footer();
}


/**
 * export_query
 */
function export_query($query) {
	header('Content-Type: text/css');
// run query
// build csv
}


/**
 * Global Variable Scrubber
 *
 * This secures our scripts from any register_global attacks/mistakes if
 * we can't control this setting in local server config.
 *
 * This is based on several examples on php.net.
 *
 * @author Zac Hester
 * @date 2004-10-05
 */
function scrub_globals() {

	//See if we need to scrub at all.
	if(!ini_get('register_globals')) { return(false); }

	//Check for exploit of this method.
	if(isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) {
		die('Script exploit detected!  Page access denied.');
	}

	//Protect some variables from being scrubbed.
	$protected = array('GLOBALS', '_GET', '_POST', '_COOKIE', '_REQUEST',
		'_SERVER', '_ENV', '_FILES');

	//Build our scanning list.
	$input = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES,
		(isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array()));

	//Scan through everything that needs to be scrubbed.
	foreach($input as $k => $v) {

		//Make sure it's not protected, but is a global.
		if(!in_array($k, $protected) && isset($GLOBALS[$k])) {

			//Scrub the global.
			unset($GLOBALS[$k]);
		}
	}
}

?>