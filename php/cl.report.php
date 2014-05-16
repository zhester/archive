<?php
/****************************************************************************
	Database Report Class
	Zac Hester - 2006-12-22

	An extensible, object-oriented approach to delivering database
	reports to a web page.

	Goals:
		Simple instantiation/configuration
		Does NOT abstract vital elements of the report such as the query
		Allows extensions to modify portions of the report for highly
			customized cases
		Excellent performance by not storing up large result sets in
			arrays, but sending straight to the output buffer as soon as
			a field is processed against the record data

	Example of Simplest Usage:
		$r = new report(
			'select id,name from categories',
			REPORT_QUERY_COLUMNS
		);
		$r->print_table();

	Other/Advanced/Complete Examples:
		http://code.zacharyhester.com/?type=flat&lang=php&name=report

	To Do:
		- abstract additional attributes for each element
			- tooltips for all!
		- add to code site

	Developer Notes:
		All DBMS calls are in:
			Report::print_table
			Report::set_query_columns
****************************************************************************/


//A token to force the special-case creation of database-based columns
define('REPORT_QUERY_COLUMNS', 1);


/**
 * Report
 *
 * Base report class.
 */
class Report {

	/**
	 * Public properties to customize/refine behavior.
	 */
	var $objectNoun;
	var $objectNounpl;

	/**
	 * Set to allow default viewing without an explicit GET query.
	 */
	var $defaultSort;
	var $defaultPage;
	var $defaultShow;

	/**
	 * Private data.
	 */
	var $query;
	var $columns;
	var $baseUri;
	var $baseQuery;

	/**
	 * Report
	 *
	 * Report object constructor.
	 *
	 * @param query The SQL query to use (without 'order by' or 'limit')
	 * @param basic_columns Convenience list of basic data columns
	 */
	function Report($query, $basic_columns = array()) {

		//Basic report setup/initialization.
		$this->setQuery($query);
		$this->columns = array();

		//Find current URI information.
		$uri_bits = explode('?', $_SERVER['REQUEST_URI'], 2);
		$this->baseUri = $uri_bits[0];
		$this->baseQuery = '';

		//Read GET query info to maintain state.
		$get = $_GET;
		unset($get['sort']);
		unset($get['page']);
		unset($get['show']);
		$pairs = array();
		foreach($get as $k => $v) {
			$pairs[] = urlencode($k).'='.urlencode($v);
		}
		$this->baseQuery = implode('&amp;', $pairs);

		//Check for any basic columns to start the report.
		if(is_array($basic_columns) && count($basic_columns)) {

			//Use a simple hash map for basic column creation.
			foreach($basic_columns as $k => $v) {
				$col = $this->createColumn($k, $v);
				$col->sorting = $v;
				$this->addColumn($col);
			}
		}

		//Check for special case for super-easy use.
		//  Note: Don't reset the query later if using this short cut.
		else if($basic_columns === REPORT_QUERY_COLUMNS) {
			$this->setQueryColumns();
		}
	}

	/**
	 * setQuery
	 *
	 * Sets the current SQL database query.
	 *
	 * @param query The SQL query to use
	 */
	function setQuery($query) {

		//Set base query.
		$this->query = $query;
	}

	/**
	 * createColumn
	 *
	 * @param label This column's displayed label
	 * @param basic_key A convenience key for simple data output
	 */
	function createColumn($label, $basic_key = '') {
		return(new ReportColumn($label, $basic_key));
	}

	/**
	 * addColumn
	 *
	 * Adds a column object as returned by the create_column() method.
	 *
	 * @param column A column object to add to the report
	 */
	function addColumn($column) {
		$this->columns[] = $column;
	}

