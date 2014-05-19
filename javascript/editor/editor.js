/****************************************************************************
	Full Featured Content Editor
	Zac Hester - 2006-03-03
	Release: 1.0.0

	This is my humble attempt at a highly functional, easy-to-use
	content editor with a lot of great features.

	To interface with this system, there are a few simple requirements:
		- Edit fields are set up as plain textarea form fields.
		- The textareas belong to one of the following three classes:
			edt_rich, edt_markup, edt_plain
		- The textareas are contained within a div classed "edt_parent"
		- The form can have a few hidden fields defined:
			edt_browseimage: path to image listing on server
			edt_browselink: path to document listing on server
			edt_stylesheet: path to style sheet for content
			edt_features: set of editor features "all" or "css"
			edt_admin_module? - - hook for image upload form
				- module, object, and id are already available !
		- Don't count on the textareas keeping their ID attribute if
			you set one.  This system assigns/reassigns the IDs.
		- The editors have to be initialized with one call edt_init()
		- To update the original textarea(s), call edt_udpate_forms()

	Requirements:
		- IE 5.5+
		- Gecko browsers (Moz 1.3+, FF 1.0+, NS 6+)
		- KHTML browsers (after OS X improvements... 1.3+?)

	To Do:
		- check link creators to make sure something is selected
		- table creator
		- image browser
		- document browser
		- new features:
			- find + search/replace
			- character map
			- insert flash
			- downloadable files
			- super/sub script
			- strikethrough
			- insert anchor
			- break link
		- new features contingent of selection points and range manipulation
		- arbitrary HTML insertion will allow editor buttons in HTML mode
		- then, try to make arbitrary properties editable


	Table of Contents
		0.   Predefined, Global Data
		I.   Initialization
		II.  Event Handlers
		III. Dialogue Generators
		IV.  DOM Convenience Functions
		V.   DOM Utilities
		VI.  Other Utilities
****************************************************************************/

/*---------------------------------------------------------------------------
	0. Initialization
---------------------------------------------------------------------------*/

//Global to detect silly browsers.
var edt_isIE = false;

//Valid fonts.
var edt_fontlist = {
	'Arial,sans-serif': 'Arial',
	'Courier New,Courier,monospace': 'Courier New',
	'Georgia,serif': 'Georgia',
	'Times New Roman,Times,serif': 'Times New Roman',
	'Verdana,sans-serif': 'Verdana'
};

//Valid block elements.
var edt_blocklist = {
	'<p>': 'Normal',
	'<h1>': 'Heading 1',
	'<h2>': 'Heading 2',
	'<h3>': 'Heading 3',
	'<h4>': 'Heading 4',
	'<h5>': 'Heading 5',
	'<h6>': 'Heading 6',
	'<blockquote>': 'Blockquote',
	'<pre>': 'Preformatted'
};

//Valid font sizes.
var edt_sizelist = {
	'1': '1',
	'2': '2',
	'3': '3',
	'4': '4',
	'5': '5',
	'6': '6',
	'7': '7'
};

//Valid colors.
var edt_colors = [
	['FFFFFF','FFCCCC','FFCC99','FFFF99','FFFFCC',
	'99FF99','99FFFF','CCFFFF','CCCCFF','FFCCFF'],
	['CCCCCC','FF6666','FF9966','FFFF66','FFFF33',
	'66FF99','33FFFF','66FFFF','9999FF','FF99FF'],
	['C0C0C0','FF0000','FF9900','FFCC66','FFFF00',
	'33FF33','66CCCC','33CCFF','6666CC','CC66CC'],
	['999999','CC0000','FF6600','FFCC33','FFCC00',
	'33CC00','00CCCC','3366FF','6633FF','CC33CC'],
	['666666','990000','CC6600','CC9933','999900',
	'009900','339999','3333FF','6600CC','993399'],
	['333333','660000','993300','996633','666600',
	'006600','336666','000099','333399','663366'],
	['000000','330000','663300','663333','333300',
	'003300','003333','000066','330099','330033']
];

