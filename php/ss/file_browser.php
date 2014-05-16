<?php
/****************************************************************************
	Single Script File Browser
	Zac Hester
	2008-06-25

	Something I whipped up as a drop-in server file browser.  The idea
	is giving people an easy way to download files that is easy to manage.

	To Do:
		- Filter based on extension.
		- Filter based on MIME type.
		- Sorting by size and date.
		- Path in page title.
		- Heading/logo customizations in a config section.
		- "DL" links to force download headers.
		- Fix the icons (they didn't resize very well).
		- If zipping is available, allow folder downloads.
		- If unzipping is available, browse into archive files.
		- Image thumbnails?
****************************************************************************/

/**
 * Builds a human-friendly string describing a file size.
 *
 * @author Zac Hester <zac@planetzac.net>
 * @date 2006-03-03
 *
 * @param size The size in bytes
 * @param verbose Whether or not to print verbose descriptions
 * @return A string describing the size
 */
function get_size($nbytes, $verbose = false) {
	if($verbose) {
		$units = array('bytes', 'kilobytes', 'megabytes',
			'gigabytes', 'terabytes');
	}
	else {
		$units = array('B', 'kB', 'MB', 'GB', 'TB');
	}
	$size = $nbytes;
	$index = 0;
	while($size >= 1024) {
		$size /= 1024;
		++$index;
	}
	return(sprintf('%01.2f %s', $size, $units[$index]));
}


