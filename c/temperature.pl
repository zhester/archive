#!/usr/bin/perl
#############################################################################
#
# Temperature Lookup Table Construction
#
# Steve Larsen and Zac Hester
#
# March 3rd, 2006
#
# For: Dr. Batchelder, CENG 447: Embedded Systems, Lab 4
#
# Construct a table of temparature values corresponding to AD-converted
# integer values for a particular thermister sensing circuit.
#
#############################################################################

# Open output file.
open FH, "> temp_list.txt";

# Run through every possible index value.
#  (Index 0 is not a valid log argument.)
for($i = 1; $i < 255; $i++) {

	# Calculate the real temperature for this index.
	print FH &calc($i).",";
}

# Close output file.
close FH;

##
# Calculate a temperature based on an index.
##
sub calc {

	# Argument 0 is the index for which to calculate.
	my $index = shift;

	# The raw voltage at this index value.
	my $volts = ($index * 0.012890625); # 0.129 = 3.3 / 256

	# The thermister resistance is based on the voltage divider formula.
	#  (10k*Vt) / (5-Vt)
	my $rt = (10000 * $volts) / (5 - $volts);

	# The temperature is calculated from the characteristic equation of
	#  the thermister.  (Perl's log() is the natural log.)
	my $temp = (log($rt) - log(28899)) / -0.0429;

	# Return rounded temperature value.
	return int($temp + 0.5);
}