//Define feature sets for popular sets of editing features.
var edt_features = {
	'css': {
		'bold': 'Bold', 'italic': 'Italic', 'underline': 'Underline',
		's0': '|',
		'createlink': 'Insert Link', 'browselink': 'Browse for Link',
		'maillink': 'Insert Email Link', 'insertimage': 'Insert Image',
		'browseimage': 'Browse for Image',
		's1': '|',
		'insertorderedlist': 'Numbered List',
		'insertunorderedlist': 'Bulleted List',
		'formatblock': 'Style',
		's2': '|',
		'undo': 'Undo', 'redo': 'Redo', 'selectall': 'Select All'
	},
	'all': {
		'bold': 'Bold', 'italic': 'Italic', 'underline': 'Underline',
		'forecolor': 'Text Color', 'hilitecolor': 'Background Color',
		's0': '|',
		'createlink': 'Insert Link', 'browselink': 'Browse for Link',
		'maillink': 'Insert Email Link', 'insertimage': 'Insert Image',
		'browseimage': 'Browse for Image',
		's1': '|',
		'formatblock': 'Style', 'fontname': 'Font', 'fontsize': 'Size',
		's2': '|',
		'justifyleft': 'Justify Left', 'justifycenter': 'Center',
		'justifyright': 'Justify Right', 'justifyfull': 'Justify Full',
		'indent': 'Indent', 'outdent': 'Outdent',
		's3': '|',
		'inserthorizontalrule': 'Horizontal Line',
		'insertorderedlist': 'Numbered List',
		'insertunorderedlist': 'Bulleted List',
		's4': '|',
		'undo': 'Undo', 'redo': 'Redo', 'selectall': 'Select All'
	}
};

//Editor configuration (defaults).
var edt_conf = {
	'features': 'all',
	'stylesheet': 'resources/editor_blank.css', //not currently used
	'form_id': ''
};

//HTTP client.
var edt_client = null;


/*---------------------------------------------------------------------------
	I. Initialization
---------------------------------------------------------------------------*/

/**
 * edt_init
 * Initialize all editors on the page.
 *
 * @param form_id The ID string of the form where the editor(s) live.
 */
function edt_init(form_id) {

	//Grab DOM node to form.
	var frm = document.getElementById(form_id);
	if(!frm) { return(false); }

	//Set ID for later use.
	edt_conf['form_id'] = form_id;

	//Browser detection.
	var ua = navigator.userAgent.toLowerCase();
	edt_isIE = (
		(ua.indexOf('msie') != -1)
		&& (ua.indexOf('opera') == -1)
		&& (ua.indexOf('webtv') == -1)
	);

	//Check for desired features.
	if(frm.elements['edt_features']) {
		edt_conf['features'] = frm.elements['edt_features'].value;
	}

	//Check for an editor stylesheet.
	if(frm.elements['edt_stylesheet']) {
		edt_conf['stylesheet'] = frm.elements['edt_stylesheet'].value;
	}

	//Scan the form for editor parent divs.
	var divs = frm.getElementsByTagName('div');
	for(var i = 0, j = 0; i < divs.length; ++i) {
		if(divs[i].className == 'edt_parent') {
			//Set the parent element's ID.
			divs[i].setAttribute('id', 'edt_'+j);
			//Initialize the editor.
			edt_init_editor(divs[i]);
			++j;
		}
	}
}


/**
 * edt_init_editor
 * Initialize an individual editor.
 *
 * @param edt_parent The DOM node of the editor's container element.
 */
