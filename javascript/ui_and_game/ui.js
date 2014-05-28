/*****************************************************************************
	UI Library
*****************************************************************************/

Element.prototype.hasClass = function(className) {
	if(this.className.length > 0) {
		var cs = this.className.split(' ');
		if(cs.indexOf(className) != -1) {
			return true;
		}
	}
	return false;
};

Element.prototype.setClass = function(className) {
	if(this.className.length > 0) {
		var cs = this.className.split(' ');
		if(cs.indexOf(className) == -1) {
			this.className += ' '+className;
		}
	}
	else {
		this.className = className;
	}
};

Element.prototype.clearClass = function(className) {
	if(this.className.length > 0) {
		var cs = this.className.split(' ');
		var ci = cs.indexOf(className);
		if(ci != -1) {
			cs.splice(ci, 1);
		}
		this.className = cs.join(' ');
	}
};

Element.prototype.getBox = function() {
	var cobj = this;
	var cleft = 0, ctop = 0;
	while(cobj.offsetParent) {
		cleft += cobj.offsetLeft;
		ctop += cobj.offsetTop;
		cobj = cobj.offsetParent;
	}
	return {'x':cleft, 'y':ctop, 'w':this.offsetWidth, 'h':this.offsetHeight};
};


/*****************************************************************************
	- this is a generalized interface... it's pretty much useless in the UI
		until an instantiated object is specialized into its purpose
*****************************************************************************/
function UIWidget() {
	this.ownerFrame = null;
	this.root = document.createElement('div');
	this.root.setClass('ui_widget');
}

UIWidget.prototype.getElement = function() {
	return this.root;
};


/*****************************************************************************
	The UIWidgetShop mutates widgets into something useful.
*****************************************************************************/
UIWidgetShop = {};
UIWidgetShop.makeLog = function(widget) {
	widget.root.setClass('ui_log');
	widget.append = function(message) {
		var line = document.createElement('div');
		line.appendChild(document.createTextNode(message));
		this.root.appendChild(line);
	};
};



/*****************************************************************************
Frames belong to several classes:
	- User frame (normal interaction, content)
	- Dialog (immediate interaction, errors)
	- UI control (top-level control; not application specific)
	- Generic (the fundamental frame; can be used directly)
Frames can control internal widget display:
	- Overflow: expand, scroll, clip, shrink(?)
		- All frames have minimum dimensions
		- Some frames have maximum dimensions
		- Some frames have fixed dimensions
		- Frames may set restrictions on total size or size of body
Frames should implement makeScalable (to resize a frame)
*****************************************************************************/
function UIFrame(title) {
	this.title = title;
	this.id = null;
	this.manager = null;
	this.hasFocus = false;
	this.widgets = [];
	this.root = document.createElement('div');
	this.root.style.position = 'absolute';
	this.root.setClass('ui_frame');
	this.conf = {
		'focusMode': 'mousedown'
	};
	this.ui = {
		'titlebar': null,
		'title': null,
		'control': null,
		'body': null
	};
	for(var k in this.ui) {
		this.ui[k] = document.createElement('div');
		this.ui[k].setClass('ui_'+k);
	}
	this.ui.title.appendChild(document.createTextNode(this.title));
	this.ui.titlebar.appendChild(this.ui.title);
	this.ui.titlebar.appendChild(this.ui.control);
	this.root.appendChild(this.ui.titlebar);
	this.root.appendChild(this.ui.body);
}

UIFrame.prototype.createWidget = function() {
	var wdg = new UIWidget();
	wdg.ownerFrame = this;
	return wdg;
};

UIFrame.prototype.addWidget = function(widget) {
	var uikey = arguments[1] ? arguments[1] : 'body';
	this.ui[uikey].appendChild(widget.getElement());
	this.widgets.push(widget);
};

UIFrame.prototype.removeWidget = function(widget) {
	////
};

UIFrame.prototype.getElement = function() {
	return this.root;
};

UIFrame.prototype.setFocusMode = function(mode) {
	if(this.conf.focusListener != null) {
		this.root.removeEventListener(
			this.conf.focusMode,
			this.conf.focusListener,
			true
		);
	}
	this.conf.focusMode = mode;
	this.conf.focusListener = this.getFocusListener(mode);
	this.root.addEventListener(mode, this.conf.focusListener, true);
};

UIFrame.prototype.receiveFocus = function() {
	this.hasFocus = true;
	this.root.setClass('ui_focus');
};

UIFrame.prototype.receiveBlur = function() {
	this.hasFocus = false;
	this.root.clearClass('ui_focus');
};

UIFrame.prototype.makeDraggable = function() {
	this.dragBox = arguments[0] ? arguments[0] : null;
	this.root.style.position = 'absolute';
	this.root.setClass('ui_draggable');
	this.ui.titlebar.addEventListener(
		'mousedown',
		this.getDragListener('mousedown'),
		true
	);
};

