/*****************************************************************************
	CSS DOM Extension
	Zac Hester
	2011-07-12

	Example Usage:
	var cd = document.styleSheets[0];
	cd.applyStyle('h1', 'color', '#333333');
	cd.applyStyle('.highlight', 'background-color', '#FFFFEE');
	cd.removeStyle('blockquote');
*****************************************************************************/

/**
 * CSSStyleSheet::applyStyle
 * Applies a given CSS property/value pair to all style sheet rules that
 * match the given selector.  If the selector is not present in the style
 * sheet, a new rule is created for the given selector.
 *
 * @param selector The selector to match against existing selectors
 * @param property The CSS declaration property
 * @param value The CSS declaration value
 * @param inclusive Optionally set to true to match all selectors that have
 *                  one selector item that matches the specified selector
 * @return True if the style was successfully applied
 */
CSSStyleSheet.prototype.applyStyle = function(selector, property, value) {
	var inclusive = arguments[3] ? arguments[3] : false;
	var text = '';
	var rules = this.getRulesBySelector(selector, inclusive);
	var nrules = rules.length;
	if(nrules) {
		text = property.replace(/-(\w)/g,
			function(m, n) { return n.toUpperCase(); } );
		for(var i = 0; i < nrules; ++i) {
			rules[i].style[text] = value;
		}
	}
	else {
		text = selector+' { '+property+': '+value+'; }';
		this.insertRule(text, this.cssRules.length);
	}
	return true;
};


/**
 * CSSStyleSheet::removeStyle
 * Removes a style from the style sheet by passing a selector.  The selector
 * must be a functionally equivalent selector for the target rule.
 *
 * @param selector The selector to match against existing selectors
 * @return True if a style was successfully removed
 */
CSSStyleSheet.prototype.removeStyle = function(selector) {
	var found = false;
	var nrules = this.cssRules.length;
	var rule = null;
	for(var i = (nrules-1); i >= 0; --i) {
		rule = this.cssRules[i];
		if(
			rule.type == CSSRule.STYLE_RULE
			&&
			this.isEqualSelector(rule.selectorText, selector)
		) {
				this.deleteRule(i);
				found = true;
		}
	}
	return found;
};


/**
 * CSSStyleSheet::hasSelector
 * Compares a possible list of selectors against an individual selector.
 * If the target selector matches any of the items in the list of selectors,
 * this function returns true.
 *
 * @param stext A string listing all selectors for a rule
 * @param selector The individual selector to find in the list of selectors
 * @return True if selector is one of the stext items
 */
CSSStyleSheet.prototype.hasSelector = function(stext, selector) {
	return (stext.split(',').map(String.trim).indexOf(selector) != -1);
};


/**
 * CSSStyleSheet::isEqualSelector
 * Compares two selectors to determine if they are functionally equal.  The
 * comparison parses lists of selectors and compares the results.  Thus,
 * selectors need not be identical to be functionally equal.
 *
 * @param stext Source list of all selectors for a rule
 * @param ttext Target list of all selectors for a rule
 * @return True if stext and ttext are functionally equal
 */
CSSStyleSheet.prototype.isEqualSelector = function(stext, ttext) {
	var left = stext.split(',').map(String.trim);
	var right = ttext.split(',').map(String.trim);
	if(left.length != right.length) { return false; }
	left.sort();
	right.sort();
	if(left.join(',') != right.join(',')) { return false; }
	return true;
};


/**
 * CSSStyleSheet::getRulesBySelector
 * Searches all rules in the style sheet, and builds a list of rules that
 * match the supplied selector.
 *
 * @param selector The selector to match against existing selectors
 * @param inclusive Whether or not to be inclusive of list matches
 * @return An array of CSSRule objects that match the selector
 */
CSSStyleSheet.prototype.getRulesBySelector = function(selector) {
	var inclusive = arguments[1] ? arguments[1] : false;
	var nrules = this.cssRules.length;
	var rule = null;
	var rules = [];
	for(var i = 0; i < nrules; ++i) {
		rule = this.cssRules[i];
		if(
			rule.type == CSSRule.STYLE_RULE
			&&
			(
				(!inclusive&&this.isEqualSelector(rule.selectorText,selector))
				||
				( inclusive && this.hasSelector(rule.selectorText, selector) )
			)
		) {
			rules.push(this.cssRules[i]);
		}
	}
	return rules;
};

