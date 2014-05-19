<?php
/*===========================================================================
	Zac Hester
	2007-03-21

	This script attempts to solve the Missionaries Cannibals problem for
	an arbitrary number of each group.

	This is a translation from my Lisp version.
===========================================================================*/


// Define a structure to represent the current state.
//(defstruct node
//	p	// Stores a symbol pointing to this state's parent state.
//	m	// Number of missionaries on the left bank.
//	c	// Number of cannibals on the left bank.
//	b	// Number of boats on the left bank (0 or 1 only)
//	a	// A string describing how we arrived at this state
//)


// Establish two global variables to assist the HEF and successor function.
$M = 0;
$C = 0;


// cross
// The primary interface to the solution system.
//
// @param m Number of missionaries
// @param c Number of cannibals
function cross($m, $c) {
	global $M, $C;

	// Check for invalid parameters.
	if($c > $m) {
		echo "No solution. (invalid input)";
		return(false);
	}

	// Set global variables to inform the HEF comparison function.
	$M = $m;
	$C = $c;

	$cnode = array(
		'p' => false, 'm' => $m, 'c' => $c, 'b' => 1, 'a' => 'Start'
	);
	$opened = array($cnode);
	$closed = array();

	// Primary loop.
	while(!is_goal($cnode)) {

		// Check for empty open list (no solution)
		if(count($opened) == 0) {
			echo "No solution. (exhausted)";
			return(false);
		} 

		// Start at top node of open list
		$cnode = array_pop($opened);

		// Update open and close lists
		$closed[] = $cnode;

		// Generate successors and update node lists
		foreach(gen_successors($cnode) as $childnode) {

			// Make sure this node isn't already on a list
			if(!state_exists($childnode, $opened)
				&& !state_exists($childnode, $closed)) {

				// Add to open list
				$opened[] = $childnode;
			}
		}

		// Sort the new open list based on the HEF
		usort($opened, 'hef_compare');
	}

	// Display the solution path
	show_solution($cnode);
	return(true);
}


// hef_compare
// Compares two nodes to each other to see which one is closer to the goal
//
// @param n1 A subject node
// @param n2 A subject node
// @return t if n1 is closer than n2, nil otherwise
function hef_compare($n1, $n2) {

	return(hef($n1) < hef($n2));
}


// hef
// Hueristic evaluation function
//
// @param n A node to evaluate
// @return A scaler value representing how close this node is to the goal
function hef($n) {

	// Simple method: number of people on the left side
	return($n['m'] + $n['c']);
}


// is_goal
// Tests to see if a node qualifies as a valid goal
//
// @param n A subject node
// @return t if the node is the final node, nil otherwise
function is_goal($n) {

	// The goal is satisfied if everyone is on the right bank (0)
	return($n['m'] == 0 && $n['c'] == 0 && $n['b'] == 0);
}


// is_equal
// Check to see if two nodes have equivalent state
//
// @param n1 A subject node
// @param n2 A subject node
// @return t if the nodes are equal, nil otherwise
function is_equal($n1, $n2) {

	// The comparison only requires the M, C, and B values be equal
	return(
		$n1['m'] == $n2['m']
		&& $n1['c'] == $n2['c']
		&& $n1['b'] == $n2['b']
	);
}


// state_exists
// Checks if a given state exists within a list.
//
// @param n The needle node
// @param nlist The haystack list of nodes
// @return t if it exists in the list, nil otherwise
function state_exists($n, $nlist) {

	// Scan the list of states
	foreach($nlist as $cnode) {

		// If we find the needle state, send back t
		if(is_equal($n, $cnode)) {
			return(true);
		}
	}
	return(false);
}


// show_solution
// Print out the final list of solution states and moves.
//
// @param n The goal node
function show_solution ($n) {
	global $M, $C;

	$path = trace_solution($n);

	// Print solution heading.
	echo "\nSolution for $M Missionaries and $C Cannibals\n\n";
	echo "Left Bank   Right Bank   Canoe   Move\n";
	echo "---------   ----------   -----   --------------------------\n";

	// Print solution steps.
	foreach($path as $cnode) {
		printf(
			"M:%s, C:%s    M:%s, C:%s     %s   %s\n",
			$cnode['m'], $cnode['c'],
			($M-$cnode['m']), ($C-$cnode['c']),
			($cnode['b']?'Left ':'Right'),
			$cnode['a']
		);
	}

	// Print summary.
	echo "\nCompleted in ".(count($path)-1)." moves.\n";
}


// trace_solution
// Trace up the solution path from a goal node
//
// @param n The goal node
// @return A list of nodes that traces from the origin to the goal
function trace_solution($n) {

	$path = array($n);

	while($n['p'] != false) {

		// step the node up to the parent
		$n = $n['p'];

        // add it to the path
        $path[] = $n;
    }
    return(array_reverse($path));
}


