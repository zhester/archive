/****************************************************************************
	XMLTLImport: XML Transport Layer, Client Import Utility
	Version 0.0.0 for ActionScript 3.0
	Zac Hester
	2009-02-11

	XMLTL native object importing for Action Script 3.0.
	XML object data transport layer for highly structured data types.
	DTD: http://rushmoreradio.net/public/xmltl.dtd

	Example usage:
	var xi:XMLTLImport = new XMLTLImport(<xmltl>
		<s n="k0">v0</s>
		<h n="k1">
			<s n="k1a">v1a</s>
			<s n="k1b">v1b</s>
		</h>
	</xmltl>);
	var as3obj:Object = xi.importObject();
	trace(as3obj.k0); //outputs "v1"
	trace(as3obj.k1.k1b); //outputs "v1b"
****************************************************************************/

package {

	public class XMLTLImport {

		//Handles the root XML document element.
		private var xml;


		/**
		 * XMLTLImport
		 * Creates a new XMLTLImport object.
		 *
		 * @param xml_root The XMLTL XML object to import
		 */
		public function XMLTLImport(xml_root:XML) {
			xml = xml_root;
		}


		/**
		 * importObject
		 * Provides an interface for importing the XMLTL document.
		 *
		 * @param xml_root An optional XMLTL XML object to import
		 * @return A native object representing the XMLTL contents
		 */
		public function importObject() {
			return(getObject(arguments[0]?arguments[0]:xml));
		}


		/**
		 * getObject
		 * Constructs an AS3 object out of an XMLTL node.
		 *
		 * @param node The XMLTL node element
		 * @return An AS3 object representing the node
		 */
		private function getObject(node:XML) {

			var ctag:String = node.localName();

			if(ctag == 'xmltl') {
				var bt:String = node.@bt ? node.@bt : 'h';
				ctag = (bt != 'h' && bt != 'a') ? 's' : bt;
			}

			if(ctag == 's') {
				return(getScalor(node));
			}

			else if(ctag == 'a') {
				var arr:Array = new Array();
				for(var i = 0; i < node.children().length(); ++i) {
					arr.push(getObject(node.children()[i]));
				}
				return(arr);
			}

			else if(ctag == 'h') {
				var obj:Object = new Object();
				var c:XML = null;
				for(var j = 0; j < node.children().length(); ++j) {
					c = node.children()[j];
					if(c.@n) {
						obj[c.@n] = getObject(c);
					}
					else {
						if(!obj.ANONYMOUS) { obj.ANONYMOUS = new Array(); }
						obj.ANONYMOUS.push(getObject(c));
					}
				}
				return(obj);
			}

			return(null);
		}


		/**
		 * getScalor
		 * Converts the contents of a leaf node into a properly-typed
		 *   AS3 scalor value.
		 *
		 * @param node The XMLTL leaf node
		 * @return A typed scalor value representing the node's value
		 */
		private function getScalor(node:XML) {
			var s:String = node.toString();
			var t:String = node.@t;
			if(t) {
				switch(t) {
					case 'n': return(null);
					case 'b': return(s=='true'?true:false);
					case 'i': return(parseInt(s));
					case 'f': return(parseFloat(s));
				}
			}
			return(s);
		}
	}
}