function edt_init_editor(edt_parent) {

	//Grab the ID number of this editor.
	var idnum = edt_parent.id.split('_')[1];

	//Try to reference a textarea and set it up.
	var ta = edt_parent.getElementsByTagName('textarea')[0];
	if(!ta) { return(false); }
	ta.id = 'edt_'+idnum+'_ta';
	ta.value = trim(ta.value);

	//Hidden form field tracks current editor mode.
	var fldname = ta.getAttribute('name');
	var mdelem = document.createElement('input');
	mdelem.id = 'edt_'+idnum+'_fld_editormode';
	mdelem.setAttribute('name', fldname+'_editormode');
	mdelem.setAttribute('type', 'hidden');
	mdelem.setAttribute('value', '');
	edt_parent.appendChild(mdelem);

	//Set up iframe editor.
	var ifr = document.createElement('iframe');
	ifr.id = 'edt_'+idnum+'_if';
	ifr.setAttribute('src', 'resources/editor.html#'+idnum);
	var ifrdoc = null;

	//IE needs a little help.
	if(edt_isIE) {
		ifr.frameBorder = '0';
		ifr.onreadystatechange = function() {
			if(this.readyState == 'complete') {
				this.onreadystatechange = null;
				ifrdoc = this.contentWindow.document;
				ifrdoc.designMode = 'on';
				//IE will set up the document from here.
				this.onreadystatechange = function() {
					if(this.readyState == 'complete') {
						this.onreadystatechange = null;
						edt_body_loaded(idnum);
					}
				}
			}
		};
	}

	//Normal browsers.
	else {
		ifr.onload = function() {
			this.onload = null;
			ifrdoc = this.contentWindow.document;
			ifrdoc.designMode = 'on';
			if(edt_conf['features'] == 'css') {
				ifrdoc.execCommand('styleWithCSS', false, true);
			}
			//Gecko will set up the document from the document's body.
		};
	}

	//Attach the iframe to the editor container.
	edt_parent.appendChild(ifr);

	//Build rich editor controls.
	ctl = document.createElement('div');
	ctl.id = 'edt_'+idnum+'_ctl_0';
	ctl.className = 'edt_ctl edt_ctl_0';

	//Scan for features.
	var feats = edt_features[edt_conf['features']];
	for(var key in feats) {
		if(feats[key] == '|') {
			edt_add_separator(ctl, '|');
		}
		else {
			//Check for browse features that require form setup.
			if(key.substr(0,6) == 'browse') {
				var frm = document.getElementById(edt_conf['form_id']);
				var fel = frm.elements['edt_'+key];
				if(fel && fel.value.length) {
					edt_add_control(ctl, key, feats[key]);
				}
			}
			//All normal features.
			else {
				edt_add_control(ctl, key, feats[key]);
			}
		}
	}

	//Add the control node.
	edt_parent.insertBefore(ctl, ta);

	//Set up editor modes/misc controls.
	ctl = document.createElement('div');
	ctl.id = 'edt_'+idnum+'_ctl_5';
	ctl.className = 'edt_ctl edt_ctl_5';
	edt_add_control(ctl, 'mode_rich', 'Rich Edit');
	edt_add_control(ctl, 'mode_markup', 'HTML Edit');
	edt_add_control(ctl, 'mode_plain', 'Plain Edit');
	edt_add_separator(ctl, '|');
	edt_add_control(ctl, 'misc_expand', 'Expand');
	edt_add_control(ctl, 'misc_contract', 'Contract');
	//edt_add_control(ctl, 'misc_debug', 'Debug');
	edt_parent.appendChild(ctl);
}



/**
 * edt_body_loaded
 * This is executed as soon as the editor's document is ready for editing.
 *
 * @param idnum The ID number of the editor in question.
 */
function edt_body_loaded(idnum) {
	var edt = document.getElementById('edt_'+idnum+'_if');
	var ta = document.getElementById('edt_'+idnum+'_ta');

	//Assign default content.
	edt.contentWindow.document.body.innerHTML = ta.innerHTML;

	//Detect starting editor mode (or default).
	var mode = 'rich';
	if(ta.className) {
		var bits = ta.className.split('_');
		if(bits[1] && bits[1].length) { mode = bits[1]; }
	}

	//Set the editor mode.
	edt_set_mode(idnum, mode);
}


/**
 * edt_set_mode
 * Switch editor modes.
 *
 * @param idnum The ID number of the current editor
 * @param mode The string indicating the editor's mode
 * @return Whether or not the mode was set (true == success)
 */