	/**
	 * printTable
	 *
	 * Prints the report in an HTML table.
	 */
	function printTable() {

		//Build and run the query.
		$q = $this->getQuery();
		$res = mysql_query($q);

		//Begin report output.
		echo "\n<div class=\"report\">\n";

		//Verify query results.
		if($res) {
			$num_records = mysql_num_rows($res);
			$num_columns = count($this->columns);
			if($num_records) {
				echo "\t<table class=\"data\">\n\t\t<tr class=\"header\">\n";
				foreach($this->columns as $i => $column) {
					echo "\t\t\t<th class=\"col$i\">";
					if($column->sorting) {
						if($this->isPaging()) {
							list($page, $show) = $this->getPageShow();
							$paging = '&amp;page='.$page.'&amp;show='.$show;
						}
						else {
							$paging = '';
						}
						echo '<a href="'.$this->baseUri.'?'
							.($this->baseQuery?$this->baseQuery.'&amp;':'')
							.'sort='.urlencode($column->sorting).$paging.'">';
					}
					echo report::gmc($column->label);
					if($column->sorting) {
						echo '</a>';
					}
					echo "</th>\n";
				}
				echo "\t\t</tr>\n";
				$row = 0;
				while($record = mysql_fetch_assoc($res)) {
					echo "\t\t<tr class=\"".($row%2?'odd':'even')."\">\n";
					foreach($this->columns as $i => $column) {
						echo "\t\t\t<td class=\"col$i\">";
						$column->printValue($record);
						echo "</td>\n";
					}
					echo "\t\t</tr>\n";
					++$row;
				}
				if($this->objectNounpl) {
					echo "\t\t<tr class=\"footer\">\n\t\t\t<td colspan=\""
						.$num_columns.'">Displaying '.$num_records
						.' '.$this->objectNounpl.".</td>\n\t\t</tr>\n";
				}
				echo "\t</table>\n";
			}
			else {
				echo '<p class="warning">There are no '
					.($this->objectNounpl?$this->objectNounpl:'records')
					." currently available.</p>\n";
			}
		}

		//The query failed.
		else {
			echo '<p class="error">'
				.'There was an error querying the database.</p>'
				."\n<!--\n\n".mysql_error()."\n\n$q\n\n-->\n";
		}

		//Finish report output.
		echo "</div>\n";
	}

	/**
	 * printPager
	 *
	 * Prints a "pager" interface for paged reports.
	 *
	 * @param total The total number of records in this report.
	 */
	function printPager($total, $separator = '&nbsp; ') {
		if($this->isPaging()) {
			list($page, $show) = $this->getPageShow();
			if($_GET['sort']) {
				$sorting = '&amp;sort='.urlencode($_GET['sort']);
			}
			else if($this->defaultSort) {
				$sorting = '&amp;sort='.urlencode($this->defaultSort);
			}
			else {
				$sorting = '';
			}
			$links = array();
			$pages = ceil($total / $show);
			for($i = 0; $i < $pages; ++$i) {
				if($i != $page) {
					$links[] = '<a href="'.$this->baseUri.'?'
						.($this->baseQuery?$this->baseQuery.'&amp;':'')
						.'page='.$i.'&amp;show='.$show.$sorting
						.'">'.($i+1).'</a>';
				}
				else {
					$links[] = ($i+1);
				}
			}
			echo implode($separator, $links);
		}
		else {
			echo '<!-- paging not detected -->';
		}
	}

	/*----------------------------------------------------------------------*/
	/*-- Static Methods ----------------------------------------------------*/

	/**
	 * gmc (Get Markup CDATA) -- Static Public
	 *
	 * Return a markup CDATA-valid string.
	 *
	 * @param str Input string
	 * @return Output string 
	 */
	function gmc($str) {
		return(htmlspecialchars($str));
	}

	/**
	 * gme (Get Markup Entity) -- Static Public
	 *
	 * Return a markup entity-valid string.
	 *
	 * @param str Input string
	 * @return Output string
	 */
	function gme($str) {
		return(htmlentities($str));
	}

	/*----------------------------------------------------------------------*/
	/*-- Private Methods ---------------------------------------------------*/

	/**
	 * getQuery -- Private
	 *
	 * Controls how the query to the database is modified for final querying.
	 *
	 * @return Full SQL query string
	 */
	function getQuery() {
		$q = $this->query;

		$sort = $_GET['sort'] ? $_GET['sort'] :
			$this->defaultSort ? $this->defaultSort : '';
		if($sort) {
			$q .= ' order by '.$sort;
		}

		if($this->isPaging()) {
			list($page, $show) = $this->getPageShow();
			$q .= ' limit '.($page*$show).','.$show;
		}
		else if($this->defaultShow) {
			$q .= ' limit '.$this->defaultShow;
		}

		return($q);
	}


