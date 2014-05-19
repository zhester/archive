/**
 * check_form
 *
 * General-purpose, client-side form validation function.  The form can
 * use a number of features in this function to provide better user
 * interaction with data entry.  However, don't rely on this function
 * as a barrier to server-side resources.  Always run critical checking on
 * the server. 
 *
 * A comma-separated list of required fields should be provided in a hidden
 * form field with a name of "required_fields" as thus:
 *    <input type="hidden" name="required_fields" value="name,address" />
 *
 * A keyword "ALL" can be used instead of a list of fields to indicate that
 * every field in the form must contain some information.  You must
 * carefully evaluate each field's "value" property and whether or not
 * it can be evaluated as a "true" or non-empty statement in JavaScript for
 * this to work.
 *
 * The best event to call this function is the form's "onsubmit" event:
 *    <form id="example" onsubmit="return(check_form('example'));"> ...
 *
 * Optionally, you may choose to name your submit button "submit" which
 * will lock out the button during processing giving your users some
 * more feedback:
 *    <input type="submit" name="submit" value="Submit Form" />
 *
 * Optionally, you may add a CSS style pointing to any fields that are
 * members of the "error" class to improve the visibility of fields that
 * need to be completed:
 *    input.error, textarea.error { background-color: #FFFFEE; }
 *
 * @author Zac Hester
 * @date 2007-07-11
 * @version 1.0.0
 *
 * @param form_id A string containing the ID of the form to check
 * @return True if the form is found to be properly filled out
 */
function check_form(form_id) {

	//Reference form.
	var frm = document.getElementById(form_id);

	//Allow the form if we couldn't find it.
	if(!frm) { return(true); }

	//Check for form submit button (must be named "submit" for this feature).
	var submit_button = frm.elements['submit'];
	var submit_text = '';
	if(submit_button) {
		submit_button.disabled = true;
		submit_text = submit_button.value;
		submit_button.value = 'Please Wait...';
	}

	//Indicates the list of required fields.
	var req = frm.elements['required_fields'];

	//List of required fields.
	var field_list = new Array();

	//List of failed fields.
	var failed = new Array();

	//Check for required fields list for this form.
	if(req && req.value.length) {

		//Check for special case.
		if(req.value == 'ALL') {
			for(var i in frm.elements) {
				field_list.push(i);
			}
		}

		//Normal list of fields for checking.
		else {
			field_list = req.value.split(',');
		}

		//Pointer for current element.
		var elem = null;
		var test = '';

		//Run through each field.
		for(var i = 0; i < field_list.length; ++i) {

			//Set pointer.
			elem = frm.elements[field_list[i]];

			//Check for valid form element with maybe something in it.
			if(elem && elem.value) {

				//Reference the element data and trim.
				test = elem.value.trim();
	
				//Make sure the element has some info.
				if(test.length == 0) {
					failed.push(field_list[i]);
					elem.className = 'error';
				}
			}

			//Valid form element, but value is not detectable.
			else if(elem) {
				failed.push(field_list[i]);
				elem.className = 'error';
			}
		}
	}

	//See if the search found any failed fields.
	if(failed.length > 0) {
		alert('You must complete all required fields '
			+' before submitting the form.');
		if(submit_button) {
			submit_button.value = submit_text;
			submit_button.disabled = false;
		}
		return(false);
	}

	//If we make it this far, the form is okay.
	return(true);
}


/**
 * Add my own trim method to the String object.
 */
String.prototype.trim = function() {
	var str = this.replace(/^\s+/g, '');
	str = str.replace(/\s+$/g, '');
	return(str);
}
