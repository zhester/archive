#|===========================================================================
	Zac Hester
	2007-02-02
	CSC 447: Spring 2007
	Programming Assignment #1: Family Tree

	Test Database Data
===========================================================================|#

; Initialize the database.
(defun init_db ()

	;-- Age 90 Generation I --------------------------------------------------

	(setf Adam
		(make-person
			:name 'Adam
			:age 90
			:sex 'male
    		:parents '()
			:children '(Suzy)
		)
	)

	(setf Eve
		(make-person
			:name 'Eve
			:age 90
			:sex 'female
    		:parents '()
			:children '(Suzy)
		)
	)

	(setf Oldy
		(make-person
			:name 'Oldy
			:age 70
			:sex 'male
    		:parents '()
			:children '(Tom)
		)
	)

	(setf Olderton
		(make-person
			:name 'Olderton
			:age 70
			:sex 'female
    		:parents '()
			:children '(Tom)
		)
	)

	;-- Age 70 Generation II -------------------------------------------------

	(setf Tom
		(make-person
			:name 'Tom
			:age 70
			:sex 'male
    		:parents '(Oldy Olderton)
			:children '(Mike Apostrophe Tormoline)
		)
	)

	(setf Suzy
		(make-person
			:name 'Suzy
			:age 70
			:sex 'female
    		:parents '(Adam Eve)
			:children '(Mike Apostrophe Tormoline)
		)
	)

	(setf John
		(make-person
			:name 'John
			:age 70
			:sex 'male
    		:parents '()
			:children '(Mary Torbald Misenkite)
		)
	)

	(setf Jane
		(make-person
			:name 'Jane
			:age 70
			:sex 'female
    		:parents '()
			:children '(Mary Torbald Misenkite)
		)
	)

	;-- Age 50 Generation III ------------------------------------------------

	(setf Mike
		(make-person
			:name 'Mike
			:age 50
			:sex 'male
    		:parents '(Tom Suzy)
			:children '(Melanie Michael Mitchel)
		)
	)

	(setf Apostrophe
		(make-person
			:name 'Apostrophe
			:age 50
			:sex 'male
    		:parents '(Tom Suzy)
			:children '()
		)
	)

	(setf Tormoline
		(make-person
			:name 'Tormoline
			:age 50
			:sex 'female
    		:parents '(Tom Suzy)
			:children '(Todd)
		)
	)

	(setf Mary
		(make-person
			:name 'Mary
			:age 50
			:sex 'female
    		:parents '(John Jane)
			:children '(Melanie Michael Mitchel)
		)
	)

	(setf Torbald
		(make-person
			:name 'Torbald
			:age 50
			:sex 'male
    		:parents '(John Jane)
			:children '(Missy)
		)
	)

	(setf Misenkite
		(make-person
			:name 'Misenkite
			:age 50
			:sex 'female
    		:parents '(John Jane)
			:children '()
		)
	)

	;-- Age 30 Generation IV -------------------------------------------------

	(setf Melanie
		(make-person
			:name 'Melanie
			:age 30
			:sex 'female
    		:parents '(Mike Mary)
			:children '(Timothy Tabitha)
		)
	)

	(setf Michael
		(make-person
			:name 'Michael
			:age 30
			:sex 'male
    		:parents '(Mike Mary)
			:children '()
		)
	)

	(setf Mitchel
		(make-person
			:name 'Mitchel
			:age 30
			:sex 'male
    		:parents '(Mike Mary)
			:children '()
		)
	)

	(setf Bob
		(make-person
			:name 'Bob
			:age 30
			:sex 'male
    		:parents '()
			:children '(Timothy Tabitha)
		)
	)

	(setf Todd
		(make-person
			:name 'Todd
			:age 30
			:sex 'male
    		:parents '(Tormoline)
			:children '()
		)
	)

	(setf Missy
		(make-person
			:name 'Missy
			:age 30
			:sex 'female
    		:parents '(Torbald)
			:children '()
		)
	)

	;-- Age 10 Generation V --------------------------------------------------

	(setf Timothy
		(make-person
			:name 'Timothy
			:age 10
			:sex 'male
    		:parents '(Bob Melanie)
			:children '()
		)
	)

	(setf Tabitha
		(make-person
			:name 'Tabitha
			:age 10
			:sex 'female
    		:parents '(Bob Melanie)
			:children '()
		)
	)

	(format t "DB Loaded~%")
	(values)
)

