/**
 * AnimationSequence
 *
 * @author Zac Hester
 * @date 2007-10-09
 * @version 1.1.0
 *
 * This is a small project I built after many hours of coding my own
 * setInterval handlers for each project where some small amount of
 * animation was required.  This class abstracts the messy details into
 * a friendly, Flash-like interface for quickly adding animated transitions
 * to DOM elements.  Besides the style properties that are actually altered
 * during frame animation, all the styling is entirely controlled by the
 * page where the elements live.  Therefore, motion animations that involve
 * adjusting things like "left" and "top," will need to be performed on
 * elements where that will have some effect (usually via relative or
 * absolute positioning).
 *
 * Some example code:
 *
 *   var as = new AnimationSequence();
 *   var tgt = as.addTarget(document.getElementById('test'));
 *   var seg = tgt.addSegment(100);
 *   var tf = seg.addTransform(as.props.backgroundColor, 'red', 'blue');
 *   as.compile();
 *   as.play();
 *
 * Revision History
 * 2007-10-09 - 1.1.0
 *   Added primitive event triggers.
 * 2007-10-05 - 1.0.0
 *   First working version (no event triggers yet).
 *
 * To Do
 * - Test event triggers.
 * - Test playing from/to indices.
 * - Add value checking for play(start, stop)
 * - Fix backgroundPosition animation properties.
 * - Null animation property checking.
 *
 * @param frames_per_second Optionally specify the FPS setting.
 */