	/**
	 * setQueryColumns -- Private
	 *
	 * Sets up some very simple report columns based on the columns
	 * returned from the database query itself.
	 */
	function setQueryColumns() {

		//We'll run the query only to retrieve the columns.
		$res = mysql_query($this->query.' limit 1');
		if($res) {

			//Map database columns directly into report columns.
			$num_fields = mysql_num_fields($res);
			for($i = 0; $i < $num_fields; ++$i) {
				$field = mysql_field_name($res, $i);
				$col = $this->createColumn($field, $field);
				$col->sorting = $field;
				$this->addColumn($col);
			}
		}
	}

	function isPaging() {
		if($_GET['show']) {
			$show = $_GET['show'];
		}
		else if($this->defaultShow) {
			$show = $this->defaultShow;
		}
		else {
			$show = false;
		}
		return($show);
	}

	function getPageShow() {
		if($_GET['page']) {
			$page =  $_GET['page'];
		}
		else if($this->defaultPage) {
			$page = $this->defaultPage;
		}
		else {
			$page = 0;
		}
		if($_GET['show']) {
			$show = $_GET['show'];
		}
		else if($this->defaultShow) {
			$show = $this->defaultShow;
		}
		else {
			$show = 100;
		}
		return(array($page, $show));
	}
}


/**
 * ReportColumn
 *
 * An object that controls an individual column in a report table.
 */
class ReportColumn {

	//Stores/sets the column's displayed label
	var $label;

	//A string that fits between multi-element fields.
	var $glue;

	//Strings that wrap up multi-element fields;
	var $prefix;
	var $suffix;

	//Set to the order by query value to allow people to sort on this column.
	var $sorting;

	//A list of all the elements in this column.
	var $elements;

	/**
	 * ReportColumn
	 *
	 * Class constructor.
	 *
	 * @param label The column's displayed label
	 * @param basic_key A convenience key for basic data output
	 */
	function ReportColumn($label, $basic_key = '') {
		$this->label = $label;
		$this->elements = array();
		if($basic_key) {
			$elem = $this->createElement();
			$elem->setAttribute('key', $basic_key);
			$this->addElement($elem);
		}
		$this->glue = ' | ';
		$this->prefix = '';
		$this->suffix = '';
		$this->sorting = '';
	}

	/**
	 * createElement
	 *
	 * Creates a new field element for this column.  However, the new
	 * element is not attached or associated with the column until you
	 * use add_element().
	 *
	 * @param element_type The type of element to create
	 * @return A new element object
	 */
	function createElement($element_type = 'data') {
		$class_name = 'ReportElement_'.$element_type;
		if(class_exists($class_name)) {
			return(new $class_name());
		}
		return(false);
	}

	/**
	 * addElement
	 *
	 * Adds a created element to this column.
	 * Custom field elements can also be passed to this method if you
	 * follow the documentation provided with the report_element class.
	 *
	 * @param element A field element object as returned by create_element()
	 */
	function addElement($element) {
		$this->elements[] = $element;
	}

	/**
	 * printValue
	 *
	 * Controls how the output for the column is printed.
	 *
	 * @param record A reference to an associative array containing the
	 *     current record's data	 	 
	 */
	function printValue(&$record) {
		$parts = array();
		$temp = '';
		foreach($this->elements as $i => $element) {
			$temp = $element->getValue($record);
			if($temp) {
				$parts[] = $temp;
			}
		}
		if(count($parts) > 1) {
			echo $this->prefix.implode($this->glue, $parts).$this->suffix;
		}
		else if(count($parts)) {
			echo $parts[0];
		}
		else {
			echo '&nbsp;';
		}
	}
}


/**
 * ReportElement
 *
 * Primitive column element with utility methods.
 * Consider this class VIRTUAL.
 */
class ReportElement {

	//Attributes of this element.
	var $attributes;

	//Registry of attributes that are required by this element.
	var $registry;

	//The usual way a table keys the primary ID.
	var $id_key;

	/**
	 * ReportElement
	 *
	 * Base class constructor.
	 * This must be manually called in any inheriting classes.
	 *
	 * @param attr A convenience list of field attributes
	 * @param reg A convenience list of all required field attributes
	 */
	function ReportElement($attr = array(), $reg = array()) {
		$this->attributes = $attr;
		$this->registry = $reg;
		$this->id_key = 'id';
	}

	/**
	 * getValue
	 *
	 * Virtual method.
	 * All inheriting classes must define this function as a vital part
	 * of their interface.
	 *
	 * @param rec A reference to an associative array containing the
	 *     current record's data
	 * @return A string formatted according to this field's type
	 */
	function getValue(&$record) { return(false); }

