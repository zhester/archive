#|===========================================================================
	Zac Hester <zac.hester@gmail.com>
	2007-04-11
	CSC 447: Spring 2007
	Programming Assignment #3: 8-Tile Puzzle
	CLisp interpreter used for development and testing.

	Usage:
		(puzzle)

	The user function will prompt for input of the initial puzzle.  The
	puzzle is entered on 3 lines with each line representing three tile
	values each separated by a space.  Entering a zero (0) represents the
	blank space in the puzzle.

	Ex:
		Please enter the puzzle to solve using the numbers
			0 through 8, where 0 is the blank:

		1 3 4
		8 6 2
		7 0 5

		Finding solution...

	If the puzzle is found to be solvable, the program will begin searching
	for the best solution.  After finding a solution, the program will
	output a list of moves required to reach the goal state along with
	basic information about the difficulty in finding the solution.

	This program has proven to solve each puzzle except for the worst case
	in a reasonable amount of time (less than one minute).

	The Heuristic Evaluation Function calculates a simple estimated cost
	by adding up the number of tiles that are out of place.  The heuristic
	comparison is calculated as the lesser of two nodes' total cost:
		Given: f(n) = g(n) + h'(n)
		Best option: min( f(n1), f(n2) )

	Solubility of an initial puzzle is tested using the code given by
	Dr. Weiss (included directly in this file).
===========================================================================|#


; Define a structure to represent the current state.
(defstruct node
	parent	; Stores a symbol pointing to this state's parent state.
	state	; This puzzle configuration.
	g		; Number of branches from initial node (depth).
	hprime	; Estimated number of branches to the goal.
)

