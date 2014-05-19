
/**
 * init_flipper
 * Initializes an image flipper on the page.
 *
 * @author Zac Hester
 * @date 2006-04-13
 *
 * @param url The URL to get a list of images as a JSON document
 * @param max_dim The maximum thumbnail dimension
 */
function init_flipper(url, max_dim) {

	//Set the maximum dimension.
	flipper_max_dim = max_dim;

	//Acquire an HTTP client.
	var client = new http_client();

	//Request image list.
	client.get(url, flipper_get);
}


/**
 * flipper_get
 * Handles the server response.
 *
 * @author Zac Hester
 * @date 2006-04-13
 *
 * @param response The response text (JSON document)
 */
function flipper_get(response) {

	//Acquire document reference.
	var ele = document.getElementById('flipper');

	//Parse the JSON document.
	flipper_data = eval(response);
	var fd = flipper_data;
	var num_images = fd.length;

	//Create a div to hold the thumbnails.
	var thumb_parent = document.createElement('div');
	thumb_parent.className = 'flipper_thumbs';
	var timg = null, ta = null, dims = [];

	//Add each thumbnail to the document.
	for(var i = 0; i < num_images; ++i) {
		timg = document.createElement('img');
		timg.setAttribute('src', fd[i]['path']);
		dims = flipper_get_dims(
			[ fd[i]['width'], fd[i]['height'] ],
			flipper_max_dim
		);
		timg.style.width = dims[0]+'px';
		timg.style.height = dims[1]+'px';
		ta = document.createElement('a');
		ta.id = 'flpa_'+i;
		ta.setAttribute('href', '#flpimg_'+i);
		ta.onclick = flipper_action;
		ta.appendChild(timg);
		thumb_parent.appendChild(ta);
	}

	//Attach the thumbnails to the containing element.
	ele.appendChild(thumb_parent);
}


/**
 * flipper_action
 * Handles the click events of the anchors.
 *
 * @author Zac Hester
 * @date 2006-04-13
 */
function flipper_action() {

	//Acquire document references.
	var ele = document.getElementById('flipper');
	var img = ele.getElementsByTagName('img')[0];

	//The index into the data array is found in the anchor's ID.
	var fd_index = this.id.toString().split('_')[1];

	//Setting the "src" attribute will switch the image.
	img.setAttribute('src', flipper_data[fd_index]['path']);

	//Prevent browser from tracking this.
	return(false);
}


/**
 * flipper_get_dims
 * Calculates new image dimensions for a thumbnail images.
 *
 * @author Zac Hester
 * @date 2006-04-13
 *
 * @param dims The width and height of the original image (array)
 * @param max_dims The maximum allowed dimension
 * @return An array containing the width and height of the thumbnail
 */
function flipper_get_dims(dims, max_dim) {
	var w = dims[0];
	var h = dims[1];
	var dw, dh;

	//Vertical format.
	if(w < h && h > max_dim) {
		dh = max_dim;
		dw = Math.round((w * dh) / h);
	}
	//Horizontal format.
	else if(w > max_dim) {
		dw = max_dim;
		dh = Math.round((h * dw) / w);
	}
	//Image smaller than maximum size.
	else {
		dw = w;
		dh = h;
	}

	//Send back the new dimensions.
	return([dw, dh]);
}


/*
 * Global image data
 */
var flipper_data;
var flipper_max_dim;