function edt_set_mode(idnum, mode) {

	//Find previous mode.
	var pmdnode = document.getElementById('edt_'+idnum+'_fld_editormode');
	var pmode = pmdnode.getAttribute('value');

	//Check to see if we're switching at all.
	if(mode == pmode) { return(false); }

	//Disable new mode button.
	var btn = document.getElementById('edt_'+idnum+'_btn_mode_'+mode);
	btn.className = 'edt_btn edt_btn_mode_'+mode+' current';
	btn.onclick = function() { return(false); };

	//Enable previous mode button.
	if(pmode != '') {
		btn = document.getElementById('edt_'+idnum+'_btn_mode_'+pmode);
		btn.className = 'edt_btn edt_btn_mode_'+pmode;
		btn.onclick = edt_handle;
	}

	//Acquire document node references.
	var node = null;
	var buffer = '';
	var edt = document.getElementById('edt_'+idnum+'_if');
	var edoc = edt.contentWindow.document;
	var ctl0 = document.getElementById('edt_'+idnum+'_ctl_0');

	//Switching to markup mode.
	if(mode == 'markup') {
		if(edt_isIE) {
			buffer = edoc.body.innerHTML;
			edoc.body.innerText = buffer;
		}
		else {
			node = document.createTextNode(edoc.body.innerHTML);
			edoc.body.innerHTML = '';
			edoc.body.appendChild(node);
		}
		ctl0.style.visibility = 'hidden';
	}

	//Switching to rich edit mode.
	else if(mode == 'rich') {
		if(edt_isIE) {
			buffer = edoc.body.innerText;
			edoc.body.innerHTML = buffer;
		}
		else {
			node = edoc.body.ownerDocument.createRange();
			node.selectNodeContents(edoc.body);
			edoc.body.innerHTML = node.toString();
		}
		ctl0.style.visibility = 'visible';
	}

	//Switching to plain text mode.
	else if(mode == 'plain') {
		buffer = striptags(edoc.body.innerHTML);
		if(edt_isIE) {
			edoc.body.innerText = buffer;
		}
		else {
			node = document.createTextNode(buffer);
			edoc.body.innerHTML = '';
			edoc.body.appendChild(node);
		}
		ctl0.style.visibility = 'hidden';
	}

	//Update current mode status.
	pmdnode.setAttribute('value', mode);

	//Let's update the form.
	edt_update_form(idnum);

	//Set document's class for any special CSS.
	edoc.body.className = mode;

	//It looks like mode switch worked.
	return(true);
}


/*---------------------------------------------------------------------------
	II. Event Handlers
---------------------------------------------------------------------------*/

/**
 * edt_handle
 * Primary user interface event handler.
 *
 * The "parameters" to this function are passed by means of the calling
 * element's ID.  Thus, the handler can accept all user events and provide
 * high-level control of lower-level functions from one entry point.
 *
 * @return Boolean false (to abort links)
 */