if($_REQUEST['i']) {

$images = array(
	'file.png' => array(
		'type' => 'image/png',
		'size' => '547',
		'name' => 'file.png',
		'data' =>
		'iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAGXRFWHRTb2Z0'
		.'d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAcVJREFUeNqsVV1LwmAUfrcs'
		.'06CEDLEP+rjIhIgI+oCugj4govtu+y9d9yO6iKB+RAT+gG5yCZNSy2U6obkt'
		.'na5zZG+Y7Xs+8DC3d57nfd5zdg7RdZ24Rb4orBOvcCMgycqlrHzryCe+qHuJ'
		.'z6AAwzD/FkRRjIVHo2L/85f3yu/v9NIs41kAd2n3h4LwSTqdv6/YCbFUCK07'
		.'BUfMJeLd6/hYhMwnp7qsVKoXTg6iINDAl/0iGgnjZnUrB02wudt7vl6R4/lD'
		.'pxwkwEU5oAvGTiAE3IRazyQmY34FRuDSMjsihIZFojZbvh1AkTStckBRfua4'
		.'O6FaJwEQdvrQViAXT4PKBWuyXtK0liQpahAXw06tIgUusoNwwVqslxRZFkgw'
		.'sHYCjY3V5YMgH94jl7+ntW/axbH9+wlMN7WWWjzHFFi2a1wE7kAuMm5yUa1/'
		.'ESwMaDnHcPsG5IBqyG4WAV+dAmvtNil91Mjp0f5ZLpfl4RGyBuy4mWghw4VO'
		.'Jxql1FDpdDsBbgPjhmvPI3O6XwDvr25ur2FtDzhjk0tilwMKbGBbEPShZ4Jh'
		.'ay4YRygHHvqApLFjDJzu7zeDEBgCLgAnTM/ZBj8CDADryvmmC+aptAAAAABJ'
		.'RU5ErkJggg=='
	),
	'folder.png' => array(
		'type' => 'image/png',
		'size' => '610',
		'name' => 'folder.png',
		'data' =>
		'iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAGXRFWHRTb2Z0'
		.'d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAgRJREFUeNpi/P//PwMtARMD'
		.'jQHNLWABEYyMjHCBa3cf3wfyFWB8TSUZRmwar997ghK2uNQxguJg26Gz/zWU'
		.'ZBgkRQQxFDx8/prh3/9/E5gYmQpAfAFebgZ+Hi4MdVyc7HALNh84fZ6JkdHg'
		.'17/finALZCREGFTlJMkKhsNnrzH8+fsXzBYR5GPQVZUHsx88e3kAbAEoiLYe'
		.'PPPf0UyXKAP3n7oMZ+Nz2N0nrxxZCBn24OkrhvtPX4LZPFwcDKY6qgzEOOTj'
		.'l29An8geYEGLOFBkoSgEGU6sz5DBh89fUZOpt70J44s370czGn4LQMGEnEIG'
		.'tQ/+/f/fSNACUBLlYGcjywJtZdkGrBYAbV4ACyZQElWUFqNuEPk6mCYi8yWw'
		.'lE/4wJfvP8DlF04LQkNDmclxKchQEO7tm6gHzKzMKMU1rGTV0qpnldFi4Pn1'
		.'6/caYDCFEGvwq1dvTm/fcMy9oyPrPdbi2qGhgUXv3Tuuf0IyPMy/mIRYmBgF'
		.'nN0cDoMUYCsmQIb++fP3+6zJS+QnT65+TbDCYTjAwMBrLc79i4FRjImBUfQ/'
		.'818hdIWPX75h+PfvP8OVK9eLVi2eNWn16tV/ifEhvLgGgbS0maw8ou9Nmf8x'
		.'mvIK8KkbW5hmKslKgOUWzlok0tFR9ZbkyMHVqiisny2Um9sqCnIEJcmUkdbN'
		.'FoAAAwAvSsgBMZIOMwAAAABJRU5ErkJggg=='
	),
	'up.png' => array(
		'type' => 'image/png',
		'size' => '705',
		'name' => 'up.png',
		'data' =>
		'iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAGXRFWHRTb2Z0'
		.'d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAmNJREFUeNpi/P//PwMtAQuI'
		.'YGRkxKsofr++AAcfXwGI/ePTpwkLHS9+IMZwkOMZwQQOC9LO2sxnYmRKwK75'
		.'34SZxkcKybIg5aCZIgsvxz1iXPnv/78Fs4yPJOKygAldMOOc3X+Q4a6Sshga'
		.'4uQrMcRAPgTpAYU0NktQLEg+YpUCokGGH3n1HEUhJzMPg6WQB8N0w4NgjMVh'
		.'/7BZghxEjCBFMJfvfv4YRSE2Q0Fg9ZPJDPter4HzZxgdYsQaRFAXgMHn37+J'
		.'ToahMrkovoIGF0YQgWkLEQkw58SbF0S5HhkUXfKGs5OOWLWhW8ABInhZWUnO'
		.'SMff7WDIPG/P8P3vF7gYGxdLJboFXLgMIOT6RQ/bcUmxIlvAgiwjyMZOtA9A'
		.'qQsH4EO24A9y5JoIi2G4fuuLBeCgQAd9eluxmm6aJaON7PJvsMhFzmDYDMQG'
		.'ZDhVGJ58v4Mi9vT019/IPvgBTL/OqF5nITqYqjXmYog9O/3+I7IFoDxwHzmD'
		.'2YhJYjWs/3Y+hhgOn35DLyoeA33hgmyJHDcvhq5bXy7A2aBgwROMb9EtAEX0'
		.'hWsbns+BWYIvX4AMbr2RjFUO6FA/IPUVo7AD2Xqo6fbUR8febQJxrn54h9Nw'
		.'XOD7+1+PQFqhwY61PgBZqgTEZsByZSkpuXqZ38nsT09+gtL2NUhZh7tGAwmI'
		.'A7GVfqy0pWWhcgkhw4HBAqp0TgHxTSD+S1SVCQTcQKwMxYIek7QCFWxEfGCS'
		.'V9Y8W3ik7c5+IPMl1NWg1PGf6DoZLdhAWZ8faikLNFF8gaaWb8gGI1vAQGaz'
		.'hZEYRSCzAQIMAHvfAvEUjU1fAAAAAElFTkSuQmCC'
	),
	'help.png' => array(
		'type' => 'image/png',
		'size' => '627',
		'name' => 'help.png',
		'data' =>
		'iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAGXRFWHRTb2Z0'
		.'d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAhVJREFUeNpi/P//PwMtASMx'
		.'ihRnXu1nYmIqALH//fvXeD9du4FiC5RmXdvPyMjogE8z0PcP7qVpKZJsgfLs'
		.'6+Bw4+dkZfj4/TdBV95N1WQk1gJGoOH/nDRFGLjYmBm2XHwJl1jqLcdgLsnF'
		.'sODKe4aWEy+JtogR3eWakrwMGpI8DOvPPWeosRBnSNARxOlylTk3UIPs37+P'
		.'99K1BZDFmGAM+Z5DmSAaZDgM4DMcBO6kaKC6lomJXyJ3riiyGAtMjoVfdJqe'
		.'DB+YA3I9umZk16LLIQNuPatXQIoZlOCQfcANDiIxbqqkfWBQ/0UPIn50RdFb'
		.'HxEVLIQAC9QSHnSJk8+/YUQiOnBYeRefNBcQf2OiJCgOhCvjkxaEBREoMn6A'
		.'OM8/gCmGQCNJopMmHgBKMUywVPQeRJy49x6v4SRawg6yABZEX4C50B9ZFpTh'
		.'CAFQhMMwFvATFDowC0DBdBWWB9AzHDEAiyUfkS0AgftAX7jBLNl3/Q3B4EJO'
		.'ysjB9vvNk9tA6g22wg4UMbHAjDIFOaiuP/9Mkm+ADnUBUntxFddqQOwJtGQC'
		.'OUkXaHgqkFoLSzi4ynExIHZSmHBiOjM3vwAJhicAqa2w4CFUZYJyojUQa0vk'
		.'zw7l1rGxwmNwFZB6AMQ7YC4ntk5mgvpGHYhVoXHEBtUHy6DPgPgayB5YhiW5'
		.'0keyjB2WgYD4DzSt/xyw1ggIAAQYAGqbpYDIGsx1AAAAAElFTkSuQmCC'
	),
	'file2.png' => array(
		'type' => 'image/png',
		'size' => '4437',
		'name' => 'file2.png',
		'data' =>
		'iVBORw0KGgoAAAANSUhEUgAAAIAAAACACAYAAADDPmHLAAAABGdBTUEAANkE'
		.'3LLaAgAAEQxJREFUeJztXXtwW9WZ/+nqLVt+REkcBkpezaYwaXE6O+3uADOw'
		.'w3M3A2GaHWanC6EtXdqkC2XbaQc2LM0yzRaYQtKWtLSBJGwwSdq4CbCQ0oak'
		.'Jd6kBFLb2MDaiZ3EMUnsOJL11r2S7v4hHevo6l756uq+JN/fjMa2fCQd+/vd'
		.'3/nO933nu4AFCxYsWLBgwcIMwfDZC+1Gz8GCzohGo+3ReOLZWDw5PHZpkv/w'
		.'5Mhw34nT9xk9L6NhM3oCWiIajbaDsa+2wbbSZrMtIM9nszxGLlwEAPA8f4oH'
		.'trPh9MblyxeGDJqqYag7AkgZXYjxYBjxZGrqZ57nQzywaaYRoS4IEAwmFjjd'
		.'eGg6o9OIJpKYCEVKnud5PgRgb4pNr19+1cJTqk7UhKhZAgSDiQUul20lbFht'
		.'s9kqduroZUAKPM9vq3ci1BQBqjW6EMJlQAo8nz2UydrWf3bJpw5V+5lmg+kJ'
		.'oLbRaUgtA1KoRyKYkgDBYLDF5fLep4XRachZBsTA8/ypLJ9dv+zT87epPyt9'
		.'YRoCBIPBFqfTsxIM7mRszEq9PlfuMiAGQgQukt1bqzsHQwlglNFpVLoMiKGW'
		.'t5C6E8AMRqeRzfIYHZ9ANstX/V45IvDbWDazqVZ2DroRIBpN3GcWowsxEYog'
		.'mkiq+p61soXUlACxWHIlbLgTwEqbzdai5WdVg3gyhfFgWJP35nl+WyaL7Wbd'
		.'OahOgFoxuhAjFy6qsgyIgWFsaGrwbrxszqyHNfmAKqAKAWrV6DS0WAZyhvfB'
		.'7/OCYWwYHwsunj9/3jAAbZimAIoJEIkkbmDsttWoYaPTUHMZcNjtaPb70Oj1'
		.'FD2fTqdfavI3fAU5ApiCBBUToP/kyA+8btfqeYGWBRrMx1BUuwx4XE40+Dwl'
		.'hqcxdPbMkmVLlgzBJCRgKhncf/LMs4zN9niK5RakMxmt5mQYfG63otd5XE60'
		.'BVrQFmgpa3wAuGz23IeQ+7+bIggnlwC2d97tXcTYmG+TJyYjcY2mZBy8HldF'
		.'4xu9Hlw+dxbaAi3wuJyyXuN2ue7p6OgIIEcAw0kghwA2ADa/v+Eu+sloIol6'
		.'UwGfxw2H3V52TN6jx+VzZyHQ4p92fOnrmeabb771QZhEBaYjAGGpPZPJzBL+'
		.'MpZQFkM3M+a0NoFhSu3CMDa0+Btw+ZwAWpsaKzY8Da/Pt7ajo2MWTKACchSA'
		.'AeAIBYP9wl+EY3HN9s5GweV0TBnZ43LC53Ej0OLHp9pmo7nRJ0qOSsEwTPNN'
		.'N99qCl9gug9nADgBuAB4P/i/4fcdTucV9IAWfwOaG31aza9ukcmkz/gbG5YC'
		.'4ABkYdCOQPYSAMDV39+3WTggGlc3eDJTYLc7rhz55JPVMFgFyhGAGJ9BjgD2'
		.'TU8/9cd0movSg9KZjOoRtJmClqbmR1EggCEkkL0NBMAcOfJObGBgYLfwl/W4'
		.'JdQDeRW4DxXGY9SE3A8mUSt+/aOP7BZTAaVVNTMdAhXQHeUIQIyezT8yANK9'
		.'vcdDo6OjXcLBkVhCmxnWOex2x5UfDw7eCINIMJ0CEAJkkPNWOQDp5zY9+6Jw'
		.'YJLlkGQ59Wc4AzBv7rx/R87PMjUB0siT4LW9e86ePjX8lnBwzNoRKILL5bpu'
		.'cGjoBhigAnIJQIzPAkgBYDt37/y1cHA9hof1wpzAnHUwQAXkOIFiJGB/+Yvn'
		.'PpqYuNgjHGztCJTB5XJd19PTsxw6q4DcXQDtB7Dk6xuv7tsmHBhNJOsuPKwX'
		.'rrjiym9BZwLI/SASDHID8AFoBNAEoOF438Aer8/XRg+2wsPK8efj711147XX'
		.'nkDugtP8SqokDlCyDADgjv7v4e3CwfWYJNILn/vM1eugowpU8iEMAAdyiaEG'
		.'FKvAb4Qq0NrUiKYGr2oTnUnIq8AgdEgSVRKC5JGTJdoXSAHgent79ggHW4Eh'
		.'5cirgC47gkoJQEhAgkIsAO7HT/3wdStJpB7cHs+KrVu36lIwUmkSQhgZZAGw'
		.'H3R3Bz/s7+8UDrZUQBkYhmn+hxV3PAgdVKBaAnDILwNPPLZul3Awy6Wt8LBC'
		.'+Hy+NR0dHa3QWAWUpCHJMlC0I+jr6w6ePn3qd8LBk5FYdTOcodCrbEwpAYQq'
		.'wALgfrbxma3CwUmWA8ulq5rkTIXP51uDwjKgCQmUFiLQcQESE2Bf39c5cv7c'
		.'J1aqWCUwDNOsddlYNZUoooGh3+9/8zfCgVaSSDnyBSOaqUC1CkCrQAoAu+GJ'
		.'x49ZSSL1ICgeVR3VvqmoL9B1+E8lKhBPpazwsEJoWTZWDQHowFCRL/D9hx88'
		.'lIjHL9CDs1kekbjlCyiB3e64cnj4zF3QgATVKoBoYAhlkkQWlKF1VutaaBAY'
		.'UuPNSJKIpIr9+Ufj8b6BXwuTRIEW/7RHqC2IY/Tc6G1LFi06ABVTxWo4FpKp'
		.'4sHBAZHAkKUCSqFF2ZiaBCjxBZ54bN0uK0mkHrQoHlVrayGqAn193cGhk0Ml'
		.'KmBVDyvH7FmzvwUVVUBtAgidQXbL85tLjpJZZwiUw+12rzjY1bUQKqmAmsEF'
		.'0STRa3v3jFhJInWhZtmY2gQQ3RJKJYms8LAyeH2+Lx/s6loEFUigdnhRMklk'
		.'hYfVhVoqoEV8WWxHwEmdIbBUQBm8Pt+X1Sgb0yLFSJ8h8IIKDB0+1v1cIDD7'
		.'GnqwdYZAOaLR6H/NnRP4AXIXW1bJe2ihAPQyUKQCvX85XuIMWmcIlEONsjGt'
		.'So3oMwTC8HDJGQJLBZQjrwKPI7fsVqwCWrUmETtDIJkkshpNKUdeBRT7AloS'
		.'QDRV/PPNPzlkhYfVA8MwzdffcMNKKNwRaNmcSDQ8LHWGwNoSKkc1ZWN6EKAk'
		.'LiCVJLIaTSlDNWVjWrcnkzxDYDWaUhdKy8b0IIBoqlgqPGwliZRB0HNQNgn0'
		.'aFAopgLc6/s6R6xGU+rC3+D/Z1SYKtaLAFajKR2gpGBEr140NhQHhhqRDw6J'
		.'hYcbvR4EWvw6Ta2+wLLs4ZZm/00odCEvCz171FqNpnRApSqgZ086q9GUTqhE'
		.'BfRUAKvRlE5wuVzXvXXokKyCEb1701bUaMo6Q6AciXj85UCg9auYJlWsd5/6'
		.'ihpNWeFh5ZBbNmYEAaxGUzpBTtmYEXeqsBpN6QRKBSSTRGYgwFSjqR3bt/6P'
		.'cLDVaEo5WC6Ny9ouvxdlooNG3avGOkOgIdKZDCZCEZy7GASb4a9HmRtTGUkA'
		.'q9GUyshmeUxG4zh3MTjlO2UyGbLzEvUFDLtbFaxGU6oimkhidHwCoUisKH6S'
		.'YtmzyC0BpiMAYDWaqhrxZAqjY5cwEYqUBM7S6XRk90vbf4qcAohWDBl992oS'
		.'Hi6pHhZLEjU1eNHa1Kj/LE2IJMthMhKTdJATicT5Hdte+Ldnnv7RuwAiAOLI'
		.'OdtpUM0llN8CW13Qdyh1AHAsvfrq1NLPXPV39CAuk4Hf54XNZjRvjUM6k0Ew'
		.'HEMwHEU6Uxrg4zgu1tfbs/vhtd/csG/vntMoJN44iJSOm+E/KUwSWWcIRECa'
		.'bIXK7Ij6+3pfffS73+kYGPgoCCAJIIrc1R9GQQE4UApgtA8AWI2mpsVkND7l'
		.'4Ilh9OzI0bUP3H//qjv+/lcDAx9NouBXkdNZpDi3JLtmBgUAyjSa+mBg+A2H'
		.'w1m08M+UJFE0kcRkJC7p/I6PjfV17t71ysZnnuxF8UWUApAAEENOBeLIKQJZ'
		.'BqaI4ND0L5APyVTxh/39nZ+7pv1eevBkJF7XBJjOwYvHYmMH3/7Dzu8+tPYP'
		.'KC66TSNn/CRyBEigIPtk/S9SAbMoAFBQAQ8oFVi2rH3eK52/3TUTVIA4eFLn'
		.'IziOix3pOrzzga/esw8FwwuDaeTqT1IPshyUtJcziwIA0zSa+qulS79ED47F'
		.'k3VDgGyWRzAcLZv5fO/Y0Z3/uW7dvsHBjyMQj6KmqEcSlIqiTE2AWbaBNEiw'
		.'YmpLGIvFzt186+3/SA9KZ7LwuF1w2M34J8hDNssjHEvgYiiMlESoe+jkiQPr'
		.'Hvnehmee3HDk0qWLCRQuEGJoIvdxFGSfEEAo/aZ1AgmI4Ulzianq4f0HD//H'
		.'/PkLbqUH+zxuzGlt0n+WKkChgyf0kVKCx9Qd3lFYHkQNT2DWy6ckMBQKhU7f'
		.'ctvtq+hBXDqDRp8HDGOG3aw8JFkO48EwovEksnypXeKx2NiLv3x+wwNfu/eV'
		.'o0e6zqG0eopc5XEUrvokiklQ9qqnYTYFAOr0DAHLpREMRyU9e47jYm/tf+MF'
		.'gWcvrJlgUZB38jMd5aOveFkVtWZUABv1tUgF5s277NI1yz9/Gz2YTadNrQLE'
		.'s79UJnT7l+PHOtfe/7Wnd7780kcoNTq9pZNa52kPv6JSajMqAFCm0dSfuz/c'
		.'3tTUtJgebMbwMAndlitvHzp54sCPn/rRK2//fv8FFK5g4RE6+kHWfrLnly31'
		.'UjDTNpCGZKOp9989uufGm275Hj04HIvD7/OCYczB52giiWA4Kmn48bGxvuc3'
		.'/2TLyy9tOwn5Dh7t1cty8OTAHP8xcdRco6l4MoVgOCbp2YdCoeFdO/57C+XZ'
		.'i0U/pQwvdsVXfXLGjD6AEAwEvsA17cvZhYsWX0sPSmeyht2tPMlymAhFEI4l'
		.'JD37t3735gt337Vi89EjXecxvWdP1npCADqpo4rhCcysAIB4kqjps+3tbR27'
		.'f7vb6PBwOpPBZCQuGcHjOC7W0/3+a1QEj17nWZRe9SRsKzS66oYnMKsPQCDa'
		.'W4CcITAqSSQnNz908sSBb6/5xpbBwY+jkOfgkT28ag6eHJhdAYCcCjghSBVL'
		.'JYnaAi3wuJyaTWYyGi/r2Y+eHTm64Yn1W/KevZiDJ+XZ0zF7VRw8OTC7AgAS'
		.'LWZIoylheHgyEoMn0KL6JBSGboVrPQnkiBmeXPEkWKDL0ehacAIJSGBoyiEM'
		.'hUJnhOFhtZNExMGLTOPg/dOqO38lcPA4FCdr6EAOTQLNHDw5qIUlABBPEjUB'
		.'aNz/9juPz1+w8BZ6sBrhYTm5+Z7u91+75+5VHSifm5fa0mnu4MmBOeOnpZBs'
		.'NPWng2+/KRxczRkCkpsfHbskafz3jh3d+aUVt99/z92rdlBzItu5OAqlWJH8'
		.'V1KWJdzaiVbp6IlaUQBA4ySRSqFbOSlaQ694IWrNByBfiR/gRC5JNCGWJGpq'
		.'8Mk6QxBNJHExFEE8mYLIMo/xsbG+HVtf3PTgmq/vGx46EUGpEpVL0QqvdsAk'
		.'xgdqSwEAlRtNJVkOwXBU8uBpPBYbe3Vv5wvrH3vkCAoGJMYXpmjFPHuhc2ca'
		.'wxPUkgLQEKaKnWLh4STLweGww+Us3u2mMxmMB8OYjMaRyYqnaPe/8fovVt1x'
		.'+8Y/HjxwBsVJKakUrbAowzDPvhLUmgIApeHhso2mgJw/4HbngkOpFFdJ6JbO'
		.'SE7n2dOZOtMbnqAWAkFCiDaXAODq7e3Z88W/+ds1whdEE8lpew2ZITdvBGpR'
		.'AYBiFWhAfjcglSQqBzPl5o1ALSoAUHqeMAXALZUkEoMZc/NGoFYVAChOEk01'
		.'nVy2rL1NLElEMM2xKkImsb08vZ2ruPjSrKjVXQANEia2A3CMjZ3Pcim2e/nn'
		.'//oLTmeBBKT48l8f+JeN+eJLodFpz54uvBQesqhZuRdDLSsAUFCBovOEyKmB'
		.'Z/0Pn7x+0acXL75w/sLY8z/76ZEKcvN14eDJQa0TgA4P0yeJGvI/u1FojiSU'
		.'e9Pl5o1ArTqBNGjnLYWcIpCuWDwKf6NwnOly80ag1glA35qOGJb0xANyhrRT'
		.'39N5euHpmrpz8OSg1glAkEHO6CwKxiekoAlAjG3K3LwRqAcCEGORjI4t/1wa'
		.'hf54RCmIwWs2dKs2at0JpFFyfgDFTZLpqp269uwrQT0RwEY9GOpBE4DkEYgi'
		.'ADPY+ADw/5hdK+IP35lBAAAAAElFTkSuQmCC'
	)
);

	$k = $_REQUEST['i'];
	$known = array_keys($images);
	if(in_array($k, $known)) {
		header('Content-Type: '.$images[$k]['type']);
		header('Content-Length: '.$images[$k]['size']);
		header('Content-Disposition: inline; filename="'.$k.'"');
		echo base64_decode($images[$k]['data']);
		exit();
	}
	header('HTTP/1.0 404 Not Found');
	exit();
}