UIFrame.prototype.getDragListener = function(type) {
	var context = this;
	if(type == 'mousemove') {
		return function(e) {
			//Calculate where the element should move to.
			var newx = context.offX + e.clientX;
			var newy = context.offY + e.clientY;
			//Check to see if the dragging is limited to a box.
			if(context.dragBox != null) {
				//X limited to left edge
				if(newx < context.dragBox.x) {
					newx = context.dragBox.x;
				}
				//X limited to right edge
				else if(newx > context.maxX) {
					newx = context.maxX;
				}
				//Y limited to top edge
				if(newy < context.dragBox.y) {
					newy = context.dragBox.y;
				}
				//Y limited to bottom edge
				else if(newy > context.maxY) {
					newy = context.maxY;
				}
			}
			//Update element's position.
			context.root.style.left = newx + 'px';
			context.root.style.top = newy + 'px';
		};
	}
	else if(type == 'mousedown') {
		return function(e) {
			//Inform UI of dragging.
			context.manager.setFocus(context);
			context.root.clearClass('ui_draggable');
			context.root.setClass('ui_dragging');
			//Calculate offset from cursor to element's origin.
			var start = context.root.getBox();
			context.offX = start.x - e.clientX;
			context.offY = start.y - e.clientY;
			//If limited, calculate maximum position values.
			if(context.dragBox != null) {
				context.maxX = (context.dragBox.x+context.dragBox.w)-start.w;
				context.maxY = (context.dragBox.y+context.dragBox.h)-start.h;
			}
			//Assign listeners to drag and finish dragging.
			context.moveListener = context.getDragListener('mousemove');
			context.upListener = context.getDragListener('mouseup');
			window.addEventListener('mousemove', context.moveListener, true);
			window.addEventListener('mouseup', context.upListener, true);
			e.stopPropagation();
			e.preventDefault();
		};
	}
	else {
		return function(e) {
			context.root.clearClass('ui_dragging');
			context.root.setClass('ui_draggable');
			window.removeEventListener('mousemove',context.moveListener,true);
			window.removeEventListener('mouseup', context.upListener, true);
		};
	}
};

UIFrame.prototype.getFocusListener = function(type) {
	var context = this;
	switch(type) {
		case 'mouseover':
		case 'mousedown':
		case 'click':
			return function(e) {
				context.manager.setFocus(context);
			};
		break;
		default:
			return function(e) {};
		break;
	}
};


UIFrameShop = {};
UIFrameShop.addClose = function(frame) {
	frame.ui.control.appendChild(frame.manager.getGraphic('act_close'));
};


/*****************************************************************************
	The UIManager controls the overall UI.  Multiple UI managers may be used
	within a single application, but they should be used on different target
	elements.  After the UIManager is instantiated, all other objects should
	be created through their factory interfaces (UIManager::createFrame(),
	UIFrame::createWidget) to ensure proper setup and communication between
	UI components.
*****************************************************************************/
function UIManager() {
	this.root = arguments[0] ? arguments[0]
		: document.getElementsByTagName('body')[0];
	this.frames = [];
	this.last_frame_id = -1;
	this.focus = null;
	this.svgns = 'http://www.w3.org/2000/svg';
	this.graphics = null;
	this.slowgraphics = [];
	this.conf = {
		'box': null,
		'geom': {},
		'layout': 'grid', //|free|bounded|split
		'newMode': 'cascade', //|tile|cascade|center|topleft
			//needs concept of UI boundaries, depends on layout
		'docking': false, //|# pixels to trigger dock
		'zStart': arguments[1] ? arguments[1] : 1,
		'focusMode': 'mouseover', //|mousedown|click
		'lockout': false
	};
	this.strata = {
		'error':		[ (128 + this.conf.zStart), 2  ],
		'preempt':		[ (120 + this.conf.zStart), 2  ],
		'managetop':	[ (110 + this.conf.zStart), 10 ],
		'focus':		[ (100 + this.conf.zStart), 1  ],
		'blur':			[ (20  + this.conf.zStart), 80 ],
		'groups':		[ (5   + this.conf.zStart), 10 ],
		'guides':		[ (2   + this.conf.zStart), 1  ],
		'overflow':		[ (1   + this.conf.zStart), 1  ]
	};
	this.loadGraphics();
	this.updateGeometry();
}

UIManager.prototype.createFrame = function(title) {
	var frame = new UIFrame(title);
	++this.last_frame_id;
	frame.id = this.last_frame_id;
	frame.manager = this;
	frame.setFocusMode(this.conf.focusMode);
	return frame;
};

UIManager.prototype.addFrame = function(frame) {
	for(var i in this.frames) {
		if(this.frames[i].id == frame.id) {
			return false;
		}
	}
	this.root.appendChild(frame.getElement());
	this.frames.push(frame);
	this.setFocus(frame);
	return true;
};

