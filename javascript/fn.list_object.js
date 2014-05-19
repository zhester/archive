
/**
 * list_object
 * Generates a nested series of HTML DOM lists detailing the contents of
 * an object.
 *
 * @param obj The object to scan for information
 * @param depth The maximum search depth of the object (optional)
 * @return A DOM node that is either a list <ul>, or a single scalar
 *         value inside a <div>
 */
function list_object(obj) {

	//Make sure this is an object or array.
	if(typeof(obj) != 'object') {
		var anode = document.createElement('div');
		anode.innerHTML = obj;
		return(anode);
	}

	//This will be the list of the children of this object.
	var mynode = document.createElement('ul');

	//Variable to reference generated list items.
	var temp = null;

	//Tell the interpreter we expect exceptions.
	try {

		//Loop through all elements of the object.
		for(var i in obj) {
	
			//Get the type of each child element.
			child_type = typeof(obj[i]);
	
			//Check each child's type.
			switch(child_type) {
	
				//Objects are recursively added as a list within this
				// list item.
				case 'object':
					temp = document.createElement('li');
					temp.innerHTML = '<strong>'+i
						+'</strong> type:object; value:(children);';
					if(arguments[1] && (arguments[1]-1) > 0) {
						temp.appendChild(list_object(obj[i],arguments[1]-1));
					}
					else if(arguments[1] == null) {
						temp.appendChild(list_object(obj[i]));
					}
					mynode.appendChild(temp);
				break;
	
				//Just report functions that we find.
				case 'function':
					temp = document.createElement('li');
					temp.innerHTML = '<strong>'+i
						+'</strong> type:function; value:(code block);';
					mynode.appendChild(temp);
				break;
	
				//Scalar types have their values displayed.
				case 'string':
				case 'number':
				case 'boolean':
					temp = document.createElement('li');
					temp.innerHTML = '<strong>'+i
						+'</strong> type:'+child_type
						+'; value:'+obj[i]+';';
					mynode.appendChild(temp);
				break;
			}
		}
	}

	//Catch any exceptions, but don't do anything.
	catch(e) {
	}

	//Send back the list.
	return(mynode);
}
