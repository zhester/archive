<?php
/**
 * Completely remove any directory and its contents.
 * This is not a good funciton to use  ;)
 *
 * @author Zac Hester <zac@planetzac.net>
 * @date 2006-03-01
 *
 * @param dir The directory to wipe
 * @return Whether or not the removal was successful    
 */ 
function wipe_dir($dir) {

	//Sanity check user input.
	if(!(file_exists($dir) && is_dir($dir))) { return(false); }

	//Open the directory.
	$dh = opendir($dir);

	//Make sure we opened the directory.
	if(!$dh) { return(false); }

	//Scan each node in the directory.
	while(($node = readdir($dh)) !== false) {

		//Make sure this node isn't one of the special directories.
		if($node != '.' && $node != '..') {

			//See if this is a directory.
			if(is_dir($dir.'/'.$node)) {

				//Attempt to wipe this directory.
				if(!wipe_dir($dir.'/'.$node)) { return(false); }
			}

			//This is a file.
			else {

				//Attempt to delete the file.
				if(!unlink($dir.'/'.$node)) { return(false); }
			}
		}
	}

	//Close current directory.
	closedir($dh);

	//Attempt to remove current directory.
	if(!rmdir($dir)) { return(false); }

	//If we make it here, everything worked.
	return(true);
}
?>