; The goal puzzle.
(defvar *GOAL* '(1 2 3 8 0 4 7 6 5))


; puzzle
; The user interface function.  This function acquires data from the user,
; checks that the information is valid, and initiates the search routine.
(defun puzzle ()
	(let
		(
			; Handles the initial puzzle list
			(subject nil)

			; Some test puzzles
			(easypuzzle '(1 3 4 8 6 2 7 0 5))
			(mediumpuzzle '(2 8 1 0 4 3 7 6 5))
			(hardpuzzle '(2 8 1 4 6 3 0 7 5))
			(worstpuzzle '(5 6 7 4 0 8 3 2 1))
		)

		; Prompt for user input.
		(format t "Please enter the puzzle to solve using the numbers~%")
		(format t "    0 through 8, where 0 is the blank:~%~%")

		; Get user input and store in list.
		(dotimes (index 9)
			(setf subject (append subject (list (read t))))
		)

		; Check for solvability.
		(unless (solvable subject)
			(return-from puzzle "The specified puzzle is not solvable.")
		)

		; Indicate that we're working.
		(format t "~%Finding solution...~%")

		; Start searching for a solution.
		(solve_puzzle subject)

		; Run garbage collection.
		(gc)

		; Suppress output.
		(values)
	)
)


; solve_puzzle
; The primary search routine.  This function handles the main search
; loop as well as displaying the solution path to the user when it has
; found the solution.
;
; @param user_puzzle The puzzle (list) that will be solved
(defun solve_puzzle (user_puzzle)

	; Primary loop.
	(do*

		; Loop variable scope.
		(
			; Initialize first state
			(cnode
				(make-node
					:parent nil
					:state user_puzzle
					:g 0
					:hprime (hef user_puzzle)
				)
			)

			; Initialize node lists
			(opened (list cnode))
			(closed nil)
		)

		; Termination condition.
		(
			; If this node is the goal...
			(is_goal cnode)

			; Display the solution path
			(show_solution
				cnode
				(+ (length opened) (length closed))
				(length closed)
			)
		)

		; Check for empty open list (no solution)
		(when (null opened)
			(return-from solve_puzzle "No solution. (exhausted)")
		) 

		; Start at top node of open list
		(setf cnode (first opened))

		; Update open list
		(setf opened (rest opened))

		; Add node to closed list
		(setf closed (cons cnode closed))

		;(format t "Searching Children Of...~%") (diagnode cnode)

		; Generate successors and update node lists
		(dolist
			(
				childnode
				(gen_successors cnode)
			)

			; Make sure this node isn't already on a list
			(when
				(and
					(not (state_exists childnode opened))
					(not (state_exists childnode closed))
				)

				; Add to open list
				(setf opened (cons childnode opened))
			)
		)

		; Sort the new open list based on the HEF
		(setf opened (sort opened #'hef_compare))
	)

	; Suppress return
	(values)
)


; hef
; Heuristic evaluation function
; Simple method: number of tiles that are out of position.
;
; @param subject A puzzle (state) to evaluate
; @return A scaler value representing how close this state is to the goal
(defun hef (subject)
	(let
		(
			(total 0)
		)
		(dotimes (cindex 9 total)
			(when (not (= (nth cindex subject) (nth cindex *GOAL*)))
				(setf total (+ total 1))
			)
		)
	)	
)


; hef_compare
; Compares two nodes to each other to see which one has the lowest cost.
;
; @param n1 A subject node
; @param n2 A subject node
; @return t if n1 is closer than n2, nil otherwise
(defun hef_compare (n1 n2)
	(if
		(<
			(+ (node-hprime n1) (node-g n1))
			(+ (node-hprime n2) (node-g n2))
		)
		t
		nil
	)
)


; is_goal
; Tests to see if a node qualifies as a valid goal
;
; @param n A subject node
; @return t if the node is the final node, nil otherwise
(defun is_goal (n)

	; Check against goal definition.
	(is_equal n (make-node :state *GOAL*))
)


; is_equal
; Check to see if two nodes have equivalent state
;
; @param n1 A subject node
; @param n2 A subject node
; @return t if the nodes are equal, nil otherwise
(defun is_equal (n1 n2)

	; Test all puzzle positions.
	(dotimes (cindex 9 t)
		(when
			(not
				(=
					(nth cindex (node-state n1))
					(nth cindex (node-state n2))
				)
			)
			(return-from is_equal nil)
		)
	)
)


; state_exists
; Checks if a given state exists within a list.
;
; @param n The needle node
; @param nlist The haystack list of nodes
; @return t if it exists in the list, nil otherwise
(defun state_exists (n nlist)

	; Scan the list of states
	(dolist (cnode nlist)

		; If we find the needle state, send back t
		(when (is_equal n cnode)
			(return t)
		)
	)
)


; show_solution
; Print out the final list of solution states and moves.
;
; @param n The goal node
(defun show_solution (n g e)

	; Local scope.
	(let
		(
			; Grab the solution path as a list of nodes.
			(path (trace_solution n))
		)

		; Print solution heading.
		(format t
			"~%Solution found:~%~%"
		)

		; Print solution steps.
		(dolist (cnode path)

			; Run through each tile in the puzzle.
			(dotimes (cindex 9)

				; Beginning of a line:
				(when (zerop (mod cindex 3))
					(format t "    ")
				)

				; Output the puzzle tile value.
				(if (zerop (nth cindex (node-state cnode)))
					(format t "  ")
					(format t "~A " (nth cindex (node-state cnode)))
				)

				; End of a line:
				(when (= (mod cindex 3) 2)
					(format t "~%")
				)
			)

			; End of a puzzle:
			(format t "~%")
		)

		; Print summary.
		(format t "Completed in ~A moves.  " (- (length path) 1))
		(format t "~A nodes generated, ~A nodes expanded.~%" g e)
	)
)


; trace_solution
; Trace up the solution path from a goal node
;
; @param n The goal node
; @return A list of nodes that traces from the origin to the goal
(defun trace_solution (n)
    (do*
        (
			(path (list n))
		)
        (
			(null (node-parent n))
			path
		)

		; step the node up to the parent
        (setf n (node-parent n))

        ; add it to the path
        (setf path (cons n path))
    )
)


; gen_successors
; Generate valid new states with no knowledge of history
;
; @param n The node from which to generate new states
; @return A list of new states based on the given state
(defun gen_successors (n)

	; Local scope.
	(let
		(
			; A list of new states
			(new_states nil)

			; Location of the blank
			(blank -1)

			; A place to hold a new puzzle
			(new_puzzle nil)
		)

		; Find the blank.
		(dotimes (tile_index 9)
			(when (= (nth tile_index (node-state n)) 0)
				(setf blank tile_index)
			)
		)

		; Not on the top row, up switch allowed.
		(when (not (< blank 3))
			(setf new_puzzle (move_tile (node-state n) blank -3))
			(setf new_states
				(cons
					(make-node
						:parent n
						:state new_puzzle
						:g (+ (node-g n) 1)
						:hprime (hef new_puzzle)
					)
					new_states
				)
			)
			;(format t "    Generating new state: ~%")
			;(diagnode (first new_states))
		)

		; Not on the left side, left switch allowed.
		(when (not (= (mod blank 3) 0))
			(setf new_puzzle (move_tile (node-state n) blank -1))
			(setf new_states
				(cons
					(make-node
						:parent n
						:state new_puzzle
						:g (+ (node-g n) 1)
						:hprime (hef new_puzzle)
					)
					new_states
				)
			)
			;(format t "    Generating new state: ~%")
			;(diagnode (first new_states))
		)

		; Not on the right side, right switch allowed.
		(when (not (= (mod blank 3) 2))
			(setf new_puzzle (move_tile (node-state n) blank 1))
			(setf new_states
				(cons
					(make-node
						:parent n
						:state new_puzzle
						:g (+ (node-g n) 1)
						:hprime (hef new_puzzle)
					)
					new_states
				)
			)
			;(format t "    Generating new state: ~%")
			;(diagnode (first new_states))
		)

		; Not on the bottom, down switch allowed.
		(when (not (> blank 5))
			(setf new_puzzle (move_tile (node-state n) blank 3))
			(setf new_states
				(cons
					(make-node
						:parent n
						:state new_puzzle
						:g (+ (node-g n) 1)
						:hprime (hef new_puzzle)
					)
					new_states
				)
			)
			;(format t "    Generating new state: ~%")
			;(diagnode (first new_states))
		)

		; Return the list of new states (no duplicate states).
		(remove-duplicates new_states :test #'is_equal)
	)
)


; move_tile
; Performs a single move operation on a puzzle.
;
; @param source_puzzle The puzzle (list) on which to operate
; @param start The starting tile's position (index)
; @param stetps The number of steps to move in the list (int)
; @return A new puzzle (list) with the tile moved as indicated
(defun move_tile (source_puzzle start steps)
	(let
		(
			(new_puzzle (copy-list source_puzzle))
			(start_tile (nth start source_puzzle))
			(swap_tile (nth (+ start steps) source_puzzle))
		)
		(setf (nth start new_puzzle) swap_tile)
		(setf (nth (+ start steps) new_puzzle) start_tile)
		new_puzzle
	)
)


; diagnode
; A simple testing utility function to trace state info.
;
; @param n The node to diagnose
(defun diagnode (n)
	(format t "  State: ~A; Depth: ~A; Est: ~A; Total: ~A~%"
		(node-state n)
		(node-g n)
		(node-hprime n)
		(+ (node-g n) (node-hprime n))
	)
	(values)
)





#|
                  ***** SOLVABLE.LSP *****
The SOLVABLE function returns T if a given 8-puzzle position is solvable,
NIL otherwise.
Usage:    (solvable L)
          where L is a 9-element list such as (1 2 3 8 0 4 7 6 5)
Reference:  "Mathematical Games and Pastimes", p.79-85,
             A.P.Domoryad, Macmillan, 1964.
Written 03/88 by John M. Weiss, Ph.D.
Modifications:
|#
(defvar *flag*)
(defun solvable (L)
    (setq *flag* nil)                               ; global *flag*
    (mapcar #'(lambda (elem) (disorder elem L)) L)
    (eq *flag* (evenp (position 0 L)))
)
(defun disorder (elem L)
    (cond
        ((eq (car L) elem))
        ((> (car L) elem)
            (setq *flag* (not *flag*))
            (disorder elem (cdr L))
        )
        (t (disorder elem (cdr L)))
    )
)
#|
Here are some legal puzzle configurations:
Goal:        Easy:        Medium:        Hard:        Worst:
1 2 3        1 3 4        2 8 1          2 8 1        5 6 7
8   4        8 6 2          4 3          4 6 3        4   8
7 6 5        7   5        7 6 5            7 5        3 2 1
|#