$me = basename($_SERVER['SCRIPT_NAME']);

if($_REQUEST['p']
	&& strpos($_REQUEST['p'], '..') === false
	&& substr($_REQUEST['p'],0,1) != '/'
	&& is_dir($_REQUEST['p'])) {
	$cdir = $_REQUEST['p'];
	$relpath = $cdir.'/';
	$path_display = '';
	if(strpos($cdir, '/')) {
		$parts = explode('/', $cdir);
		for($i = 0; $i < count($parts); ++$i) {
			$path_parts = array();
			for($j = 0; $j <= $i; ++$j) {
				$path_parts[] = $parts[$j];
			}
			$path_display .= ' / <a href="'.$me.'?p='
				.implode('/', $path_parts).'">'.$parts[$i].'</a>';
		}
	}
	else {
		$path_display .= '/ <a href="'.$me.'?p='.$cdir.'">'.$cdir.'</a>';
	}
}
else {
	$cdir = '.';
	$relpath = '';
	$path_display = '/';
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<title>File Browser</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<style type="text/css">
body {
	margin: 0;
	padding: 0;
	font: 10pt/130% Arial,Helvetica,sans-serif;
	color: #000000;
	background-color: #EEEEEE;
}
a {
	color: #0000CC;
	background-color: transparent;
}
a:hover {
	color: #009900;
	background-color: transparent;
}
h1,h2,h3,h4,h5,h6 {
	font-family: Verdana,sans-serif;
}
img {
	border: none;
}
table {
	width: 99%;
	margin: 0.5em auto;
	padding: 0;
	border-collapse: collapse;
}
	table td, table th {
		margin: 0;
		padding: 3px 5px;
		border: solid 1px #CCCCCC;
	}
	table tr.even {
		color: inherit;
		background-color: #FFFFFF;
	}
	table tr.odd {
		color: inherit;
		background-color: #DDDDFF;
	}
	table tr.dir td.col0 {
		padding-left: 32px;
		background-image: url(<?php echo $me; ?>?i=folder.png);
		background-position: 2px 50%;
		background-repeat: no-repeat;
		background-color: transparent;
	}
	table tr.file td.col0 {
		padding-left: 32px;
		background-image: url(<?php echo $me; ?>?i=file.png);
		background-position: 2px 50%;
		background-repeat: no-repeat;
		background-color: transparent;
	}
	table tr.header th {
		text-align: left;
		background-color: #FFFFAA;
	}
	table tr.footer td {
		background-color: #FFFFAA;
	}
p.up {
	margin: 2px 0;
	min-height: 20px;
	padding: 2px 0 2px 32px;
	background-image: url(<?php echo $me; ?>?i=up.png);
	background-position: 2px 50%;
	background-repeat: no-repeat;
	background-color: transparent;
}
div#page_root {
	width: 80%;
	margin: 8px auto;
	padding: 0;
	border: solid 1px #999999;
	color: inherit;
	background-color: #FFFFFF;
}
div#header {
	height: 128px;
	background-image: url(<?php echo $me; ?>?i=file2.png);
	background-position: right top;
	background-repeat: no-repeat;
	background-color: #DDDDFF;
	border-bottom: solid 1px #CCCCCC;
}
	div#header h1 {
		margin: 0;
		padding: 84px 0 0 10px;
	}