function AnimationSequence() {

	//Set up sequence constants.
	this.fps = arguments[0] ? arguments[0] : 20;
	this.frame_delay = Math.round(1000 / this.fps);
	this.frame_index = 0;
	this.frame_total = 0;
	this.frame_limit = 0;
	this.is_playing = false;
	this.loop = false;

	//Interval handle.
	this.interval;

	//Transformable property list.
	this.props = [];

	//List of animation targets.
	this.targets = [];

	//Event listeners.
	this.onplay = null;   //User requested play.
	this.onpause = null;  //User requested pause.
	this.onstop = null;   //User requested stop.
	this.onloop = null;   //Animation traversed end to start again.
	this.onfinish = null; //Animation completed all frames.


	/**
	 * Constructor.
	 */
	this.init = function() {

		//Build the list of transformable properties.
		this.addProperty('top', 'px', ['style','top']);
		this.addProperty('left', 'px', ['style','left']);
		this.addProperty('bottom', 'px', ['style','bottom']);
		this.addProperty('right', 'px', ['style','right']);
		this.addProperty('width', 'px', ['style','width']);
		this.addProperty('height', 'px', ['style','height']);
		this.addProperty('zIndex', '', ['style','zIndex'], 'rounded');
		this.addProperty('color', '', ['style','color'], 'colorHex');
		this.addProperty('borderWidth', 'px', ['style','borderWidth']);
		this.addProperty('borderColor', '', ['style','borderColor'], 'color');
		this.addProperty('backgroundColor',
			'', ['style','backgroundColor'], 'color');
		this.addProperty('backgroundPosition',
			'px', ['style','backgroundPosition'], 'position');
		this.addProperty('null', null, null);

		//Opacity is a special one since we need extra stuff for MSIE.
		prop = this.addProperty('opacity', '', ['style','opacity']);
		prop.setSpecial = function(node, value) {
			node.style.filter = 'alpha(opacity='+Math.round(value*100)+')';
		};
	};


	/**
	 * addProperty
	 * Adds a new transformable property to the animation sequence.
	 */
	this.addProperty = function(label, unit, path) {

		//Create the property and alias using the label.
		var prop = null;
		if(arguments[3]) {
			prop = new ASProperty(unit, path, arguments[3]);
		}
		else {
			prop = new ASProperty(unit, path);
		}
		this.props[label] = prop;
		return(prop);
	};


	/**
	 * addTarget
	 * Adds a target object for animation.
	 */
	this.addTarget = function(node) {

		//Create a new target object.
		var tgt = new ASTarget(node);

		//Add it to the list.
		this.targets.push(tgt);

		//Return the target object handle.
		return(tgt);
	};


	/**
	 * establishTimeline
	 * Scans all animation objects for important information about the
	 * global timeline.
	 */
	this.establishTimeline = function() {

		//Local stuff.
		var tgt, seg, seg_count;
		this.frame_total = 0;

		//Scan all the target objects.
		for(var i = 0; i < this.targets.length; ++i) {
			tgt = this.targets[i];
			seg_count = 0;

			//Check each target's segments.
			for(var j = 0; j < tgt.segments.length; ++j) {
				seg = tgt.segments[j];
				seg_count += seg.frames;
			}

			//Check the total frames.
			if(seg_count > this.frame_total) {
				this.frame_total = seg_count;
				this.frame_limit = this.frame_total;
			}
		}

		//Make sure we have something to do.
		return(this.frame_total);
	};


	/**
	 * setFrameHandler
	 * Sets the primary frame event handler that will respond to repeated
	 * calls from setInterval().  This is where the frame pointer and
	 * timeline termination are maintained.
	 */
	this.setFrameHandler = function() {
		var ctx = this;
		return(function() {

			//We are still rendering frames.
			if(ctx.frame_index < ctx.frame_limit) {

				//Tell all target objects the current global frame.
				for(var i = 0; i < ctx.targets.length; ++i) {
					ctx.targets[i].renderFrame(
						ctx.frame_index,
						ctx.targets[i]
					);
				}

				//Increment frame index.
				ctx.frame_index++;
			}

			//We've reached the end of the animation.
			else {

				//Check for a loop.
				if(ctx.loop) {

					//Reset frame index.
					ctx.frame_index = 0;

					//Check for event listener.
					if(ctx.onloop) {
						ctx.onloop();
					}
				}

				//Otherwise, stop animation.
				else {
					clearInterval(ctx.interval);
					ctx.interval = null;
					ctx.is_playing = false;

					//Check for event listener.
					if(ctx.onfinish) {
						ctx.onfinish();
					}
				}
			}
		});
	};


	/**
	 * compile
	 * Allows some optimization of the animation sequence by handling
	 * initial calculations before animation is necessary.
	 */
	this.compile = function() {

		//Set up timeline basics, given our provided animation data.
		this.establishTimeline();
	};


	/**
	 * play
	 * Plays the animation sequence.
	 */
	this.play = function() {

		//Check for scope context.
		var ctx = arguments[2] ? arguments[2] : this;

		//Check for starting index.
		if(arguments[0]) {
			ctx.frame_index = arguments[0];
		}

		//Check for limit index.
		if(arguments[1]) {
			ctx.frame_limit = arguments[1];
		}

		//Check to see if they're trying to play again.
		if(!ctx.is_playing) {

			//If we're at the end and someone wants to play,
			//  reset frame pointer.
			if(ctx.frame_index >= (ctx.frame_total-1)) {
				ctx.frame_index = 0;
			}
	
			//Make sure there's something to play.
			if(ctx.frame_total) {
	
				//Begin frame rendering interval.
				ctx.interval = setInterval(
					ctx.setFrameHandler(),
					ctx.frame_delay
				);
			}

			//We're playing.
			ctx.is_playing = true;

			//Check for event listener.
			if(ctx.onplay) {
				ctx.onplay();
			}
		}
	};


	/**
	 * playLoop
	 * Does the same thing as play(), but sets a loop flag so the animation
	 * will repeat once the timeline has reached the end.
	 */
	this.playLoop = function() {

		//Check for scope context.
		var ctx = arguments[1] ? arguments[1] : this;

		//Set loop variable.
		ctx.loop = true;

		//Play it.
		ctx.play();
	};


	/**
	 * pause
	 * Terminates animation without altering internal pointers.
	 */
	this.pause = function() {

		//Check for scope context.
		var ctx = arguments[1] ? arguments[1] : this;

		//Stop the frame intervals.
		clearInterval(ctx.interval);
		ctx.interval = null;

		//Not playing.
		ctx.is_playing = false;

		//Check for event listener.
		if(ctx.onpause) {
			ctx.onpause();
		}
	};


	/**
	 * stop
	 * Terminates animation, resets internal pointers, and requests all
	 * animation targets to set themselves to their initial frame.
	 */
	this.stop = function() {

		//Check for scope context.
		var ctx = arguments[1] ? arguments[1] : this;

		//Stop the frame intervals.
		clearInterval(ctx.interval);
		ctx.interval = null;

		//Not playing.
		ctx.is_playing = false;

		//Reset frame index.
		ctx.frame_index = 0;

		//Reset loop flag.
		ctx.loop = false;

		//Tell all target objects to render their first frame.
		for(var i = 0; i < ctx.targets.length; ++i) {
			ctx.targets[i].renderFrame(0, ctx.targets[i]);
		}

		//Check for event listener.
		if(ctx.onstop) {
			ctx.onstop();
		}
	};


	//Initialize this object.
	this.init();
}



