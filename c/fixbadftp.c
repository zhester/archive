/****************************************************************************
	Fix Bad FTP'd Files
	Zac Hester - 2004-12-30

	This an attempt at trying to fix my botched binary files that were
	transfered in ASCII mode from Unix to DOS.  So, I need to bring them back
	from DOS ASCII format to binary format.

	I've tried using text-based utilities, but those just made it worse.  I've
	also tried to reverse the transfer's modifications by "getting" using the
	same client-server scheme as I did when I "put" the files.  The setup that
	caused the problem was:
		Client: ftp (standard Unix client) on DragonFly BSD.
			-Operating in ASCII (default) mode.
			-Sent tarballs to server.
		Server: FileZilla FTP Server on Windows XP.
			-Operating in ASCII mode (specified by client).
			-Received tarballs.

		The conversion that should have taken place:
			lf -> crlf

		The conversion I need to do:
			crlf -> lf
****************************************************************************/

#include <stdio.h>
#include <string.h>

//Set a default work buffer size (bytes).
#define WBS (1024*4)


/**
 * Writes out a short test file.
 */
void writetestfile() {
	FILE* ofh = fopen("testfile", "w");
	if(!ofh) { printf("Can't write test file.\n"); return; }
	unsigned char buffer[] = {'4','6','a','5','\0','\n','\n','\r','3','\r','t',
		'\r','\n','5','9','g','\r','\r','\n','\n','4','\0','f','7','g','a','s',
		'\n','\n','\r','t','m','\r','\r','\n','3','\0','\t','6','1','2','\n',
		'\r','U','R','M','T','4','I','M','1','3','3','7','d','0','0','d'};
	fwrite(buffer, sizeof(char), sizeof(buffer), ofh);
	fclose(ofh);
	return;
}


/**
 * Program entry point.
 */
int main(int argc, char** argv) {

	//Write out a test file for debugging purposes.
	if(strcmp(argv[1], "-test") == 0) {
		writetestfile();
		printf("Written test file data to \"testfile\"\n");
		return(0);
	}

	//Check user sanity.
	if(argc != 3) {
		printf("Invalid command specified.  Please use:\n");
		printf("fixbadftp INPUTFILE OUTPUTFILE\n");
		return(1);
	}


	//Input and output file handles.
	FILE* ifh;
	FILE* ofh;

	//Working set data storage.
	unsigned char inbox[WBS];		//Data coming from the input file.
	unsigned char outbox[WBS];		//Data to be written to the output file.
	unsigned char lastchar;			//The last character read from the inbox.
	unsigned char firstloop = 1;	//Flag to trigger the first time through.
	long int numdetections = 0;		//Number of CRLF pairs found.
	long int totalread = 0;			//Total bytes read from input file.
	long int totalwritten = 0;		//Total bytes written to output file.
	int numbytesread = 0;			//Number of bytes read for current iteration.
	int i, j;						//Loop counters/array indexes.

	//Open input file and check.
	ifh = fopen(argv[1], "r");
	if(!ifh) {
		printf("Unable to open input file (%s).\n", argv[1]);
		return(2);
	}

	//Open output file and check.
	ofh = fopen(argv[2], "w");
	if(!ofh) {
		printf("Unable to open output file (%s).\n", argv[2]);
		fclose(ifh);
		return(2);
	}

	/* The scanning algorithm works on the idea of moving chunks of the input
	 * file into memory and then scanning that chunk.  Since the algorithm has
	 * to remember what character it saw last and then decide to write or not
	 * write the last character based on the current character, the process of
	 * of storing a character to write happens on the previous character, not
	 * on the current character.
	 */

	//Process the entire input file.
	while(!feof(ifh)) {

		//Read in a buffer full of data.
		numbytesread = fread(inbox, sizeof(char), sizeof(inbox), ifh);
		totalread += numbytesread;

		//Scan inbox array.
		for(i = 0, j = 0; i < numbytesread; ++i) {

			//Go to next element on the first time through.
			if(firstloop && i == 0) {
				lastchar = inbox[0];
				firstloop = 0;
				continue;
			}

			//Let's check to see if something needs to happen.
			else {

				//The last char WAS NOT a CR.
				if(lastchar != '\r') {

					//Always write.
					outbox[j] = lastchar; ++j;
				}

				//The last char WAS a CR.
				else {

					//The current char is not a LF.
					if(inbox[i] != '\n') {

						//Always write.
						outbox[j] = lastchar; ++j;
					}

					else {

						//If we fall through to here, nothing needs to be written
						// because we encountered a CRLF pair.  The line feed itself
						// will be written on the next pass.
						++numdetections;
					}
				}
			}

			//Advance the last character.
			lastchar = inbox[i];
		}

		//Write our converted binary data (watch the number of bytes to write).
		totalwritten += fwrite(outbox, sizeof(char), (j), ofh);
	}

	//Write out the final character (it has to be good).
	outbox[0] = lastchar;
	totalwritten += fwrite(outbox, sizeof(char), 1, ofh);

	//Close files.
	fclose(ifh);
	fclose(ofh);

	//Display scanning/repairing statistics.
	printf("Processing results for input file \"%s\":\n", argv[1]);
	printf("Total Bytes Read:    %ld\n", totalread);
	printf("Total Bytes Written: %ld\n", totalwritten);
	printf("Read - Written:      %ld\n", (totalread-totalwritten));
	printf("CRLFs Detected:      %ld\n", numdetections);
	printf("Sane? (0=yes):       %d\n",
		((totalread-totalwritten)-numdetections));

	//We're done.
	return(0);
}