function edt_handle() {
	var bits = this.id.split('_');
	var idnum = bits[1];
	var type = bits[2];
	var action = bits[3];
	var option = bits[4] ? bits[4] : null;
	var edt = document.getElementById('edt_'+idnum+'_if');
	var edoc = edt.contentWindow.document;

	//Check for all actions that need more info.
	if(type != 'dlg' && !option && (action == 'forecolor'
		|| action == 'hilitecolor'
		|| action == 'fontname'
		|| action == 'fontsize'
		|| action == 'formatblock'
		|| action == 'insertimage'
		|| action == 'browseimage'
		|| action == 'createlink'
		|| action == 'browselink'
		|| action == 'maillink'
	)) {
		edt_draw_dialogue(idnum, action, edt_get_coords(this));
		return(false);
	}

	//Close request.
	if(action == 'close') {
		//Do nothing.
	}

	//Check for mode switching.
	else if(action == 'mode') {
		if(option == 'plain') {
			if(confirm('Switching to plain mode will lose formatting.'
				+"\nPlease confirm mode switch.")) {
				edt_set_mode(idnum, option);
			}
		}
		else {
			edt_set_mode(idnum, option);
		}
	}

	//Misc commands.
	else if(action == 'misc') {
		var cheight = edt.offsetHeight;
		if(option == 'contract' && cheight > 120) {
			edt.style.height = (cheight-100).toString()+'px';
		}
		else if(option == 'expand') {
			edt.style.height = (cheight+100).toString()+'px';
		}
		else if(option == 'debug') {
			//edt_update_form(idnum);
			alert(edt.contentWindow.document.designMode);
			/*
			var buffer = this.id;
			var temp = edt.contentWindow.getSelection();
			var temp2 = temp.getRangeAt(0);
			alert(
				temp2.startOffset+', '+temp2.endOffset+"\n"+
				//temp2.extractContents()+"\n"+
				edoc.body.innerHTML.substr(temp2.startOffset,
					(temp2.endOffset-temp2.startOffset))
			);
			*/
		}
	}

	//Dialogues with special input.
	else if(type == 'dlg') {
//////////selection range detection
		var elem = null;
		if(action == 'createlink' || action == 'insertimage') {
			elem = document.getElementById('edt_'+idnum+'_dlg_'+action+'_0');
			option = elem.value;
			edoc.execCommand(action, false, option);
		}
		else if(action == 'maillink') {
			elem = document.getElementById('edt_'+idnum+'_dlg_'+action+'_0');
			option = 'mailto:'+elem.value;
			edoc.execCommand('createlink', false, option);
		}
//////////browseimage
//////////browselink
//////////inserttable

		//Set command using lookups from selection lists.
		else {
			var list = [];

			//Reference list based on the current action.
			switch(action) {
				case 'fontname': list = edt_fontlist; break;
				case 'fontsize': list = edt_sizelist; break;
				case 'formatblock': list = edt_blocklist; break;
			}

			//Scan the list to find the value from the key.
			var i = 0;
			for(k in list) {

				//The numerical option references the command value.
				if(i == option && k) {
					edoc.execCommand(action, false, k);
					break;
				}
				++i;
			}
		}
	}

////////////// my commands

	//Editor commands.
	else {
		if(action.indexOf('color') != -1) {
			option = '#'+option;
		}
		edoc.execCommand(action, false, option);
	}

	//Hide any dialogues.
	var test = document.getElementById('edt_'+idnum+'_dlg');
	if(test) { document.body.removeChild(test); }

	edt.contentWindow.focus();
	return(false);
}


/*---------------------------------------------------------------------------
	III. Dialogue Generators
---------------------------------------------------------------------------*/

/**
 * edt_draw_dialogue
 * Builds and displays a dialogue-type menu for additional user input.
 *
 * @param idnum The current editor's ID number
 * @param action The action the dialogue is performing
 * @param corner The top, left corner of the dialogue
 */
function edt_draw_dialogue(idnum, action, corner) {
	var test = document.getElementById('edt_'+idnum+'_dlg');
	if(test) { document.body.removeChild(test); }
	var btn = document.getElementById('edt_'+idnum+'_btn_'+action);
	var focuson = '';
	var dlg = document.createElement('div');
	dlg.id = 'edt_'+idnum+'_dlg';
	dlg.className = 'edt_dlg '+action;
	dlg.style.top = corner[0]+'px';
	dlg.style.left = corner[1]+'px';
	switch(action) {
		case 'forecolor': case 'hilitecolor':
			dlg.appendChild(edt_get_palette(idnum, action));
		break;
		case 'createlink':
			dlg.appendChild(edt_get_linkprompt(idnum, action,
				'Enter the address of the link.', 'http://'));
			focuson = 'edt_'+idnum+'_dlg_'+action+'_0';
		break;
		case 'insertimage':
			dlg.appendChild(edt_get_linkprompt(idnum, action,
				'Enter the address of the image.', 'http://'));
			focuson = 'edt_'+idnum+'_dlg_'+action+'_0';
		break;
		case 'maillink':
			dlg.appendChild(edt_get_linkprompt(idnum, action,
				'Enter the email address.', ''));
			focuson = 'edt_'+idnum+'_dlg_'+action+'_0';
		break;
		case 'fontname':
			dlg.appendChild(edt_get_menu(idnum, action, edt_fontlist));
		break;
		case 'fontsize':
			dlg.appendChild(edt_get_menu(idnum, action, edt_sizelist));
		break;
		case 'formatblock':
			dlg.appendChild(edt_get_menu(idnum, action, edt_blocklist));
		break;
//////////browseimage
//////////browselink
//////////inserttable
		case 'inserttable':
			alert('implement dialogue');
		break;
		case 'browselink':
			alert('implement dialogue');
		break;
		case 'browseimage':
			alert('implement dialogue');
		break;
	}
	var an = document.createElement('a');
	an.id = 'edt_'+idnum+'_btn_close';
	an.setAttribute('href', '#edt_'+idnum+'_btn_close');
	an.onclick = edt_handle;
	an.innerHTML = 'Cancel';
	var p = document.createElement('p');
	p.appendChild(an);
	dlg.appendChild(p);
	document.body.appendChild(dlg);
	if(focuson) {
		document.getElementById(focuson).focus();
	}
}


