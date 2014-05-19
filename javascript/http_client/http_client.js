/****************************************************************************
	Simplified HTTP Client
	Zac Hester <zac@planetzac.net>
	2006-03-19
	Class Version: 1.0.0
****************************************************************************/


/**
 * http_client
 * The constructor function of my special HTTP client class.
 *
 * @author Zac Hester
 * @date 2006-03-19
 * @version 1.0.0
 */
function http_client() {

	//Initialize stupid globals.
	http_client_object = null;
	http_client_timer = null;
	http_client_tx_handler = null;
	http_client_to_handler = null;

	//Stores the number of miliseconds the script waits for response.
	this.timeout = arguments[0] ? arguments[0] : 30000;

	//A query string.
	this.query = '';

	//Assign the basic request method.
	this.request = http_client_request;

	//Assign the get method.
	this.get = http_client_get;

	//Assign the post method.
	this.post = http_client_post;

	//Create a new HTTP client.
	http_client_create();
}


/**
 * http_client_get
 * Performs a simple get and sends back the response through a
 * user-specified handler function.
 *
 * @author Zac Hester
 * @date 2006-03-19
 * @version 1.0.0
 *
 * @param url The URL/URI of the request as a string
 * @param text_handler A function handle to "catch" the response text
 * @param timeout_handler A function called if it times out
 * @return Whether or not the request was made successfully
 */
function http_client_get(url) {
	http_client_tx_handler = arguments[1] ?
		arguments[1] : http_client_tx_handler;
	http_client_to_handler = arguments[2] ?
		arguments[2] : http_client_to_handler;
	return(this.request('GET', url));
}


/**
 * http_client_post
 * Performs a simple post and sends back the response through a
 * user-specified handler function.
 *
 * @author Zac Hester
 * @date 2006-03-19
 * @version 1.0.0
 *
 * @param url The URL/URI of the request as a string
 * @param query The POST query data (already encoded in a string)
 * @param text_handler A function handle to "catch" the response text
 * @param timeout_handler A function called if it times out
 * @return Whether or not the request was made successfully
 */
function http_client_post(url, query) {
	this.query = query;
	http_client_tx_handler = arguments[2] ?
		arguments[2] : http_client_tx_handler;
	http_client_to_handler = arguments[3] ?
		arguments[3] : http_client_to_handler;
	return(this.request('POST', url));
}


/*---------------------------------------------------------------------------
	Functions and variables below this point are considered private.
---------------------------------------------------------------------------*/


/**
 * http_client_create
 * Create an XMLHTTP object (tries to account for many implementations).
 *
 * @author Zac Hester
 * @date 2006-03-19
 * @version 1.0.0
 */
function http_client_create() {

	//Try to create a regular XMLHTTP object.
	try {
		http_client_object = new XMLHttpRequest();
	}

	//The browser is either old or MS.
	catch(e) {
		var MSPROGIDS = [
			'MSXML2.XMLHTTP.5.0',
			'MSXML2.XMLHTTP.4.0',
			'MSXML2.XMLHTTP.3.0',
			'MSXML2.XMLHTTP',
			'Microsoft.XMLHTTP'
		];
		for(var k in MSPROGIDS) {
			try { http_client_object = new ActiveXObject(MSPROGIDS[k]); }
			catch(e) { continue; }
			break;
		}
	}

	//Test construction.
	if(http_client_object == null) {
		throw('Error: Your browser does not support'
			+' features required by this page.');
	}
}


/**
 * http_client_busy
 * Checks whether the HTTP client is busy or not.
 *
 * @return True if busy, false otherwise
 */
function http_client_busy() {
	switch(http_client_object.readyState) {
		case 1: case 2: case 3: return(true); break;
	}
	return(false);
}


/**
 * http_client_request
 * Generic HTTP request method.
 *
 * @param request The type of request: GET or POST
 * @param url The URL of the request
 * @return Whether or not the request finished successfully
 */
function http_client_request(request, url) {

	//Check for a request in progress.
	if(http_client_busy()) {
		alert('Error: The page is currently busy.');
		return(false);
	}

	//Assign response handler.
	http_client_object.onreadystatechange = function() {
		if(http_client_object.readyState == 4) {
	
			//Clear timeout timer.
			if(http_client_timer != null) {
				window.clearTimeout(http_client_timer);
				http_client_timer = null;
			}

			//Successful response.
			if(http_client_object.status == 200) {
	
				//Send text through handler.
				http_client_tx_handler(http_client_object.responseText);
			}
	
			//Response failed.
			else {
	
				//Send back an error message.
				http_client_tx_handler('Error: Failed to contact server.');
			}
		}
	};

	//POST request.
	if(request == 'POST') {
		http_client_object.open(request, url, true);
		http_client_object.setRequestHeader('Content-Type',
			'application/x-www-form-urlencoded');
		http_client_object.setRequestHeader('Content-Length',
			this.query.length);
		http_client_object.setRequestHeader('Connection', 'close');
		http_client_object.send(this.query);
	}

	//GET request.
	else {
		if(this.query.length) {
			url += '?'+this.query;
		}
		http_client_object.open(request, url, true);
		http_client_object.send(null);
	}

	//Check for timeout handler.
	if(http_client_to_handler) {

		//Set timeout handler.
		http_client_timer = window.setTimeout(
			function() {
				window.clearTimeout(http_client_timer);

				//If it's still busy after the timeout, perform timeout.
				if(http_client_busy()) {
					http_client_object.onreadystatechange = function(){};
					http_client_object.abort();
					http_client_to_handler();
				}
			},
			this.timeout
		);
	}

	//The request finished normally.
	return(true);
}


/**
 * This is globally scoped since I can't figure out how to reference
 * the XMLHTTP object when it is a property object (has-a) of a containing
 * object.  If you find this and you know how, please email me so I can
 * fix this.
 */
var http_client_object = null;
var http_client_timer = null;
var http_client_tx_handler = null;
var http_client_to_handler = null;
