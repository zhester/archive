#|===========================================================================
	Zac Hester
	2007-03-21
	CSC 447: Spring 2007
	Programming Assignment #2: Missionaries and Cannibals
	CLisp interpreter used for development and testing.

	This script attempts to solve the Missionaries Cannibals problem for
	an arbitrary number of each group.

	Usage:
		(cross m c)
		Where m is the number of missionaries and c is the number of
		cannibals.

===========================================================================|#


; Define a structure to represent the current state.
(defstruct node
	parent	; Stores a symbol pointing to this state's parent state.
	m		; Number of missionaries on the left bank.
	c		; Number of cannibals on the left bank.
	b		; Number of boats on the left bank (0 or 1 only)
	arrived	; A string describing how we arrived at this state
)


; Establish two global variables to assist the HEF and successor function.
(defvar *M*)
(defvar *C*)


; cross
; The primary interface to the solution system.
;
; @param m Number of missionaries
; @param c Number of cannibals
(defun cross (m c)

	; Check for invalid parameters.
	(when (> c m) (return-from cross "No solution. (invalid input)"))

	; Set global variables to inform the HEF comparison function.
	(setf *M* m)
	(setf *C* c)

	; Primary loop.
	(do*

		; Loop variable scope.
		(
			; Initialize first state
			(cnode
				(make-node
					:parent nil
					:m m
					:c c
					:b 1
					:arrived "Start"
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
			(show_solution cnode)
		)

		; Check for empty open list (no solution)
		(when (null opened) (return-from cross "No solution. (exhausted)")) 

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

	(values)
) ; End of cross function


; hef_compare
; Compares two nodes to each other to see which one is closer to the goal
;
; @param n1 A subject node
; @param n2 A subject node
; @return t if n1 is closer than n2, nil otherwise
(defun hef_compare (n1 n2)
	(if
		; Are we not past the point of local symmetry?
;		(and
;			(not (eq (node-c n1) *C*))
;			(eq (node-m n1) (node-c n1))
;			(eq (node-m n1) (node-c n1))
;		)
		t ; stub evaluation of symmetry
		(< (hef n1) (hef n2))
		(> (hef n1) (hef n2))
	)
)


; hef
; Hueristic evaluation function
;
; @param n A node to evaluate
; @return A scaler value representing how close this node is to the goal
(defun hef (n)

	; Simple method: number of people on the left side
	(+ (node-m n) (node-c n))
)


; is_goal
; Tests to see if a node qualifies as a valid goal
;
; @param n A subject node
; @return t if the node is the final node, nil otherwise
(defun is_goal (n)

	; The goal is satisfied if everyone is on the right bank (0)
	(and
		(zerop (node-m n))
		(zerop (node-c n))
		(zerop (node-b n))
	)
)


; is_equal
; Check to see if two nodes have equivalent state
;
; @param n1 A subject node
; @param n2 A subject node
; @return t if the nodes are equal, nil otherwise
(defun is_equal (n1 n2)

	; The comparison only requires the M, C, and B values be equal
	(and
		(= (node-m n1) (node-m n2))
		(= (node-c n1) (node-c n2))
		(= (node-b n1) (node-b n2))
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
(defun show_solution (n)

	; Local scope.
	(let
		(
			; Grab the solution path as a list of nodes.
			(path (trace_solution n))
		)

		; Print solution heading.
		(format t
			"~%Solution for ~A Missionaries and ~A Cannibals~%~%" *M* *C*
		)
		(format t
			"Left Bank   Right Bank   Canoe   Move~%"
		)
		(format t
			"---------   ----------   -----   --------------------------~%"
		)

		; Print solution steps.
		(dolist (cnode path)
			(format t "M:~A, C:~A    M:~A, C:~A     ~A   ~A~%"
				(node-m cnode)
				(node-c cnode)
				(- *M* (node-m cnode))
				(- *C* (node-c cnode))
				(if (zerop (node-b cnode)) "Right" "Left ")
				(node-arrived cnode)
			)
		)

		; Print summary.
		(format t "~%Completed in ~A moves.~%" (- (length path) 1))
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

			; The boat's new side
			(boat (if (zerop (node-b n)) 1 0))
		)

		; Move 1C>
		(when (and (= (node-b n) 1) (> (node-c n) 0))
			(setf new_states
				(cons
					(make-node
						:parent n
						:m (node-m n)
						:c (- (node-c n) 1)
						:b boat
						:arrived "Moved 1 Cannibal Right"
					)
					new_states
				)
			)
		)

		; Move 2C>
		(when (and (= (node-b n) 1) (> (node-c n) 1))
			(setf new_states
				(cons
					(make-node
						:parent n
						:m (node-m n)
						:c (- (node-c n) 2)
						:b boat
						:arrived "Moved 2 Cannibals Right"
					)
					new_states
				)
			)
		)

		; Move 1C<
		(when (and (= (node-b n) 0) (< (node-c n) *C*))
			(setf new_states
				(cons
					(make-node
						:parent n
						:m (node-m n)
						:c (+ (node-c n) 1)
						:b boat
						:arrived "Moved 1 Cannibal Left"
					)
					new_states
				)
			)
		)

		; Move 2C<
		(when (and (= (node-b n) 0) (< (node-c n) (- *C* 1)))
			(setf new_states
				(cons
					(make-node
						:parent n
						:m (node-m n)
						:c (+ (node-c n) 2)
						:b boat
						:arrived "Moved 2 Cannibals Left"
					)
					new_states
				)
			)
		)

		; Move 1M>
		(when (and (= (node-b n) 1) (> (node-m n) 0))
			(setf new_states
				(cons
					(make-node
						:parent n
						:m (- (node-m n) 1)
						:c (node-c n)
						:b boat
						:arrived "Moved 1 Missionary Right"
					)
					new_states
				)
			)
		)

		; Move 2M>
		(when (and (= (node-b n) 1) (> (node-m n) 1))
			(setf new_states
				(cons
					(make-node
						:parent n
						:m (- (node-m n) 2)
						:c (node-c n)
						:b boat
						:arrived "Moved 2 Missionaries Right"
					)
					new_states
				)
			)
		)

		; Move 1M<
		(when (and (= (node-b n) 0) (< (node-m n) *M*))
			(setf new_states
				(cons
					(make-node
						:parent n
						:m (+ (node-m n) 1)
						:c (node-c n)
						:b boat
						:arrived "Moved 1 Missionary Left"
					)
					new_states
				)
			)
		)

		; Move 2M<
		(when (and (= (node-b n) 0) (< (node-m n) (- *M* 1)))
			(setf new_states
				(cons
					(make-node
						:parent n
						:m (+ (node-m n) 2)
						:c (node-c n)
						:b boat
						:arrived "Moved 2 Missionaries Left"
					)
					new_states
				)
			)
		)

		; Move 1M1C>
		(when (and (= (node-b n) 1) (> (node-m n) 0) (> (node-c n) 0))
			(setf new_states
				(cons
					(make-node
						:parent n
						:m (- (node-m n) 1)
						:c (- (node-c n) 1)
						:b boat
						:arrived "Moved 1 M. and 1 C. Right"
					)
					new_states
				)
			)
		)

		; Move 1M1C<
		(when (and (= (node-b n) 0) (< (node-m n) *M*) (< (node-c n) *C*))
			(setf new_states
				(cons
					(make-node
						:parent n
						:m (+ (node-m n) 1)
						:c (+ (node-c n) 1)
						:b boat
						:arrived "Moved 1 M. and 1 C. Left"
					)
					new_states
				)
			)
		)

		;(diagnode n) (format t "    Successors: ~A~%" (length new_states))

		; Return the list of new states (no duplicates or invalid states).
		(drop_invalids
			(remove-duplicates new_states :test #'is_equal)
		)
	)
)


; drop_invalids
; Remove all invalid states from a list of states
;
; @param states A list of states to scan for invalid states
; @return A new list of states without any invalid states
(defun drop_invalids (states)

	; Local scope.
	(let
		(
			; A buffer for a new list of states
			(newlist nil)

			; Some check values
			(mleft 0)
			(cleft 0)
			(mright 0)
			(cright 0)
		)

		; Scan the list
		(dolist (cnode states newlist)

			(setf mleft (node-m cnode))
			(setf cleft (node-c cnode))
			(setf mright (- *M* mleft))
			(setf cright (- *C* cleft))

			; Check for allowable states.
			(unless
				(or
					; Test for fewer missionaries on the left bank.
					(if
						(zerop mleft)
						nil
						(< mleft cleft)
					)
					; Test for fewer missionaries on the right bank.
					(if
						(zerop mright)
						nil
						(< mright cright)
					)
				)

				; Add this generated state to the list of successors.
				;(format t "Adding New State...~%") (diagnode cnode)
				(setf newlist (cons cnode newlist))
			)
		)
	)
)


; diagnode
; A simple testing utility function to trace state info.
;
; @param n The node to diagnose
(defun diagnode (n)
	(format t "  State: M:~A; C:~A; B:~A;~%"
		(node-m n)
		(node-c n)
		(node-b n)
	)
	(values)
)