UIManager.prototype.removeFrame = function(frame) {
//////////////needs to handle refocusing
	for(var i in this.frames) {
		if(this.frames[i].id = frame.id) {
			this.frames.splice(i,1);
			return true;
		}
	}
	return false;
};

UIManager.prototype.setFocus = function(frame) {
	if(this.conf.lockout) { return; }
	/**
	focus events may be initiated by the frame, a widget, or the manager
	**/
	//build a new frame list, and update zIndexes
	var flist = [];
	var j = 0;
	for(var i in this.frames) {
		if(this.frames[i].id != frame.id) {
			flist.push(this.frames[i]);
			this.frames[i].getElement().style.zIndex = this.strata.blur[0]+j;
			++j;
		}
	}
	flist.push(frame);
	this.frames = flist;
	//blur previously focused frame
	if(this.focus != null) {
		this.focus.receiveBlur();
	}
	//set current focus frame
	this.focus = frame;
	//update zIndex of target
	frame.getElement().style.zIndex = this.strata.focus[0];
	//inform frame object that it is the focus
	this.focus.receiveFocus();
};

UIManager.prototype.updateGeometry = function() {
	this.conf.geom.document = {
		'x': 0,
		'y': 0,
		'w': document.documentElement.scrollWidth,
		'h': document.documentElement.scrollHeight
	};
	this.conf.geom.viewport = {
		'x': window.pageXOffset,
		'y': window.pageYOffset,
		'w': document.documentElement.clientWidth,
		'h': document.documentElement.clientHeight
	};
	this.conf.geom.element = this.root.getBox();
};

UIManager.prototype.debug = function(message) {
	if(this.dbg == null) {
		for(var i in this.frames) {
			if(this.frames[i].title == 'Debug') {
				this.dbg = this.frames[i];
				break;
			}
		}
	}
	if(this.dbg != null) {
		this.dbg.widgets[0].append(message);
	}
};

UIManager.prototype.showError = function(message) {
	//currently relies on CSS, and locks out the entire page
	//   would like to only lock out real boundaires of UIManager
	var lock = document.createElement('div');
	lock.id = 'ui_error_lockout';
	lock.className = 'ui_lockout';
	lock.style.zIndex = this.strata.error[0];
	this.root.appendChild(lock);
	var err = this.createFrame('Error');
	var msg = err.createWidget();
	//UIWidgetShop.makeAck(txt);
	//temp:
	msg.getElement().appendChild(document.createTextNode(message));
	err.addWidget(msg);
	this.addFrame(err);
	err.getElement().style.zIndex = this.strata.error[0];
	this.conf.lockout = true;
};

UIManager.prototype.loadGraphics = function() {
	var xhr = new XMLHttpRequest();
	var context = this;
	xhr.addEventListener(
		'readystatechange',
		(function(e) {
			if(this.readyState == 4) {
				//200:HTTP OK; 0:Local File
				if(this.status == 200 || this.status == 0) {
					var svg = this.responseXML.getElementsByTagName('svg');
					if(svg.length > 0) {
						context.graphics = this.responseXML;
						context.finishGraphics();
					}
				}
			}
		}),
		false
	);
	xhr.open('GET', 'ui.svg', true);
	xhr.overrideMimeType('text/xml');
	xhr.send();
};

UIManager.prototype.finishGraphics = function() {
	if(this.slowgraphics.length > 0) {
		for(var i in this.slowgraphics) {
			this.slowgraphics[i].parentNode.replaceChild(
				this.getGraphic(this.slowgraphics[i].id),
				this.slowgraphics[i]
			);
		}
	}
};

UIManager.prototype.getGraphic = function(gid) {
	var graphic = null;
	//If the graphics haven't been loaded yet, add this request to a wait
	//  list, and respond with an empty graphic.  Once the graphics have
	//  been loaded, a followup routine (UIManager::finishGraphics()) will
	//  replace all these empty graphics with the real version.
	if(this.graphics == null) {
		graphic = document.createElementNS(this.svgns, 'svg');
		graphic.id = gid;
		graphic.setAttribute('width', '18');
		graphic.setAttribute('height', '18');
		this.slowgraphics.push(graphic);
		return graphic;
	}
	graphic = this.graphics.getElementById(gid);
	//If a graphic ID doesn't exist, build a graphic to signal an error.
	if(graphic == null) {
		graphic = document.createElementNS(this.svgns, 'svg');
		graphic.id = 'fail';
		graphic.setAttribute('width', '18');
		graphic.setAttribute('height', '18');
		var text = document.createElementNS(this.svgns, 'text');
		text.setAttribute('x', '4');
		text.setAttribute('y', '14');
		text.style.fill = '#FF0066';
		text.style.fontWeight = 'bold';
		text.appendChild(document.createTextNode('?'));
		graphic.appendChild(text);
	}
	//////// need to update the ID so we don't have ID conflicts in UI
	return graphic;
};

