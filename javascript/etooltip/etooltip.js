/****************************************************************************
	Enhanced Tooltip
	Zac Hester - 2006-11-09
	Based on some messy, ancient code from http://www.dynamicdrive.com/

	Styling notes: This dynamically adds a div with an id of "etooltip" to
	the document body.  Access this in CSS to adjust it's appearance.
	(Just don't mess with any positioning rules.)
****************************************************************************/

//Basic settings and globals.
var x_custom = -40;
var y_custom = 22;
var ett_active = false;
var is_IE = document.all ? true : false;

//Fire this onmouseover on the element you are showing the tip over.
function show_tooltip(contents) {
	var node = document.createElement('div');
	node.id = 'etooltip';
	node.innerHTML = contents;
	node.style.position = 'absolute';
	node.style.left = '-2000px';
	document.body.appendChild(node);
	ett_active = true;
}

//Fire this onmouseout on the starting element.
function hide_tooltip() {
	document.body.removeChild(document.getElementById('etooltip'));
	ett_active = false;
}

//This tracks the mouse when the tooltip is visible (fired continuously).
document.onmousemove = function(e) {
	if(ett_active) {

		//Tooltip.
		var ett = document.getElementById('etooltip');

		//Current mouse and window coordinates.
		var curr_x, curr_y, right_edge, bottom_edge;
		var left_check = x_custom < 0 ? Math.abs(x_custom) : 0;

		//Calculate proper mouse/viewing positions.
		if(is_IE) {
			curr_x = event.clientX + document.body.scrollLeft;
			curr_y = event.clientY + document.body.scrollTop;
			right_edge = document.body.clientWidth - event.clientX - x_custom;
			bottom_edge = document.body.clientHeight - event.clientY - y_custom;
		}
		else {
			curr_x = e.pageX;
			curr_y = e.pageY;
			right_edge = window.innerWidth - e.clientX - x_custom;
			bottom_edge = window.innerHeight - e.clientY - y_custom;
		}

		//Check for horizontal collision and move appropriately.
		if(right_edge < ett.offsetWidth) {
			ett.style.left = '';
			ett.style.right = '5px';
		}
		else if(curr_x < left_check) {
			ett.style.left = '5px';
			ett.style.right = '';
		}
		else {
			ett.style.left = (curr_x + x_custom) + 'px';
			ett.style.right = '';
		}

		//Check for vertical collision and move appropriately.
		if(bottom_edge < ett.offsetHeight) {
			ett.style.top = '';
			ett.style.bottom = '5px';
		}
		else {
			ett.style.top = (curr_y + y_custom) + 'px';
			ett.style.bottom = '';
		}
	}
}