/**
 * edt_get_menu
 * Builds a DOM node that requests a user to select one entry from a list.
 *
 * @param idnum The ID of the current editor
 * @param action The action to perform
 * @param options The list of options as an associative array
 * @return The DOM node of the menu display
 */
function edt_get_menu(idnum, action, options) {
	var ul = document.createElement('ul');
	var li = null;
	var an = null;
	var i = 0;
	for(var k in options) {
		an = document.createElement('a');
		an.id = 'edt_'+idnum+'_dlg_'+action+'_'+i;
		an.className = 'edt_btn_'+action+'_'+i;
		an.onclick = edt_handle;
		an.setAttribute('href', '#edt_'+idnum+'_dlg_'+action+'_'+i);
		an.innerHTML = options[k];
		if(action == 'fontname') {
			an.style.fontFamily = k;
		}
		li = document.createElement('li');
		li.appendChild(an);
		ul.appendChild(li);
		++i;
	}
	return(ul);
}


/**
 * edt_get_linkprompt
 * Builds a DOM node that requests a user to enter link information.
 *
 * @param idnum The ID of the current editor
 * @param action The action to perform
 * @param prompt The user prompt text (field label)
 * @param defval The default value of the field
 * @return The DOM node of the prompt display
 */
function edt_get_linkprompt(idnum, action, prompt, defval) {
	var pr = document.createElement('div');
	var node = document.createElement('span');
	node.innerHTML = prompt;
	pr.appendChild(node);
	node = document.createElement('input');
	node.id = 'edt_'+idnum+'_dlg_'+action+'_0';
	node.setAttribute('value', defval);
	pr.appendChild(node);
	node = document.createElement('a');
	node.id = 'edt_'+idnum+'_dlg_'+action;
	node.setAttribute('href', '#edt_'+idnum+'_dlg_'+action);
	node.innerHTML = 'Insert';
	node.onclick = edt_handle;
	var spn = document.createElement('span');
	spn.appendChild(node);
	pr.appendChild(spn);
	return(pr);
}


/**
 * edt_get_palette
 * Build a color pallete.
 *
 * @param idnum The current editor's ID number
 * @param action The action to perform with the color
 * @return A DOM node of the palette
 */
function edt_get_palette(idnum, action) {
	var table, tbody, tr, td, an;
	tbody = document.createElement('tbody');
	for(var i = 0; i < edt_colors.length; ++i) {
		tr = document.createElement('tr');
		for(var j = 0; j < edt_colors[i].length; ++j) {
			an = document.createElement('a');
			an.id = 'edt_'+idnum+'_btn_'+action+'_'+edt_colors[i][j];
			an.setAttribute('href',
				'#edt_'+idnum+'_btn_'+action+'_'+edt_colors[i][j]);
			an.style.backgroundColor = '#'+edt_colors[i][j];
			an.onclick = edt_handle;
			an.innerHTML = '&nbsp;';
			td = document.createElement('td');
			td.appendChild(an);
			tr.appendChild(td);
		}
		tbody.appendChild(tr);
	}
	table = document.createElement('table');
	table.className = 'palette';
	table.appendChild(tbody);
	return(table);
}


/*---------------------------------------------------------------------------
	IV. DOM Convenience Functions
---------------------------------------------------------------------------*/

/**
 * edt_add_control
 * Adds an editor control to a given document object element.
 *
 * @param elem The DOM object to contain the new control
 * @param action A string representing the button's action
 * @param label A string describing the action to the user
 */