	/**
	 * set_attribute
	 *
	 * A somewhat OOPish way to set arbitrary attributes for this element.
	 *
	 * @param key The key of the attribute to set
	 * @param value The value to the attribute will hold
	 */
	function setAttribute($key, $value) {
		$this->attributes[$key] = $value;
	}

	/**
	 * get_attribute
	 *
	 * A somewhat OOPish way to get arbitrary attributes for this element.
	 *
	 * @param key The key of the attribute to retrieve
	 * @return The current value of the attribute
	 */
	function getAttribute($key) {
		return($this->attributes[$key]);
	}

	/**
	 * registerAttribute
	 *
	 * Add a field key to the list of required attributes.
	 *
	 * @param name The name of the attribute to register
	 */
	function registerAttribute($name) {
		if(!in_array($name, $this->registry)) {
			$this->registry[] = $name;
		}
	}

	/**
	 * check_attributes
	 *
	 * Scans the user-supplied attribute list to make sure it's all there.
	 *
	 * @return True on success
	 */
	function checkAttributes() {
		foreach($this->registry as $attribute) {
			if(!isset($this->attributes[$attribute])) {
				return(false);
			}
		}
		return(true);
	}
}


/**
 * ReportElement_data
 *
 * Custom object used to control the display of simple data fields.
 *
 * @extends ReportElement
 */
class ReportElement_data extends ReportElement {

	/**
	 * ReportElement_data
	 *
	 * Constructor.  Calls base constructor.
	 */
	function ReportElement_data() {
		$this->ReportElement();
		$this->registerAttribute('key');
	}

	/**
	 * getValue
	 *
	 * Required method to return the resulting display of this field.
	 *
	 * @param rec A reference to an associative array containing the
	 *    current record's data
	 * @return A string formatted according to this field's type
	 */
	function getValue(&$record) {
		if($this->checkAttributes()) {
			return(Report::gmc($record[$this->getAttribute('key')]));
		}
		return('&#63;');
	}
}


/**
 * ReportElement_link
 *
 * Custom object used to control the display of basic links.
 *
 * @extends ReportElement
 */
class ReportElement_link extends ReportElement_data {

	/**
	 * ReportElement_link
	 *
	 * Constructor.  Calls base constructor.
	 */
	function ReportElement_link() {
		$this->ReportElement_data();
		$this->registerAttribute('uri_fragment');
	}

	/**
	 * getValue
	 *
	 * Required method to return the resulting display of this field.
	 *
	 * @param rec A reference to an associative array containing the
	 *    current record's data
	 * @return A string formatted according to this field's type
	 */
	function getValue(&$record) {
		if($this->checkAttributes()) {
			return(
				'<a href="'.$this->getAttribute('uri_fragment')
				.Report::gme($record[$this->id_key]).'">'
				.Report::gmc($record[$this->getAttribute('key')])
				.'</a>'
			);
		}
		return('&#63;');
	}
}


/**
 * ReportElement_procedural
 *
 * A report element that allows procedural manipulation without
 * creating an entire class for the element.
 *
 * @extends ReportElement
 */
class ReportElement_procedural extends ReportElement {

	/**
	 * ReportElement_procedural
	 *
	 * Constructor.  Calls base constructor.
	 */
	function ReportElement_procedural() {
		$this->ReportElement();
		$this->registerAttribute('callback');
	}

	/**
	 * getValue
	 *
	 * Required method to return the resulting display of this field.
	 *
	 * @param rec A reference to an associative array containing the
	 *    current record's data
	 * @return A string formatted according to this field's type
	 */
	function getValue(&$record) {
		$callback = $this->getAttribute('callback');
		if($this->checkAttributes() && function_exists($callback)) {
			return($callback($record));
		}
		return('&#63;');
	}
}


/**
 * Empty framework for a new element class.
 */
/*
class ReportElement_### extends ReportElement {
	function ReportElement_###() {
		$this->ReportElement();
		$this->registerAttribute('???');
	}
	function getValue(&$record) {
		if($this->checkAttributes()) {
			return(
				$record[$this->getAttribute('???')]
			);
		}
		return('&#63;');
	}
}
*/


/*-------------------------------------------------------------------------*/
/*-- Application-Standard Elements ----------------------------------------*/