div#navigation {
	background-color: #EEEEEE;
	padding: 1px 10px;
	border-bottom: solid 1px #CCCCCC;
}
	div#navigation h2 {
		margin: 5px 0;
		padding: 0;
		font-size: 120%;
	}
	div#navigation ul {
		margin: 2px 0;
		padding: 0;
		list-style: none;
		float: right;
	}
		div#navigation ul li {
			margin: 0;
			padding: 0 4px;
		}
			div#navigation ul li a {
				text-decoration: none;
			}
div#primary {
	padding: 1px 10px;
}
div#footer {
	padding: 5px 10px;
	background-color: #DDDDFF;
	border-top: solid 1px #CCCCCC;
	font-size: 80%;
	color: #555555;
	text-align: center;
}
div#help {
	position: absolute;
	width: 512px;
	top: 20px;
	right: 20px;
	margin: 0;
	padding: 0;
	border: solid 3px #999999;
	background-color: #FFFFFF;
}
	div#help h2 {
		margin: 0;
		padding: 5px;
		font-size: 100%;
		background-image: url(<?php echo $me; ?>?i=help.png);
		background-position: 99% 50%;
		background-repeat: no-repeat;
		background-color: #EEEEEE;
		border-bottom: solid 1px #CCCCCC;
	}
	div#help p {
		margin: 0;
		padding: 5px;
	}
	div#help p.footer {
		background-color: #EEEEEE;
		border-top: solid 1px #CCCCCC;
		text-align: center;
	}
