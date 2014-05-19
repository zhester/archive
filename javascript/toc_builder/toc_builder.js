/****************************************************************************
	TOC Builder
	Zac Hester
	2011-07-12

	Just a simple way to build a table of contents for hierarchical sections
	of a document.  This method restricts its search to the descendants of a
	single document element.

	Notes on my personal document conventions:
	1.  I only use a single <h1> element per document (not necessarily per
	    page).  This is always a semantic document title, and the first one
	    the TOC builder finds, it will use it to head off the TOC itself.
	    It would be trivial to adjust this behavior (for instance, using
	    the document's <title> contents).
	2.  I use headers <h2> through <h6> to indicate sub-sections of the
	    document.  <h2>s head the major sections of the document.  Any
	    heading elements before the first <h2> are considered document-level
	    headings (like subtitles, author credits, etc).
	3.  When authoring a document, I don't assign my own "id" attributes to
	    the heading elements.  If a user manually assigns IDs for another
	    purpose, this function will overwrite the IDs for use in direct-
	    linking to each sub-section from the TOC.
****************************************************************************/

String.prototype.getSlug = function() {
	return this.replace(/\s/g, '_')
		.replace(/[^A-Za-z0-9_-]/gi, '')
		.replace(/_+/g, '_');
};

function getTOC(elem) {

	//Create the target element for the TOC contents.
	var tgt = document.createElement('div');
	tgt.id = 'toc';

	//Scan for a document-level heading (expected one h1 per element).
	var h1s = elem.getElementsByTagName('h1');
	if(h1s.length) {
		tgt.appendChild(h1s[0].cloneNode(true));
	}

	//Build a list of sub-section headers.
	var hs = null, hdrs = [];
	for(var i = 2; i <= 6; ++i) {
		hs = elem.getElementsByTagName('h'+i);
		for(var j = 0; j < hs.length; ++j) { hdrs.push(hs[j]); }
	}

	//Sort the list in order of occurrance in the element.
	hdrs.sort(
		function(a, b) {
			return 3 - (a.compareDocumentPosition(b) & 6);
		}
	);

	//Iterate over all sub-section headers in the element.
	var j, a, li, ul, testul, hash, ohash, label, level;
	var plevel = 0, ldiff = 0, index = [];
	for(var i in hdrs) {

		//Skip headers below 2 until the first 2.
		if(plevel == 0 && hdrs[i].tagName != 'H2') { continue; }

		//Generate an index entry for (relatively) robust URL hashes.
		ohash = hdrs[i].firstChild.nodeValue.getSlug();
		hash = ohash;
		j = 0;
		while(index.indexOf(hash) != -1) {
			hash = ohash+'_'+j;
			++j;
		}
		while(document.getElementById(hash)) {
			hash = ohash+'_'+j;
			++j;
		}
		index[i] = hash;

		//Assign this header an ID for link anchors.
		hdrs[i].id = index[i];

		//Heading level.
		level = parseInt(hdrs[i].tagName.substr(1, 1));

		//Descending levels.
		if(level > plevel) {
			ul = document.createElement('ul');
			if(plevel == 0) {
				tgt.appendChild(ul);
			}
			else {
				li.appendChild(ul);
			}
		}

		//Ascending levels.
		else if(level < plevel) {
			ldiff = plevel - level;
			for(var j = 0; j < ldiff; ++j) {
				testul = ul.parentNode.parentNode;
				if(testul.tagName == 'UL') { ul = testul; }
				else { break; }
			}
		}

		//Create the TOC entry.
		a = document.createElement('a');
		a.setAttribute('href', '#'+index[i]);
		if(hdrs[i].firstChild && hdrs[i].firstChild.nodeValue) {
			label = hdrs[i].firstChild.nodeValue;
		}
		else {
			label = ' ';
		}
		a.appendChild(document.createTextNode(label));
		li = document.createElement('li');
		li.appendChild(a);
		ul.appendChild(li);

		//Set the previous level.
		plevel = level;
	}

	//Return the TOC as a new document element.
	return tgt;
}