/**
 * ASProperty
 * Complex data type for managing transformable property specifications.
 *
 * @param property_unit
 * @param property_path
 * @param property_value_type
 */
function ASProperty(property_unit, property_path) {

	//The CSS unit (if necessary).
	this.unit = property_unit;

	//The property object path as an array of property names.
	this.path = property_path;

	//The type of delta calculation for this property.
	this.delta = arguments[2] ? arguments[2] : 'length';


	/**
	 * setUnit
	 * Alter this property's unit string.
	 */
	this.setUnit = function(new_unit) {

		//Set object variable.
		this.unit = new_unit;
	};
}


/**
 * ASTarget
 * Provides the storage and control for an animated object.  The primary
 * responsibilities of this class is to organize the animation segments.
 *
 * @param target_node
 */
function ASTarget(target_node) {

	//Save a reference to the node.
	this.node = target_node;

	//List of animation segments for this target.
	this.segments = [];


	/**
	 * addSegment
	 * Creates a new animation segment for the target.
	 */
	this.addSegment = function(frame_count) {
		var seg = new ASSegment(frame_count, this.node);
		this.segments.push(seg);
		return(seg);
	};


	/**
	 * renderFrame
	 * Translates the global frame index to a local index for the correct
	 * animation segment within the target.
	 */
	this.renderFrame = function(frame_index) {

		//Check for scope context.
		var ctx = arguments[1] ? arguments[1] : this;

		//Segment frame pointers.
		var lower_bound = 0;
		var upper_bound = 0;
		var seg = null;

		//Scan animation segments to find out which one we're in.
		for(var i = 0; i < ctx.segments.length; ++i) {

			//Shortcut.
			seg = ctx.segments[i];

			//Increment upper limit of segment.
			upper_bound += seg.frames;

			//Check if this is the current segment.
			if(frame_index >= lower_bound && frame_index < upper_bound) {

				//Tell the segment what frame it needs to run.
				seg.renderTransforms(
					frame_index - lower_bound,
					seg
				);
				break;
			}

			//Increment lower bound of segment.
			lower_bound += seg.frames;
		}
	};
}


/**
 * ASSegment
 * Controls animation segments within a target element.
 *
 * @param segment_frames Number of frames to animate inside this sequence.
 * @param segment_node The node being animated.
 */
