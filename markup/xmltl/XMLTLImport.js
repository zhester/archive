//XMLTLImport, Zac Hester, 2009-02-11
function XMLTLImport(xmltl_dom_root) {
	this.dom = xmltl_dom_root;
}
XMLTLImport.prototype.import = function() {
	return(this.getObject(arguments[0]?arguments[0]:this.dom));
};
XMLTLImport.prototype.getObject = function(elem) {
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
XMLTLImport.prototype.getScalor = function(elem) {
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