//Edit links.
class ReportElement_editlink extends ReportElement_link {
	function ReportElement_editlink() {
		$this->ReportElement_link();
		$this->setAttribute('key','nodata_editlink');
	}
	function getValue(&$record) {
		if($this->checkAttributes()) {
			return(
				'<a href="'
				.$this->getAttribute('uri_fragment')
				.$record[$this->id_key]
				.'">Edit</a>'
			);
		}
		return('&#63;');
	}
}

//Delete links.
// optional attributes: allow_callback
class ReportElement_deletelink extends ReportElement_link {
	function ReportElement_deletelink() {
		$this->ReportElement_link();
		$this->registerAttribute('delete_params');
		$this->setAttribute('key','nodata_deletelink');
		$this->setAttribute('uri_fragment', 'nodata_deletelink');
	}
	function getValue(&$record) {
		$test = $this->getAttribute('allow_callback');
		if($this->checkAttributes()) {
			if($test && function_exists($test) ? $test($record) : true) {
				return(
					'<a href="#" onclick="request_delete('
					.$this->getAttribute('delete_params')
					.',\''.$record[$this->id_key]
					.'\'); return(false);">Delete</a>'
				);
			}
			return('Delete');
		}
		return('&#63;');
	}
}

//Order links.
class ReportElement_orderlink extends ReportElement_link {
	function ReportElement_orderlink() {
		$this->ReportElement_link();
		$this->registerAttribute('limit');
		$this->registerAttribute('label');
	}
	function getValue(&$record) {
		if($this->checkAttributes()) {
			if($record[$this->getAttribute('key')]
				== $this->getAttribute('limit')) {
				return(Report::gmc($this->getAttribute('label')));
			}
			else {
				return(
					'<a href="'
					.$this->getAttribute('uri_fragment')
					.$record[$this->id_key]
					.'">'.Report::gmc($this->getAttribute('label')).'</a>'
				);
			}
		}
		return('&#63;');
	}
}

//Toggle links.
// optional attributes: label_enable,query_enable,label_disable,query_disable
class ReportElement_togglelink extends ReportElement_link {
	function ReportElement_togglelink() {
		$this->ReportElement_link();
	}
	function getValue(&$record) {
		if($this->checkAttributes()) {
			if($record[$this->getAttribute('key')]) {
				$label = $this->getAttribute('label_enable') ?
					$this->getAttribute('label_enable') : 'Enable';
				$query = $this->getAttribute('query_enable') ?
					$this->getAttribute('query_enable') : '&amp;toggle=0';
			}
			else {
				$label = $this->getAttribute('label_disable') ?
					$this->getAttribute('label_disable') : 'Disable';
				$query = $this->getAttribute('query_disable') ?
					$this->getAttribute('query_disable') : '&amp;toggle=1';
			}
			return(
				'<a href="'
				.$this->getAttribute('uri_fragment')
				.$record[$this->id_key].$query
				.'">'.Report::gmc($label).'</a>'
			);
		}
		return('&#63;');
	}
}

//Links with details/tooltips.
// optional attributes: tooltip_key, tooltip
class ReportElement_tooltiplink extends ReportElement_link {
	function ReportElement_tooltiplink() {
		$this->ReportElement_link();
	}
	function getValue(&$record) {
		if($this->getAttribute('tooltip_key')) {
			$ttdata = $record[$this->getAttribute('tooltip_key')];
		}
		else if($this->getAttribute('tooltip')) {
			$ttdata = $this->getAttribute('tooltip');
		}
		else {
			$ttdata = '';
		}
		if($this->checkAttributes()) {
			if($ttdata) {
				return(
					'<a href="'
					.$this->getAttribute('uri_fragment')
					.$record[$this->id_key]
					.'" onmouseover="show_tooltip(\''
					.addslashes($ttdata)
					.'\');" onmouseout="hide_tooltip();">'
					.$record[$this->getAttribute('key')]
					.'</a>'
				);
			}
			else {
				return(parent::getValue());
			}
		}
		return('&#63;');
	}
}

//Date/time listing.
// optional attributes: format
class ReportElement_date extends ReportElement_data {
	function ReportElement_date() {
		$this->ReportElement_data();
	}
	function getValue(&$record) {
		if($this->checkAttributes()) {
			$format = $this->getAttribute('format') ?
				$this->getAttribute('format') : 'Y-m-d g:ia';
			return(
				Report::gmc(
					date($format, $record[$this->getAttribute('key')])
				)
			);
		}
		return('&#63;');
	}
}

?>