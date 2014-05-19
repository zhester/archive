#|===========================================================================
	Zac Hester
	2007-02-02
	CSC 447: Spring 2007
	Programming Assignment #1: Family Tree
	CLisp interpreter used for development and testing.

	This script defines a set of query functions to access family members
	relative to other family members based on a simple node structure.

	The possible queries include:

		parents, mothers, fathers, children, sons, daughters, siblings,
		sisters, brothers, grandparents, grandfathers, grandmothers,
		grandchildren, grandsons, granddaughters, uncles, aunts, nieces,
		nephews, cousins, male-cousins, female-cousins, ancestors,
		male-ancestors, female-ancestors, descendants, male-descendants,
		female-descendants
===========================================================================|#


;============================================================================
;== Data Structures =========================================================


; Define our basic person (node) structure.
(defstruct person
	name
	age
	sex
	parents
	children
)


;============================================================================
;== Relationship Query Functions ============================================


; parents
; @param node The reference node for the query
; @return A list containing the parents of the person indicated by the node
(defun parents (node)

	; Standard struct accessor.
	(person-parents node)
)


; mothers
; @param node The reference node for the query
; @return A list containing the mothers of the person indicated by the node
(defun mothers (node)

	; Filtered list of parents.
	(drop_males (parents node))
)


; fathers
; @param node The reference node for the query
; @return A list containing the fathers of the person indicated by the node
(defun fathers (node)

	; Filtered list of parents.
	(drop_females (parents node))
)


; children
; @param node The reference node for the query
; @return A list containing the children of the person indicated by the node
(defun children (node)

	; Standard struct accessor.
	(person-children node)
)


; sons
; @param node The reference node for the query
; @return The sons of the person indicated by the reference node
(defun sons (node)

	; Filtered list of children.
	(drop_females (children node))
)


; daughters
; @param node The reference node for the query
; @return The daughters of the person indicated by the reference node
(defun daughters (node)

	; Filtered list of children.
	(drop_males (children node))
)