// gen_successors
// Generate valid new states with no knowledge of history
//
// @param n The node from which to generate new states
// @return A list of new states based on the given state
function gen_successors($n) {

	global $M, $C;
	$new_states = array();
	$boat = $n['b'] ? 0 : 1;

		// Move 1C>
		if($n['b'] == 1 && $n['c'] > 0) {
			$new_states[] =
				array(
					'p' => $n,
					'm' => $n['m'],
					'c' => $n['c']-1,
					'b' => $boat,
					'a' => "Moved 1 Cannibal Right"
				);
		}

		// Move 2C>
		if($n['b'] == 1 && $n['c'] > 1) {
			$new_states[] =
				array(
					'p' => $n,
					'm' => $n['m'],
					'c' => $n['c']-2,
					'b' => $boat,
					'a' => "Moved 2 Cannibals Right"
				);
		}

		// Move 1C<
		if($n['b'] == 0 && $n['c'] < $C) {
			$new_states[] =
				array(
					'p' => $n,
					'm' => $n['m'],
					'c' => $n['c']+1,
					'b' => $boat,
					'a' => "Moved 1 Cannibal Left"
				);
		}

		// Move 2C<
		if($n['b'] == 0 && $n['c'] < ($C-1)) {
			$new_states[] =
				array(
					'p' => $n,
					'm' => $n['m'],
					'c' => $n['c']+2,
					'b' => $boat,
					'a' => "Moved 2 Cannibals Left"
				);
		}

		// Move 1M>
		if($n['b'] == 1 && $n['m'] > 0) {
			$new_states[] =
				array(
					'p' => $n,
					'm' => $n['m']-1,
					'c' => $n['c'],
					'b' => $boat,
					'a' => "Moved 1 Missionary Right"
				);
		}

		// Move 2M>
		if($n['b'] == 1 && $n['m'] > 1) {
			$new_states[] =
				array(
					'p' => $n,
					'm' => $n['m']-2,
					'c' => $n['c'],
					'b' => $boat,
					'a' => "Moved 2 Missionaries Right"
				);
		}

		// Move 1M<
		if($n['b'] == 0 && $n['m'] < $M) {
			$new_states[] =
				array(
					'p' => $n,
					'm' => $n['m']+1,
					'c' => $n['c'],
					'b' => $boat,
					'a' => "Moved 1 Missionary Left"
				);
		}

		// Move 2M<
		if($n['b'] == 0 && $n['m'] < ($M-1)) {
			$new_states[] =
				array(
					'p' => $n,
					'm' => $n['m']+2,
					'c' => $n['c'],
					'b' => $boat,
					'a' => "Moved 2 Missionaries Left"
				);
		}

		// Move 1M1C>
		if($n['b'] == 1 && $n['m'] > 0 && $n['c'] > 0) {
			$new_states[] =
				array(
					'p' => $n,
					'm' => $n['m']-1,
					'c' => $n['c']-1,
					'b' => $boat,
					'a' => "Moved 1 M. and 1 C. Right"
				);
		}

		// Move 1M1C<
		if($n['b'] == 0 && $n['m'] < $M && $n['c'] < $C) {
			$new_states[] =
				array(
					'p' => $n,
					'm' => $n['m']+1,
					'c' => $n['c']+1,
					'b' => $boat,
					'a' => "Moved 1 M. and 1 C. Left"
				);
		}


	// Return the list of new states (no duplicates or invalid states).
	return(drop_invalids(remove_duplicates($new_states)));
	//return($new_states);
}


function remove_duplicates($nlist) {
	$newlist = array();
	foreach($nlist as $n) {
		if(!state_exists($n, $newlist)) {
			$newlist[] = $n;
		}
	}
	return($newlist);
}

// drop_invalids
// Remove all invalid states from a list of states
//
// @param states A list of states to scan for invalid states
// @return A new list of states without any invalid states
function drop_invalids($states) {

	global $M, $C;
	$newlist = array();

	foreach($states as $cnode) {

		$mleft = $cnode['m'];
		$cleft = $cnode['c'];
		$mright = $M - $mleft;
		$cright = $C - $cleft;

		// Check for allowable states.
		if(($mleft == 0) || ($mright == 0)) {
			$newlist[] = $cnode;
		}
		else if(($mleft >= $cleft) && ($mright >= $cright)) {
			$newlist[] = $cnode;
		}
	}
	
	return($newlist);
}

function stringize($n) {
	return(sprintf('m:%s, c:%s, b:%s',$n['m'],$n['c'],$n['b']));
}

?>