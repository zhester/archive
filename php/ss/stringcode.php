<?php
/****************************************************************************
	Various String Encoding/Decoding
	Zac Hester - 2006-12-18

****************************************************************************/

//Map code requsts to PHP functions and info.
$fmap = array(
	'md5' => array('n' => 'MD5', 'f' => 'md5'),
	'htmlspecialchars' => array('n' => 'HTML', 'f' => 'htmlspecialchars'),
	'base64_encode' => array('n' => 'Base64 Enc', 'f' => 'base64_encode'),
	'base64_decode' => array('n' => 'Base64 Dec', 'f' => 'base64_decode')
);

//Encode/decode for iframe reporting.
if($_REQUEST['code']) {

	//No DB insertion, so check/strip slashes.
	if(get_magic_quotes_gpc()) {
		foreach($_REQUEST as $k => $v) {
			$_REQUEST[$k] = stripslashes($v);
		}
	}

	//Kick out a simple page for the iframe report.
	?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<title>String Encoder/Decoder Results</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta name="Content-Language" content="english" />
<meta name="author" content="Zac Hester" />
<meta name="generator" content="vi" />
<style type="text/css">
body {
	margin: 0;
	padding: 0;
	font-family: Arial,Verdana,sans-serif;
	font-size: 12pt;
}
table {
	margin: 0.5em auto;
	padding: 0;
	border-collapse: collapse;
}
table tr th {
	font-weight: normal;
	text-align: right;
	padding: 3px 15px;
	border: solid 1px #666666;
}
table tr td {
	padding: 3px 15px;
	border: solid 1px #666666;
}
</style>
</head>
<body>
<?php
if(in_array($_REQUEST['code'], array_keys($fmap))) {
	?>
	<table>
		<tr>
			<th>Source</th>
			<td>
				<?php echo htmlspecialchars($_REQUEST['source']); ?>
			</td>
		</tr>
		<tr>
			<th>Code Method</th>
			<td>
				<?php echo $fmap[$_REQUEST['code']]['n']; ?>
			</td>
		</tr>
		<tr>
			<th>Output</th>
			<td>
				<?php echo htmlspecialchars(
					$fmap[$_REQUEST['code']]['f']($_REQUEST['source'])
				); ?>
			</td>
		</tr>
	</table>
	<?php
}
else {
	echo '<p>Enter a string above to encode/decode.</p>';
}
?>
</body>
</html>
	<?php

}


//Logo output.
else if($_REQUEST['binary'] == 'ss_logo') {
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


//Display the page.
else {
	?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<title>String Encoder/Decoder</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta name="Content-Language" content="english" />
<meta name="author" content="Zac Hester" />
<meta name="generator" content="vi" />
<style type="text/css">
body {
	font-family: Arial,Verdana,sans-serif;
	font-size: 12pt;
}
h1 {
	margin: 0 0 0.5em 0;
}
h3 {
	margin: 0.5em 0 0 0;
}
div#page_root {
	width: 640px;
	margin: 0.5em auto;
	padding: 10px;
	border: solid 1px #CCCCCC;
	color: #000000;
	background-color: #FFFFFF;
	background-position: right top;
	background-repeat: no-repeat;
	background-image: url(stringcode.php?binary=ss_logo);
}
iframe {
	width: 100%;
	height: 120px;
	border: none;
}
</style>
</head>
<body>
<div id="page_root">
<form action="stringcode.php" method="get" target="results">
	<h1>String Encoder/Decoder</h1>
	<p>
		Source String<br />
		<input type="text" name="source" size="40" />
	</p>
	<p>
		Target Encoding/Decoding<br />
		<select name="code">
			<option value="default"></option>
			<?php
			foreach($fmap as $k => $v) {
				echo '<option value="'.$k.'">'.$v['n'].'</option>';
			}
			?>
		</select>
	</p>
	<p>
		<input type="submit" value="Run" />
	</p>
</form>
<h3>Results</h3>
<p>
	<iframe src="stringcode.php?code=default" name="results"></iframe>
</p>
</div>
</body>
</html>
	<?php
}

?>