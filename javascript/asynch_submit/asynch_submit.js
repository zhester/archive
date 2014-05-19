
/**
 * asynch_submit
 * Submits any HTML form asynchronously.
 *
 * This script requires the use of my http_client class.
 *
 * @author Zac Hester
 * @date 2006-03-20
 * @version 1.0.0
 *
 * @param form_id The ID of the form to submit
 * @param handler A user-defined event handler taking one argument
 */
function asynch_submit(form_id, handler) {

	//Reference the form and get settings.
	var frm = document.getElementById(form_id);
	var action = frm.getAttribute('action');
	var method = 'GET';
	var test = frm.getAttribute('method');
	if(test) { method = test.toUpperCase(); }

	//HTTP client.
	var as_http_client = new http_client();

	//Construct parameter list as a query string.
	var query = 'asynch_submission=true';
	query += as_get_qstring(frm.getElementsByTagName('INPUT'));
	query += as_get_qstring(frm.getElementsByTagName('SELECT'));
	query += as_get_qstring(frm.getElementsByTagName('TEXTAREA'));

	//Post requests.
	if(method == 'POST') {
		as_http_client.post(action, query, handler,
			function() {
				handler('Error: The connection to the server has timed out.');
			}
		);
	}

	//Get requests.
	else {
		as_http_client.get(action+'?'+query, handler,
			function() {
				handler('Error: The connection to the server has timed out.');
			}
		);
	}

	//We made it this far.
	return(true);
}


/**
 * as_get_qstring
 * Return a chunk of a query string for a DOM node list.
 *
 * @param nodes A DOM node list of form elements
 */  
function as_get_qstring(nodes) {
	var query = '';
	var ele = null;
	var type = '';
	for(var i = 0; i < nodes.length; ++i) {
		ele = nodes[i];
		if(ele.tagName == 'SELECT' && ele.name) {
			query += '&'+ele.name+'='
				+encodeURIComponent(ele.options[ele.selectedIndex].value);
		}
		else if(ele.tagName == 'TEXTAREA' && ele.name) {
			query += '&'+ele.name+'='+encodeURIComponent(ele.value);
		}
		else if(ele.tagName == 'INPUT' && ele.name) {
			type = ele.getAttribute('type');
			if(type == 'radio') {
				if(ele.checked) {
					query += '&'+ele.name+'='
						+encodeURIComponent(ele.value);
				}
			}
			else if(type == 'checkbox') {
				if(ele.checked) {
					query += '&'+ele.name+'='
						+encodeURIComponent(ele.value);
				}
			}
			else {
				query += '&'+ele.name+'='+encodeURIComponent(ele.value);
			}
		}
	}
	return(query);
}