function edt_add_control(elem, action, label) {
	var idnum = elem.id.split('_')[1];
	var ctl = document.createElement('a');
	var node = null;
	ctl.id = 'edt_'+idnum+'_btn_'+action;
	ctl.className = 'edt_btn edt_'+action;
	ctl.setAttribute('href', '#edt_'+idnum+'_btn_'+action);
	ctl.setAttribute('title', label);
	//Fix for PNG transparency problems in IE.
	if(edt_isIE) {
		if(action.substr(0,4) != 'mode' && action.substr(0,4) != 'misc') {
			node = document.createElement('img');
			node.setAttribute('src', 'resources/icons/ie.gif');
			ctl.appendChild(node);
		}
		else {
			ctl.innerHTML = label;
		}
	}
	else {
		ctl.innerHTML = label;
	}
	ctl.onclick = edt_handle;
	elem.appendChild(ctl);
}


/**
 * edt_add_separator
 * Adds a simple separator to the given document object.
 *
 * @param elem The DOM object to contain the new separator
 * @param str The string to use for the separator's content
 */
function edt_add_separator(elem, str) {
	var sep = document.createElement('span');
	sep.innerHTML = str;
	elem.appendChild(sep);
}


/*---------------------------------------------------------------------------
	V. DOM Utilities
---------------------------------------------------------------------------*/

/*
function insert_node(idnum, node) {

}
*/

/*---------------------------------------------------------------------------
	VI. Other Utilities
---------------------------------------------------------------------------*/

/**
 * edt_update_forms
 * Updates all the real form fields with the current editor documents.
 *
 * @return Success of update (to prevent form submission)
 */
function edt_update_forms() {
	var frm = document.getElementById(edt_conf['form_id']);
	var divs = document.getElementsByTagName('div');
	var idnum;
	for(var i = 0, j = 0; i < divs.length; ++i) {
		if(divs[i].className == 'edt_parent') {
			edt_update_form(j);
			++j;
		}
	}
	return(true);
}


/**
 * edt_update_form
 * Update the form field for which we are editing.
 *
 * @param idnum The ID number of the editor to update
 */
function edt_update_form(idnum) {
	var ta = document.getElementById('edt_'+idnum+'_ta');
	var edt = document.getElementById('edt_'+idnum+'_if');
	ta.value = edt.contentWindow.document.body.innerHTML;
}


/**
 * edt_get_coords
 * Returns the bottom left point of a button for pull-down interfaces.
 *
 * @param elem The DOM element for which to find the position
 * @return The bottom, left pixel coordinates as a two-element array
 */
function edt_get_coords(elem) {
	var top_pos = elem.offsetTop;
	var left_pos = elem.offsetLeft;
	var elem_parent = elem.offsetParent;
	while(elem_parent) {
		top_pos += elem_parent.offsetTop;
		left_pos += elem_parent.offsetLeft;
		elem_parent = elem_parent.offsetParent;
	}
	return([top_pos + elem.offsetHeight, left_pos]);
}


/**
 * trim
 * Trim whitespace from the ends of a string.
 *
 * @param str The string to trim
 * @return The string without whitespace on either end
 */
function trim(str) {
	str = str.replace('/^\s+/', '');
	return(str.replace('/\s*$/', ''));
}


/**
 * striptags
 * Strip Markup Tags
 *
 * @param str The string to strip
 * @return A string without markup tags
 */
function striptags(str) {
	str = decode_entities(str);
	return(str.replace(/<[^>]+>/g,''));
}


/**
 * decode_entities
 * Converts HTML entity codes to literal character.
 *
 * @param str The string to decode
 * @return The decoded version of the string
 */
function decode_entities(str) {
	str = str.replace(/&lt;/g, '<');
	str = str.replace(/&gt;/g, '>');
	return(str.replace(/&quot;/g, '"'));
}

/**
 * encode_entities
 * Converts HTML entities into entity codes.
 *
 * @param str The string to encode
 * @return The encoded version of the string
 */
function encode_entities(str) {
	str = str.replace(/</g, '&lt;');
	str = str.replace(/>/g, '&gt;');
	return(str.replace(/"/g, '&quot;'));
}