function ASSegment(segment_frames, segment_node) {

	//The total number of frames in this segment.
	this.frames = segment_frames;

	//The node we are animating.
	this.node = segment_node;

	//The list of transforms accomplished in this segment.
	this.transforms = [];

	//Event listeners.
	this.onenter = null;
	this.onexit = null;


	/**
	 * addTransform
	 * Creates a new property transform for this animation segment.
	 */
	this.addTransform = function(prop, ini_val, fin_val) {
		var tf = null;
		if(arguments[3]) {
			tf = new ASTransform(prop, ini_val, fin_val, arguments[3]);
		}
		else {
			tf = new ASTransform(prop, ini_val, fin_val);
		}
		this.transforms.push(tf);
		return(tf);
	};


	/**
	 * renderTransforms
	 * Based on the segment index, performs each of the transforms needed
	 * for this position within the segment.
	 */
	this.renderTransforms = function(segment_index) {

		//Check for scope context.
		var ctx = arguments[1] ? arguments[1] : this;
		var coeff = 0;
		var trans = null;

		//Scan the list of transforms.
		for(var i = 0; i < ctx.transforms.length; ++i) {

			//Shotcut.
			trans = ctx.transforms[i];

			//Set the property of the target element to its new value.
			ctx.setProperty(
				ctx.node,
				trans.property,
				ASTypeDelta[trans.property.delta](
					trans.initial_value,
					trans.final_value,
					trans.motion(segment_index / (ctx.frames-1))
				)
			);
		}

		//Check for listener and trigger.
		if(ctx.onenter && segment_index == 0) {
			ctx.onenter();
		}
		if(ctx.onexit && segment_index == (ctx.frames-1)) {
			ctx.onexit();
		}
	};


	/**
	 * setProperty
	 * Sets the property of a specified object using the property list.
	 */
	this.setProperty = function(node, property, value) {

		//var debug = document.getElementById('debug');
		//debug.innerHTML += 'Setting value '+value+'<br />';

		//Check for scope context.
		var ctx = arguments[3] ? arguments[3] : this;

		//Descend the property tree using the "path" list.
		cnode = node;
		for(var i = 0; i < property.path.length - 1; ++i) {
			cnode = cnode[property.path[i]];
		}

		//Set the value of this property.
		cnode[property.path[property.path.length-1]] = value + property.unit;

		//Check for special case properies.
		if(property.setSpecial) {
			property.setSpecial(node, value);
		}
	};
}


/**
 * ASTransform
 * Complex data type to track transformation parameters.
 *
 * @param prop The property information object.
 * @param ini_val The initial value of this property.
 * @param fin_val The final value of this property.
 * @param motion The motion displacement evaluation function.
 */
function ASTransform(prop, ini_val, fin_val) {

	//Set basic values for this transform.
	this.property = prop;
	this.initial_value = ini_val;
	this.final_value = fin_val;
	this.motion = arguments[3] ? arguments[3] : ASMotion.linear;
}


/**
 * ASMotion
 * Predefined motion displacement calculations.  All functions must take
 * a variable indicating progress through the range as a fraction from 0
 * to 1.  All functions return a number between 0 and 1 indicating how
 * much displacement should occur at that interval.
 */
ASMotion = {

	/**
	 * linear
	 * Linear translation.
	 * y = x
	 */
	linear: function(frame_delta) {
		return(frame_delta);
	},

	/**
	 * acceleration
	 * Quadratic unity acceleration (third-order intensity).
	 * y = x ^ 3
	 */
	acceleration: function(frame_delta) {
		return(Math.pow(frame_delta, 3));
	},

	/**
	 * deceleration
	 * Quadratic unity deceleration (third-order intensity).
	 * y = ((x - 1) ^ 3) + 1
	 */
	deceleration: function(frame_delta) {
		return(Math.pow((frame_delta - 1), 3) + 1);
	},

	/**
	 * cubic
	 * Provides a cubic "S" curve.
	 * y = (((x - (1 - x)) ^ 3) + 1) / 2
	 */
	cubic: function(frame_delta) {
		return((Math.pow((frame_delta - (1 - frame_delta)), 3) + 1) / 2);
	}
};


/**
 * ASTypeDelta
 * Each property type may have special delta evaluation needs based on
 * CSS requirements (e.g. color deltas and length deltas are computed
 * very differently).  This provides a uniform interface for evaluating
 * each delta based on the property data type.
 */