</style>
<script type="text/javascript">
function show_help() {
	var node = document.createElement('div');
	node.id = 'help';
	node.innerHTML = '<h2>File Browser Help</h2>'
		+'<p>To view the contents of any folder, click on the folder in'
		+' the file list.</p>'
		+'<p>Clicking "Parent Directory" will show the contents of the'
		+' current folder\'s parent folder.</p>'
		+'<p>To download any file, right-click on the link to the file'
		+' and choose "Save Link As" or "Save Target As" (depending on'
		+' your web browser).</p>'
		+'<p class="footer"><a href="#close"'
		+' onclick="hide_help(); return(false);">Close Help</a></p>';
	document.body.appendChild(node);
}
function hide_help() {
	document.body.removeChild(document.getElementById('help'));
}
</script>
</head>
<body onload="if(window.init){init();}">
<div id="page_root">
	<div id="header">
	<h1>File Browser</h1>
	</div>
	<div id="navigation">
		<ul>
			<li><a href="#help" onclick="show_help(); return(false);"><img
				src="<?php echo $me; ?>?i=help.png" alt="Help" /></a></li>
		</ul>
<?php
	echo '<h2>Path: '.$path_display.'</h2>';
?>
	</div>
	<div id="primary">

<?php

$dh = opendir($cdir);
if($dh) {
	$dirs = array();
	$files = array();
	while(($node = readdir($dh)) !== false) {
		if(substr($node,0,1) != '.' && $node != $me) {
			if(is_dir($cdir.'/'.$node)) {
				$dirs[] = $node;
			}
			else {
				$files[] = $node;
			}
		}
	}
	closedir($dh);
	if($cdir != '.') {
		$updir = dirname($cdir) == '.' ? '' : '?p='.dirname($cdir);
		echo '<p class="up"><a href="'.$me.$updir
			.'">Parent Directory</a></p>';
	}
	if(count($files) || count($dirs)) {
		echo '<table><tr class="header"><th>File Name</th>'
			.'<th>Size</th><th>Last Modified</th></tr>';
		$i = 0;
		$tsize = 0;
		if(count($dirs)) {
			natcasesort($dirs);
			foreach($dirs as $dir) {
				$mtime = filemtime($relpath.$dir);
				echo '<tr class="'.($i%2?'odd':'even')
					.' dir"><td class="col0"><a href="'.$me.'?p='
					.$relpath.$dir.'">'
					.$dir.'</a></td><td>(dir)</td><td>'
					.date('Y-m-d H:i:s', $mtime).'</td></tr>';
				++$i;			
			}
		}
		if(count($files)) {
			natcasesort($files);
			foreach($files as $file) {
				$mtime = filemtime($relpath.$file);
				$size = filesize($relpath.$file);
				$tsize += $size;
				echo '<tr class="'.($i%2?'odd':'even')
					.' file"><td class="col0"><a href="'.$relpath.$file.'">'
					.$file.'</a></td><td>'.get_size($size).'</td><td>'
					.date('Y-m-d H:i:s', $mtime).'</td></tr>';
				++$i;			
			}
		}
		echo '<tr class="footer"><td colspan="3">'.$i.' files. '
			.get_size($tsize).' total.</td></tr></table>';
		if($cdir != '.') {
			$updir = dirname($cdir) == '.' ? '' : '?p='.dirname($cdir);
			echo '<p class="up"><a href="'.$me.$updir
				.'"> Parent Directory</a></p>';
		}
	}
	else {
		echo '<p class="message">This directory is empty.</p>';
	}
}
else {
	echo '<p class="message">Unable to open directory.  Bad request.</p>';
}

?>

	</div>
	<div id="footer">
		Single Script File Browser<br />
		<a href="http://zacharyhester.com/">Zac Hester</a>
	</div>
</div>
</body>
</html>