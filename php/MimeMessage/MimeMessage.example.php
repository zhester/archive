<?php

header( 'Content-Type: text/plain' );

require __DIR__ . '/MimeMessage.php';

//Read the email message file (this one is from Gmail).
$email = file_get_contents( __DIR__ . '/MimeMessage.eml' );

//Instatiate the parser/storage class.
$mm = new MimeMessage( $email );

//Output the top-level From: header.
echo 'From: ' . $mm->getHeader( 'From' ) . "\n\n";

//Example demonstrating proper message-part access.
if( $mm->hasParts() ) {
    $num_parts = $mm->getPartCount();
    for( $i = 0; $i < $num_parts; ++$i ) {
        $p = $mm->getPart( $i );
        $part_type = $p->getHeader( 'Content-Type' )->getMeta( 'type' );
    }
}

//Diagnostic output tells us everything about the message.
print_r( $mm->dumpArray() );

?>