ASTypeDelta = {

	/**
	 * length
	 * Intermediate length calculation.
	 */
	length: function(ini, fin, delta) {
		return(((fin - ini) * delta) + ini);
	},

	/**
	 * opacity
	 * Intermediate opacity calculation.	 
	 */
	opacity: function(ini, fin, delta) {
		return(((fin - ini) * delta) + ini);
	},

	/**
	 * color
	 * Intermediate color calculation.	 
	 */
	color: function(ini, fin, delta) {
		return(colorStep(ini, fin, delta));
	},

	/**
	 * colorHex
	 * Intermediate color calculation (strict input variables [#RRGGBB]).	 
	 */
	colorHex: function(ini, fin, delta) {
		return(colorStepFast(ini, fin, delta));
	},

	/**
	 * position
	 * Intermediate position calculation.	 
	 */
	position: function(ini, fin, delta) {
////////////
		return('');
	},

	/**
	 * rounded
	 * Only allows integers.
	 */
	rounded: function(ini, fin, delta) {
		return(Math.round(((fin - ini) * delta) + ini));
	}
};


/**
 * word2hex
 * Converts a valid CSS color name into a hex string.
 *
 * @param word The name of the color.
 * @return The standard color representation of the color.
 */
function word2hex(word) {
	switch(word) {
		case 'aqua': return('#00FFFF');
		case 'black': return('#000000');
		case 'blue': return('#0000FF');
		case 'fuchsia': return('#FF00FF');
		case 'gray': return('#808080');
		case 'green': return('#008000');
		case 'lime': return('#00FF00');
		case 'maroon': return('#800000');
		case 'navy': return('#000080');
		case 'olive': return('#808000');
		case 'purple': return('#800080');
		case 'red': return('#FF0000');
		case 'silver': return('#C0C0C0');
		case 'teal': return('#008080');
		case 'white': return('#FFFFFF');
		case 'yellow': return('#FFFF00');
	}
	return(word);
}


/**
 * short2long
 * Converts shortened color specifications (e.g. #FFF) to standard color
 * specifications (e.g. #FFFFFF).
 *
 * @param hex The short hex value.
 * @return The full hex value.
 */
function short2long(hex) {
	if(hex.length == 4) {
		var r = hex.substr(1, 1);
		var g = hex.substr(2, 1);
		var b = hex.substr(3, 1);
		return('#'+r+r+g+g+b+b);
	}
	return(hex);
}


/**
 * colorStep
 * Calculates an intermediate color between two colors given the "distance"
 * between the colors.
 * Color input values are very robust.  This includes using any of the
 * sixteen, CSS-defined color names (in lowercase).
 *
 * @param initial_color The color we're departing.
 * @param final_color The color we're approaching.
 * @param delta The amount we've traveled between colors (between 0 and 1).
 * @return A new color value spaced appropriately between the two colors.
 */
function colorStep(initial_color, final_color, delta) {

	//Check for color words.
	initial_color = word2hex(initial_color);
	final_color = word2hex(final_color);

	//Check for abbreviated colors.
	initial_color = short2long(initial_color);
	final_color = short2long(final_color);

	//Run the optimized code.
	return(colorStepFast(initial_color, final_color, delta));
}


/**
 * colorStepFast
 * Optimized, less idiot-proof version of colorStep.
 *
 * @param initial_color Starting color of the form #RRGGBB
 * @param final_color Final color of the form #RRGGBB
 * @param delta The amount of progress between the colors.
 * @return A new color value spaced appropriately between the two colors.
 */
function colorStepFast(initial_color, final_color, delta) {

	//Parse spectrum values.
	var ri = parseInt(initial_color.substr(1,2), 16);
	var gi = parseInt(initial_color.substr(3,2), 16);
	var bi = parseInt(initial_color.substr(5,2), 16);
	var rf = parseInt(final_color.substr(1,2), 16);
	var gf = parseInt(final_color.substr(3,2), 16);
	var bf = parseInt(final_color.substr(5,2), 16);

	//Calculate new spectrum values and format in 2-digit hex.
	var rn = Math.round(((rf - ri) * delta) + ri).toString(16);
	var gn = Math.round(((gf - gi) * delta) + gi).toString(16);
	var bn = Math.round(((bf - bi) * delta) + bi).toString(16);
	rn = rn.length == 1 ? '0'+rn : rn;
	gn = gn.length == 1 ? '0'+gn : gn;
	bn = bn.length == 1 ? '0'+bn : bn;

	//Combine and return.
	return('#'+rn+gn+bn);
}
