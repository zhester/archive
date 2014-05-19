; Session initialization script for PA1

; Load main source file.
(defun load_source ()
	(load "FamilyTree.lsp")
)

; Load test database file.
(defun load_db ()
	(load "pa1db.lsp")
	(init_db)
)

; Load everything.
(defun load_all ()
	(load_source)
	(load_db)
	(values)
)

; Call to load everything.
(load_all)

; Define the full list of test cases.
(setf cases
	(list
		'parents 'mothers 'fathers
		'children 'sons 'daughters
		'siblings 'sisters 'brothers
		'grandparents 'grandfathers 'grandmothers
		'grandchildren 'grandsons 'granddaughters
		'uncles 'aunts
		'nieces 'nephews
		'cousins 'male-cousins 'female-cousins
		'ancestors 'male-ancestors 'female-ancestors
		'descendants 'male-descendants 'female-descendants
	)
)

; Perform a full test run.
(defun fulltest ()
	(dolist (case cases 'Done)
		(format t "~A of Mike: ~A~%" case (funcall case Mike))
		(format t "~A of Melanie: ~A~%" case (funcall case Melanie))
	)
)

; Silly function.
(defun zprint (message)
	(format t "~A~%" message)
	(values)
)