; siblings
; @param node The reference node for the query
; @return The siblings of the person indicated by the reference node
(defun siblings (node)

	; Remove the origin node from its list of siblings.
	(drop_elements

		; Retrieve the children of this node's parents.
		(get_generation 'children (parents node))

		; The field to match for filtering
		'person-name

		; The value of the field that should be filtered
		(person-name node)
	)
)


; sisters
; @param node The reference node for the query
; @return The sisters of the person indicated by the reference node
(defun sisters (node)

	; Filtered list of siblings.
	(drop_males (siblings node))
)


; brothers
; @param node The reference node for the query
; @return The brothers of the person indicated by the reference node
(defun brothers (node)

	; Filtered list of siblings.
	(drop_females (siblings node))
)


; grandparents
; @param node The reference node for the query
; @return The grandparents of the person indicated by the reference node
(defun grandparents (node)

	; Retrieve the parents of this node's parents.
	(get_generation 'parents (parents node))
)


; grandfathers
; @param node The reference node for the query
; @return The grandfathers of the person indicated by the reference node
(defun grandfathers (node)

	; Filtered list of grandparents.
	(drop_females (grandparents node))
)


; grandmothers
; @param node The reference node for the query
; @return The grandmothers of the person indicated by the reference node
(defun grandmothers (node)

	; Filtered list of grandparents.
	(drop_males (grandparents node))
)


; grandchildren
; @param node The reference node for the query
; @return The grandchildren of the person indicated by the reference node
(defun grandchildren (node)

	; Retrieve the children of this node's children.
	(get_generation 'children (children node))
)


; grandsons
; @param node The reference node for the query
; @return The grandsons of the person indicated by the reference node
(defun grandsons (node)

	; Filtered list of grandchildren.
	(drop_females (grandchildren node))
)


; granddaughters
; @param node The reference node for the query
; @return The granddaughters of the person indicated by the reference node
(defun granddaughters (node)

	; Filtered list of grandchildren.
	(drop_males (grandchildren node))
)


; uncles
; @param node The reference node for the query
; @return The uncles of the person indicated by the reference node
(defun uncles (node)

	; The final list is filtered for females.
	(drop_females

		; Retrieve all aunts and uncles.
		(get_auntsuncles node)
	)
)


; aunts
; @param node The reference node for the query
; @return The aunts of the person indicated by the reference node
(defun aunts (node)

	; The final list is filtered for males.
	(drop_males

		; Retrieve all aunts and uncles.
		(get_auntsuncles node)
	)
)


; nieces
; @param node The reference node for the query
; @return The nieces of the person indicated by the reference node
(defun nieces (node)

	; Filtered list of niblings.
	(drop_males

		; Retrieve all niblings.
		(get_niblings node)
	)
)


; nephews
; @param node The reference node for the query
; @return The nephews of the person indicated by the reference node
(defun nephews (node)

	; Filtered list of niblings.
	(drop_females

		; Retrieve all niblings.
		(get_niblings node)
	)
)


; cousins
; @param node The reference node for the query
; @return The cousins of the person indicated by the reference node
(defun cousins (node)

	; Local scoping
	(let
		(
			; A list to store cousins
			(newlist ())
		)

		; Iterate across the node's parents
		(dolist
			(
				cnode
				(parents node)
				(remove-duplicates newlist)
			)

			; My cousins are the nieces and nephews of my parents
			(setf newlist (append newlist (get_niecesnephews (eval cnode))))
		)
	)
)


; male-cousins
; @param node The reference node for the query
; @return The male-cousins of the person indicated by the reference node
(defun male-cousins (node)

	; Filtered list of cousins.
	(drop_females

		; Retrieve all cousins.
		(cousins node)
	)
)


; female-cousins
; @param node The reference node for the query
; @return The female-cousins of the person indicated by the reference node
(defun female-cousins (node)

	; Filtered list of cousins.
	(drop_males

		; Retrieve all cousins.
		(cousins node)
	)
)


; ancestors
; @param node The reference node for the query
; @return The ancestors of the person indicated by the reference node
(defun ancestors (node)

	; Local scoping
	(let
		(
			; A list to keep track of ancestors.
			(newlist ())
		)

		; Iterate over immediate parents.
		(dolist
			(
				cnode
				(parents node)
				(remove-duplicates newlist)
			)
			(setf
				newlist
				; Add our current generation plus the previous generation.
				(append newlist (list cnode) (ancestors (eval cnode)))
			)
		)
	)
)


; male-ancestors
; @param node The reference node for the query
; @return The male-ancestors of the person indicated by the reference node
(defun male-ancestors (node)

	; Filtered list of ancesotrs.
	(drop_females

		; All ancestors.
		(ancestors node)
	)
)


; female-ancestors
; @param node The reference node for the query
; @return The female-ancestors of the person indicated by the reference node
(defun female-ancestors (node)

	; Filtered list of ancestors.
	(drop_males

		; All ancestors.
		(ancestors node)
	)
)


; descendants
; @param node The reference node for the query
; @return The descendants of the person indicated by the reference node
(defun descendants (node)

	; Local scoping
	(let
		(
			; A list to keep track of ancestors.
			(newlist ())
		)

		; Iterate over immediate children.
		(dolist
			(
				cnode
				(children node)
				(remove-duplicates newlist)
			)
			(setf
				newlist
				; Add our current generation plus the next generation.
				(append newlist (list cnode) (descendants (eval cnode)))
			)
		)
	)
)

; male-descendants
; @param node The reference node for the query
; @return The male-descendants of the person indicated by the reference node
(defun male-descendants (node)

	; Filtered list of descendants.
	(drop_females

		; All descendants.
		(descendants node)
	)
)


; female-descendants
; @param node The reference node for the query
; @return The female-descendants of the person indicated by the reference node
(defun female-descendants (node)

	; Filtered list of descendants.
	(drop_males

		; All descendants.
		(descendants node)
	)
)

		
;============================================================================
;== Data Set Utility Functions ==============================================


; get_generation
; Builds a list of a generation based on a list.
;
; @param traversal A function that will traverse each element in the list
; @param nodelist The reference list of nodes
; @return A new list of people that was build on the traversal of the
;         source list
(defun get_generation (traversal nodelist)

	; Local scoping
	(let
		(
			; A place to build a new list
			(newlist ())
		)

		; Iterate across the source list.
		(dolist
			(
				; Current node
				cnode

				; Source list
				nodelist

				; The return value is filtered for duplicates.
				(remove-duplicates newlist)
			)

			; Add the derived list to the new list.
			(setf newlist (append newlist (funcall traversal (eval cnode))))
		)
	)
)


; drop_elements
; Removes structure elements from a list of structures that match a
; specified field value.
;
; @param original The original list
; @param key The criterion key (the symbol name of the accessor function)
; @param value The criterion value
; @return A new list of elements with the matched elements removed
(defun drop_elements (original key value)

	; Local scoping
	(let
		(
			; A new list to store our filtered list.
			(newlist ())
		)

		; Iterate over each structure in the list.
		(dolist (element original newlist)

			; Check to make sure this element does NOT match the criterion
			(unless (eq (funcall key (eval element)) value)

				; Non-matching elements are added to a buffer list.
				(setf newlist (append newlist (list element)))
			)
		)
	)
)


; drop_males
; Removes all females from a list of people.
;
; @param people A list of people to filter
; @return A new list of people without any males on the list
(defun drop_males (people)

	; Filter out all people who match their person-sex field with "male"
	(drop_elements people 'person-sex 'male)
)


; drop_females
; Removes all females from a list of people.
;
; @param people A list of people to filter
; @return A new list of people without any females on the list
(defun drop_females (people)

	; Filter out all people who match their person-sex field with "female"
	(drop_elements people 'person-sex 'female)
)


; get_auntsuncles
; Retrieves all the aunts and uncles of a particular node
;
; @param node The reference node
; @return A new list of aunts of uncles
(defun get_auntsuncles (node)

	; Local scoping
	(let
		(
			; A place to build a list of aunts and uncles
			(newlist ())
		)

		; Remove duplicates from final list.
		(remove-duplicates

			; Append immediate aunts and uncles with derived aunts and uncles.
			(append

				; Get derived aunts and uncles.
				(get_generation 'parents
			
					; Get the children of the siblings of the parents.
					(get_generation 'children
			
						; Loop through the node's parents
						(dolist
							(
								cnode
								(parents node)
								(remove-duplicates newlist)
							)
		
							; Build a list of the parents' immediate siblings
							(setf
								newlist
								(append newlist (siblings (eval cnode)))
							)
						)
					)
				)

				; List of immediate aunts and uncles.
				newlist
			)
		)
	)
)


; get_niblings
; Retrieves all the nieces and nephews of a particular node
;
; @param node The reference node
; @return A new list of nieces and nephews
(defun get_niblings (node)

	; Local scoping
	(let
		(
			; A list to store all the nieces/nephews
			(newlist ())
		)
		(dolist
			(
				cnode
				; A list of the node's genetic spouses (plus the node)
				(get_generation 'parents (children node))
				(remove-duplicates newlist)
			)

			; Build a list of nieces and nephews.
			(setf
				newlist
				(append
					newlist

					; The children of our siblings are our nieces/nephews.
					(get_generation 'children (siblings (eval cnode)))
				)
			)
		)
	)
)
