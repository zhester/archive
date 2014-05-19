/****************************************************************************
	XMLTL: XML Transport Layer
	Zac Hester
	2009-02-11

	DTD: http://rushmoreradio.net/public/xmltl.dtd

	This an exhaustive functional class, not intended for implementation.
	Use the tiny classes XMLTLImport.js for specific use.
****************************************************************************/

function XMLTL() {

	this.src = false;
	this.xml = false;
	this.error = '';

	if(arguments[0]) {
		if(typeof(arguments[0]) == 'object') {
			this.src = arguments[0];
		}
		else if(typeof(arguments[0]) == 'string') {
			this.xml = arguments[0];
		}
		else {
			this.error = 'Invalid type passed to constructor.';
		}
	}
}

XMLTL.prototype.export = function() {
	if(arguments[0]) {
		this.src = arguments[0];
	}
	this.buildXML();
	return(this.xml);
};

XMLTL.prototype.import = function() {
	if(arguments[0]) {
		this.xml = arguments[0];
	}
	this.importXML();
	return(this.src);
};

XMLTL.prototype.toString = function() {
	return(this.export());
};

	/*----------------------------------------------------------------------*/

XMLTL.prototype.buildXML = function() {
	var stype = this.getType(this.src);
	this.xml = '<?xml version="1.0"?'
		+">\n"+'<!DOCTYPE xmltl PUBLIC "-//NRR//XMLTL//EN"'
		+' "http://rushmoreradio.net/public/xmltl.dtd">'
		+"\n"+'<xmltl bt="'+stype+'">';
	if(stype == 'a') {
		for(var i in this.src) {
			this.xml += this.getTag(this.src[i]);
		}
	}
	else if(stype == 'h') {
		for(var k in this.src) {
			this.xml += this.getTag(this.src[k], k);
		}
	}
	else {
		this.xml += this.getTagData(this.src);
	}
	this.xml += "\n</xmltl>";
};

XMLTL.prototype.getType = function(node) {
	if(typeof(node) == 'object') {
		if(node == null) { return('n'); }
		if(node.length) { return('a'); }
		return('h');
	}
	else if(typeof(node) == 'number') {
		if(node.indexOf('.') != -1) { return('f'); }
		return('i');
	}
	else if(typeof(node) == 'null') { return('n'); }
	else if(typeof(node) == 'boolean') { return('b'); }
	else if(typeof(node) == 'string') { return('s'); }
	return('');
};

XMLTL.prototype.getTag = function(v) {
	var n = arguments[1] ? ' n="'+this.getHE(arguments[1])+'"' : '';
	var t = this.getType(v);
	switch(t) {
		case 'n':
			return('<s'+n+' t="n"/>');
		case 'f': case 'i': case 'b':
			return('<s'+n+' t="'+t+'">'+this.getTagData(t,v)+'</s>');
		case 's':
			return('<s'+n+'>'+this.getTagData(t,v)+'</s>');
		case 'a':
			var a = '<a'+n+'>';
			for(var i in v) { a += this.getTag(v[i]); }
			return(a+'</a>');
		case 'h':
			var h = '<h'+n+'>';
			for(var k in v) { h += this.getTag(v[k], k); }
			return(h+'</h>');
	}
	this.error = 'Invalid type found in array/object.';
	return('<!-- XMLTL Unknown Data -->');
};

XMLTL.prototype.getTagData = function(type, value) {
	switch(type) {
		case 'n': return('');
		case 'b': return(value?'true':'false');
		case 'f': case 'i': case 's': return(this.getHE(value));
	}
	this.error = 'Internal logic error.';
	return('<!-- XMLTL Internal Error -->');
};

XMLTL.prototype.importXML = function() {
	var dom = null;
	if(window.ActiveXObject) {
		dom = new ActiveXObject('Microsoft.XMLDOM');
		dom.async = 'false';
		dom.loadXML(this.xml);
	}
	else {
		var dp = new DOMParser();
		dom = dp.parseFromString(this.xml, 'text/xml');
	}
	this.src = this.getObject(dom.childNodes[1]);
	return(this.src);
};

XMLTL.prototype.getObject = function(elem) {
	var ctag = elem.tagName;
	if(ctag == 'xmltl') {
		var bt = elem.getAttribute('bt') ? elem.getAttribute('bt') : 'h';
		ctag = (bt != 'h' && bt != 'a') ? 's' : bt;
	}
	if(ctag == 's') {
		return(this.getScalor(elem));
	}
	else if(ctag == 'a') {
		var arr = [];
		for(var i = 0; i < elem.childNodes.length; ++i) {
			arr.push(this.getObject(elem.childNodes[i]));
		}
		return(arr);
	}

	else if(ctag == 'h') {
		var obj = {};
		var c = null;
		for(var i = 0; i < elem.childNodes.length; ++i) {
			c = elem.childNodes[i];
			if(c.getAttribute('n')) {
				obj[c.getAttribute('n')] = this.getObject(c);
			}
			else {
				if(!obj.ANONYMOUS) { obj.ANONYMOUS = []; }
				obj.ANONYMOUS.push(this.getObject(c));
			}
		}
		return(obj);
	}
	this.error = 'Invalid XML document.';
	return(null);
};

XMLTL.prototype.getScalor = function(elem) {
	var s = elem.textContent;
	var t = elem.getAttribute('t');
	if(t) {
		switch(t) {
			case 'n': return(null);
			case 'b': return(s=='true'?true:false);
			case 'i': return(parseInt(s));
			case 'f': return(parseFloat(s));
		}
	}
	return(s);
};

XMLTL.prototype.getHE = function(str) {
	var nstr = str.replace(
		/&|<|>|"/g,
		function(m) {
			switch(m) {
				case '&': return('&amp;');
				case '<': return('&lt;');
				case '>': return('&gt;');
				case '"': return('&quot;');
			}
			return(null);
		}
	);
	return(nstr);
};
