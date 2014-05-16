<?php
/****************************************************************************
	PHP Source Style Blank
	Zac Hester <zac.hester@gmail.com>
	2009-06-23

	=====================================================================
	Copyright 2009 Zac Hester.  All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that this entire copyright
	notice is duplicated in all such copies.

	This software is provided "as is" and without any expressed or
	implied warranties, including, without limitation, the implied
	warranties of merchantibility and fitness for any particular purpose.
	=====================================================================

	This is an ongoing PHP source file intended to provide a generic
	style template for creating new source files.  As time permits, I
	will attempt to be as exaustive as possible.

	The section you are reading now is the general description of what's
	in this file and how it works.  Use case examples may be listed here
	such as:

		$myobject = new MyClass('argument');
		$myobject->hello();

	Lookup tables needed for external operation, definitions used for
	internal calculations, and other bits of information with varying
	degrees of usefullness can be entered here.

	= Phyiscal Formatting =
	My personal bias and choice of editors in many different
	environments have shaped my physical formatting style guides.
	However, as I traverse many different systems, these should be
	useful for a majority of coding environments and organizations.

	== Vertical Spacing ==
	Vertical spacing separates logical portions of the code.  Good
	coding practices should group logically-associated code within
	functions, methods, classes, and local code blocks.  These
	sections should be vertically separated by no fewer than two
	empty lines (between closing braces above and comment block
	below).

	Within these groups, single empty lines must be used to further
	separate functionally-associated lines of code.  Examples of
	functionally-associated groupings include variable initialization,
	simple (single-level) conditionals/loops, case items inside a
	switch, multiple lines of I/O processing, stepwise computations,
	and more.

	== Line Widths ==
	Every source file can have non-control characters to the 78th
	column (more legible in both GUI-based and terminal-based editors).
	Some allowance and accomodation for varying tab stop widths is
	expected, but not religiously followed.  My editors are all set for
	four columns per tab.  Nearly every code editor these days can
	adjust either its regular tab stop display, or automatically modify
	the code's physcial format to wrap at your desired width.  If all
	else fails:  preg_replace('/\t/', '    ', $code)

	== Indentation ==
	Tabs are intended to visually distinguish block-level arrangement.
	Whenever you invoke a new code block ({ ... }), it's probably a
	good idea to indent the contents.  While Python has excellent
	intentions in this regard, this practice is flexible where
	appropriate.

	=== Generic Blocks ===
	if(condition) {
		//code indented from block entry condition
	}
	while(1) {
		//code indented from block loop definition
	}

	=== Continued Lines ===
	my_long_function_with_many_parameters(parameter_one,
		parameter_two, parameter_three);

	=== Exceedingly Long/Complex Parameter Lists ===
	my_function(
		parameter_one,
		parameter_two,
		parameter_three,
		parameter_four
	);

	=== Short Blocks (this is fine) ===
	if($failure) { return(false); }
	//Yes, the braces are not required in this case.

	= PHP Language Guidelines =
	Web programmers tend to like XHTML because it's more formal and
	explicit than its HTML counterpart.  This style guide extends the
	quality of explicit practices for the sake of uniformity both
	internally and with other, more traditional languages.

	== Parameter Lists and Conditions ==
	Algol-derived languages inherit the practice of specifying lists
	inside parenthesis.  PHP (and other scripting languages) tend to
	relax this requirement and allow programmers to rely on context
	to delineate a list.  However, this guide recommends explicit
	list delineation:

		Bad: require 'somefile.php';
		Good: require('somefile.php');

	An exception to this rule is the PHP core language constructs
	such as "echo" and "return."  These are not functions.  However,
	they may optionally take their arguments within parenthesis.  This
	guide leaves it up to the programmer to choose to enclose arguments
	to constructs inside parenthesis.  Unfortunately, in some cases,
	using parenthesis with constructs decreases performance such as:

		Slower: return(true);
		Faster: return true;

	If you're curious, my code uses no parenthesis for "echo," but does
	use parenthesis for all other language constructs.

	Parameters should nearly always be spaced for legibility:

		Bad: preg_replace('/Foo/','Bar',$subject);
		Good: preg_replace('/Foo/', 'Bar', $subject);

	The only exception is when a function call or conditional pushes
	to the 79th or 80th column, and condensing the list may be the
	more legible option compared to wrapping the list to a second line.

	== Parameter Lists and Conditions with Blocks ==
	Formatting lists and conditions that demark blocks can spark
	religious wars lasting generations.  This guide is relaxed in a
	few cases.

	The following block may start on the same line or on the next
	immediate line (but no other configuration is allowed):

		=== Good (Preferred) ===
		if(condition) {
			//code
		}

		=== Good ===
		if(condition)
		{
			//code
		}

	In either case, the opening brace must have some white space after
	the demarking list/condition.

		=== Bad ===
		if(condition){
			//code
		}

****************************************************************************/


/**
 * my_function
 * Function comment description.
 *
 * @author Zac Hester <zac.hester@gmail.com>
 * @date 2009-06-23
 * @serial 2009062300
 *
 * @param p0 A type variable that specifies something
 * @param p1 A type variable that specifies something
 * @param p2 A type variable that specifies something
 * @return A type variable containing the result
 */
function my_function($p0, $p1, $p2) {
	return($p0 * $p1 % $p2);
}


/**
 * MyClass
 * Library-grade classes go in their own file named after them and are
 * documented in the file's overall comment header.  Quick/temporary
 * classes may be defined elsewhere.  In which case, they would need
 * their own comment block.
 *
 */
class MyClass {


	/**
	 * propertyName
	 * Public property description.
	 *
	 * @type Expected language type (string, int, mixed, etc)
	 */
	public var $size;


	/**
	 * methodName
	 * Public method description.
	 *
	 * @param p0 A type variable
	 * @return The result
	 */
	public function __construct() {
	}

}

?>