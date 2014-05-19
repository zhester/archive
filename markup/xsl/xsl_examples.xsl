<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:template match="/">
<xsl:variable name="test">testclass</xsl:variable>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
	<title><xsl:value-of select="rootnode/doctitle" /></title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<meta name="Content-Language" content="english" />
	<meta name="author" content="Zac Hester" />
	<meta name="generator" content="vi" />
	<link rel="stylesheet" type="text/css" href="/flat/xml/xsl_examples.css" />
</head>
<body>
<div id="page_root">
	<h1 class="{rootnode/doctitle/@type}"><xsl:value-of select="rootnode/doctitle" /></h1>
	<ul>
		<xsl:for-each select="rootnode/parent">
		<li>
			<xsl:value-of select="name" />
			(<em><xsl:value-of select="nickname" /></em>)
			<ul>
				<xsl:for-each select="child">
				<li>
					<xsl:value-of select="name" />
					(<em><xsl:value-of select="nickname" /></em>)
				</li>
				</xsl:for-each>
			</ul>
		</li>
		</xsl:for-each>
	</ul>
	<p class="{$test}">
		This is a test.<br />
		<xsl:value-of select="rootnode/doctitle/@att" />
	</p>
</div>
</body>
</html>
</xsl:template>

</xsl:stylesheet>